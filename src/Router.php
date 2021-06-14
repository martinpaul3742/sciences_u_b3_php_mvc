<?php

namespace App;

use ReflectionMethod;
use Twig\Environment;
use Doctrine\ORM\EntityManager;
use App\Controller\AbstractController;

class Router
{
	private $url;
	private $routes = []; // Routes paramétrées
	private $twigInstance;
	private $injectableParameters = []; // Paramètres injectables dans les méthodes de contrôleurs

	public function __construct(EntityManager $em, Environment $twig)
	{
		$this->injectableParameters[EntityManager::class] = $em;
		$this->twigInstance = $twig;
	}

	public function addRoute(Route $route)
	{
		$this->routes[] = $route;
	}

	public function execute(string $requestPath, string $requestMethod)
	{
		$this->url = $requestPath;

		if ($route = $this->checkRoute($requestPath, $requestMethod)) {

			// regex
			// var_dump(preg_match('/\{(.*?)\}/', $requestPath));

			// Récupération nom de la classe et nom de la méthode
			$className = $route->getClass();
			$methodName = $route->getMethod();
			$parameters = $this->getParameters($className, $methodName);

			// Instanciation du contrôleur
			$controller = new $className($this->twigInstance);

			if ($controller instanceof AbstractController) {
				// Appel de la méthode adéquate, avec le(s) paramètre(s) adéquat(s), ou aucun paramètre
				// echo '<pre>' . var_export($parameters, true) . '</pre>';
				call_user_func_array([$controller, $methodName], $parameters);
			}
		} else {
			http_response_code(404);
		}
	}

	public function checkRoute(string $requestPath, string $requestMethod)
	{
		foreach ($this->routes as $route) {
			if ($route->getPath() === $requestPath && $route->getHttpMethod() === $requestMethod) {
				return $route;
			}
		}

		return false;
	}

	public function getParameters($className, $methodName)
	{
		$parameters = [];
		$functionParameters = (new ReflectionMethod($className . '::' . $methodName))->getParameters();
		$urlAttributes = $this->getUrlAttributes();

		// echo '<pre>' . var_export($functionParameters, true) . '</pre>';
		// echo '<pre>' . var_export($urlAttributes['url_parameters'], true) . '</pre>';
		// echo '<pre>' . var_export($urlAttributes['route_parameters'], true) . '</pre>';
		// die;
		
		// S'il y a des paramètres : analyse des différents paramètres
		foreach ($functionParameters as $functionParameter) {
			$paramName = $functionParameter->getName();
			$typeName = $functionParameter->getType() ? $functionParameter->getType()->getName() : $paramName;
			// var_dump($paramName);
			// echo '<pre>' . var_export($this->injectableParameters, true) . '</pre>';
			// echo '<pre>' . var_export(array_key_exists($typeName, $this->injectableParameters), true) . '</pre>';
			// die;
			
			// Si le nom du paramètre existe dans les paramètres injectables
			if ($this->isInjectableParam($typeName)) {
				$parameters[$paramName] = $this->injectableParameters[$typeName]; // Enregistrement du paramètre dans les paramètres à injecter
			}

			// S'il y a des attributs dans la route
			// if ($urlAttributes) {
			// 	foreach ($urlAttributes['route_parameters'] as $key => $route_parameter) {
			// 		// var_dump($route_parameter);
			// 		// die;
			// 		echo '<pre>' . var_export($urlAttributes['route_parameters'], true) . '</pre>';
			// 		if ($route_parameter == $paramName) $parameters[$paramName] = $urlAttributes['url_parameters'][$key];
			// 	}
			// }
			// if ($typeName) {
			// }
		}


		return $parameters;
	}

	public function getUrlAttributes()
	{
		$test_url = '/edit/password/7/user-toto';
		// $test_url = $this->url;
		$routes = [
			'/edit/password/{id}/user-{name}',
			// '/edit/post/{post_name}',
		];

		function multiexplode (array $delimiters, string $string) {
			$ready = str_replace($delimiters, $delimiters[0], $string);
			$launch = explode($delimiters[0], $ready);
			return  $launch;
		}

		
		foreach ($routes as $route) {
			// Admettons : '/edit/password/{user_id}/token-{token}' et '/edit/password/7/token-h6rk85jsi79f6h'
			preg_match_all('/\{(.*?)\}/', $route , $out, PREG_PATTERN_ORDER);
			$route_fragments = $out[0]; // tableau des fragments de la route --> ['{user_id}', '{token}']
			$route_parameters = $out[1]; // tableau des paramètres de la route ['user_id', 'token']

			// echo '<pre>' . var_export($route_fragments, true) . '</pre>';
			// echo '<pre>' . var_export($route_parameters, true) . '</pre>';
			
			if (count($route_fragments) > 0) { // s'il y a des paramètres
				$route_without_fragments = multiexplode($route_fragments, $route); // tableau de la route sans les fragments --> ['/edit/password/', '/token-']
				$url_parameters = multiexplode($route_without_fragments, $test_url); // tableau de l'url sans la route auxquelles on a enlevé les fragments --> ['7', 'h6rk85jsi79f6h']
				foreach ($url_parameters as $k => $item) if ($item == '') unset($url_parameters[$k]); // on enlève les colonnes vides du tableau

				// echo '<pre>' . var_export($route_without_fragments, true) . '</pre>';
				// echo '<pre>' . var_export($url_parameters, true) . '</pre>';

				if (count($route_fragments) == count($url_parameters)) { // s'il y a autant de paramètres dans l'url que dans la route
					$urlAttributes = ['url_parameters' => array_values($url_parameters), 'route_parameters' => array_values($route_parameters)];
					$this->setUrlAttributesToInjectable($urlAttributes);
					// var_dump()
					return $urlAttributes;
				}
			}

			return false;
		}
	}

	public function setUrlAttributesToInjectable($urlAttributes)
	{
		// echo '<pre>' . var_export($urlAttributes, true) . '</pre>';
		foreach ($urlAttributes['route_parameters'] as $key => $route_parameter) {
			$this->injectableParameters[$route_parameter] = $urlAttributes['url_parameters'][$key];
		}
		// echo '<pre>' . var_export($this->injectableParameters, true) . '</pre>';
		// die;
	}

	public function isInjectableParam($typeName)
	{
		return array_key_exists($typeName, $this->injectableParameters);
	}
}

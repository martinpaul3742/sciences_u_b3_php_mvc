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
	private $injectableParameters = []; // Paramètres injectables dans les méthodes de contrôleurs
	private $twigInstance;

	/**
	 * 
	 */
	public function __construct(EntityManager $em, Environment $twig)
	{
		$this->injectableParameters[EntityManager::class] = $em;
		$this->twigInstance = $twig;
	}

	/**
	 * 
	 */
	public function addRoute(Route $route)
	{
		$this->routes[] = $route;
	}

	/**
	 * 
	 */
	public function execute(string $requestPath, string $requestMethod)
	{
		$this->url = $requestPath;

		// Soit l'url est enregistrée soit elle contient des attributs
		$route = $this->getRoute($requestPath, $requestMethod);

		// var_dump($route);
		// die;

		if ($route && $this->verifyHttpMethod($route, $requestMethod)) {
			// Récupération nom de la classe et nom de la méthode
			$className = $route->getClass();
			$methodName = $route->getMethod();
			$parameters = $this->getParameters($className, $methodName);

			// Instanciation du contrôleur
			$controller = new $className($this->twigInstance, $this->routes);

			if ($controller instanceof AbstractController) {
				// Appel de la méthode adéquate, avec le(s) paramètre(s) adéquat(s), ou aucun paramètre
				call_user_func_array([$controller, $methodName], $parameters);
			}
		} else {
			http_response_code(404);
		}
	}

	/**
	 * 
	 */
	public function getRoute(string $requestPath)
	{
		// Si la route est statique, ne contient pas d'attributs
		foreach ($this->routes as $route) {
			if ($route->getPath() === $requestPath) {
				return $route;
			}
		}

		// S'il n'existe pas de route statique, on vérifie qu'elle puisse contenir des paramètres
		return $this->verifyUrlAttributes($this->url); // s'il existe des attributs dans la route, on les mets dans les paramètres injectables du Router
	}

	/**
	 * 
	 */
	public function getParameters($className, $methodName)
	{
		$parameters = [];
		$functionParameters = (new ReflectionMethod($className . '::' . $methodName))->getParameters();
		
		// S'il y a des paramètres : analyse des différents paramètres
		foreach ($functionParameters as $functionParameter) {
			$paramName = $functionParameter->getName();
			$typeName = $functionParameter->getType() ? $functionParameter->getType()->getName() : $paramName; // Si le type n'est pas défini il s'agit d'un attribut de l'url, par exemple {id} = 7
			
			// Si le nom du paramètre existe dans les paramètres injectables
			if ($this->isInjectableParameter($typeName)) {
				$parameters[$paramName] = $this->injectableParameters[$typeName]; // Enregistrement du paramètre dans les paramètres à injecter
			}
		}

		return $parameters;
	}

	/**
	 * 
	 */
	public function verifyUrlAttributes($url)
	{
		foreach ($this->routes as $route) {
			$route_path = $route->getPath();

			// Admettons : '/edit/password/{user_id}/token-{token}' et '/edit/password/7/token-h6rk85jsi79f6h'
			preg_match_all('/\{(.*?)\}/', $route_path , $out, PREG_PATTERN_ORDER);
			$route_fragments = $out[0]; // tableau des fragments de la route --> ['{user_id}', '{token}']
			$route_parameters = $out[1]; // tableau des paramètres de la route ['user_id', 'token']
			
			if (count($route_fragments) > 0) { // s'il y a des paramètres
				$route_without_fragments = $this->multiexplode($route_fragments, $route_path); // tableau de la route sans les fragments --> ['/edit/password/', '/token-']
				$url_parameters = $this->multiexplode($route_without_fragments, $url); // tableau de l'url sans la route auxquelles on a enlevé les fragments --> ['7', 'h6rk85jsi79f6h']
				$route_without_fragments_string = '';
				$url_without_parameters_string = '';	
				
				foreach ($route_without_fragments as $k => $item) {
					$route_without_fragments_string .= $item;
					if ($item == '') unset($route_without_fragments[$k]); // on enlève les colonnes vides du tableau
				}
			
				foreach ($url_parameters as $k => $item) {
					if ($item == '') unset($url_parameters[$k]); // on enlève les colonnes vides du tableau
				}

				foreach ($this->multiexplode(array_values($url_parameters), $url) as $k => $item) {
					$url_without_parameters_string .= $item;
				}

				// echo '<pre>' . var_export($route_without_fragments, true) . '</pre>';
				// echo '<pre>' . var_export($url, true) . '</pre>';
				// echo '<pre>' . var_export($url_parameters, true) . '</pre>';
				// echo '<pre>' . var_export($route_without_fragments_string, true) . '</pre>';
				// echo '<pre>' . var_export($url_without_parameters_string, true) . '</pre>';
				// die;

				// echo '<pre>' . var_export('path', true) . '</pre>';
				// echo '<pre>' . var_export($route_path, true) . '</pre>';
				// echo '<pre>' . var_export('route sans fragments', true) . '</pre>';
				// echo '<pre>' . var_export($route_without_fragments, true) . '</pre>';
				// echo '<pre>' . var_export('attributs de l\'url', true) . '</pre>';
				// echo '<pre>' . var_export($url_parameters, true) . '</pre>';
				// // echo '<pre>' . var_export($route_parameters, true) . '</pre>';
				// die;

				if ($route_without_fragments_string === $url_without_parameters_string) { // s'il y a autant de paramètres dans l'url que dans la route
					$this->setUrlAttributesToInjectable([
						'url_parameters' => array_values($url_parameters),
						'route_parameters' => array_values($route_parameters)
					]);

					return $route;
				}
			}
		}

		return false;
	}

	/**
	 * 
	 */
	public function verifyHttpMethod($route, $httpMethod)
	{
		return in_array($httpMethod, $route->getHttpMethod());
	}

	/**
	 * 
	 */
	private function setUrlAttributesToInjectable($urlAttributes)
	{
		foreach ($urlAttributes['route_parameters'] as $key => $route_parameter) {
			$this->injectableParameters[$route_parameter] = $urlAttributes['url_parameters'][$key];
		}
	}

	/**
	 * 
	 */
	public function isInjectableParameter($typeName)
	{
		return array_key_exists($typeName, $this->injectableParameters);
	}

	/**
	 * 
	 */
	public function multiexplode (array $delimiters, string $string) {
		$ready = str_replace($delimiters, $delimiters[0], $string);
		$launch = explode($delimiters[0], $ready);

		return  $launch;
	}
}

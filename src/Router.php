<?php

namespace App;

use Doctrine\ORM\EntityManager;
use ReflectionMethod;
use Twig\Environment;
use App\Controller\AbstractController;

class Router
{
	private $paths = [];
	private $routes = [];

	// Paramètres injectables dans les méthodes de contrôleurs
	private $params = [];
	private $twigInstance;

	public function __construct(EntityManager $em, Environment $twig)
	{
		$this->params[EntityManager::class] = $em;
		$this->twigInstance = $twig;
	}

	public function addRoute(Route $route)
	{
		$this->routes[] = $route;
	}

	public function execute(string $requestPath, string $requestMethod)
	{
		if ($route = $this->checkRoute($requestPath, $requestMethod)) {

			// Récupération nom de la classe et nom de la méthode
			$className = $route->getClass();
			$methodName = $route->getMethod();
			$parameters = $this->getParams($className, $methodName);

			// Instanciation du contrôleur
			$controller = new $className($this->twigInstance);

			if ($controller instanceof AbstractController) {
				// Appel de la méthode adéquate, avec le(s) paramètre(s) adéquat(s), ou aucun paramètre
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

	public function getParams($className, $methodName)
	{
		$params = [];
		$parameters = (new ReflectionMethod($className . '::' . $methodName))->getParameters();

		// Analyse des différents paramètres
		// Du coup, pas de boucle si pas de paramètre
		foreach ($parameters as $param) {
			$paramName = $param->getName();
			$typeName = $param->getType()->getName();

			var_dump($paramName);
			var_dump($typeName);
			
			// Vérification si le nom du paramètre existe dans les paramètres injectables
			if ($this->isInjectableParam($typeName)) {
				// $t = $this->params;
				// echo '<pre>' . var_export($t[$typeName], true) . '</pre>';
				// die;

				// Enregistrement du paramètre dans les paramètres à injecter
				$params[$paramName] = $this->params[$typeName];
			}
		}

		return $params;
	}

	public function isInjectableParam($typeName)
	{
		return array_key_exists($typeName, $this->params);
	}
}

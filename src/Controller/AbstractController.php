<?php

namespace App\Controller;

use Twig\Environment;

abstract class AbstractController
{
	protected $twig;
	public $rootUrl;
	private $routes;

	public function __construct(Environment $twig, $routes)
	{
		$this->twig = $twig;
		$this->routes = $routes;
		$this->rootUrl = 'http://' . $_SERVER['HTTP_HOST'];
	}

	public function render($name, array $context = [])
	{
		$context['rootUrl'] = $this->rootUrl;

		echo $this->twig->render($name, $context);
	}

	public function redirectToPath($path)
	{
		header('Location: ' . $path);
	}
}

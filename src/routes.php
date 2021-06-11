<?php

use App\Router;
use App\Route;
use App\Controller\HomeController;

// Home
$router->addRoute(new Route([
    'path' => '/',
    'httpMethod' => 'GET',
    'name' => 'home',
    'class' => HomeController::class,
    'method' => 'index'
]));

// $router->addPath(
//     '/',
//     'GET',
//     'home',
//     HomeController::class,
//     'index'
// );


// Contact
// $router->addPath(
//     '/contact',
//     'GET',
//     'contact',
//     HomeController::class,
//     'contact'
// );

// is_subclass_of();


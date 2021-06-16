<?php

use App\Route;
use App\Controller\HomeController;
use App\Controller\UserController;


// $router->addRoute(new Route([
//     'path' => '/edit/password/{id}/user-{name}',
//     'httpMethod' => [GET],
//     'name' => 'home',
//     'class' => HomeController::class,
//     'method' => 'test'
// ]));


// Home
$router->addRoute(new Route([
    'path' => '/',
    'httpMethod' => [GET],
    'name' => 'home',
    'class' => HomeController::class,
    'method' => 'index'
]));

$router->addRoute(new Route([
    'path' => '/user/new-random-{gender}',
    'httpMethod' => [GET],
    'name' => 'newUserRandom',
    'class' => UserController::class,
    'method' => 'newUserRandom'
]));


$router->addRoute(new Route([
    'path' => '/user/delete-{id}',
    'httpMethod' => [GET],
    'name' => 'deleteUser',
    'class' => UserController::class,
    'method' => 'delete'
]));



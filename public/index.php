<?php

// Composer va installer la méthode de chargement PSR-4 auprès de PHP
require_once __DIR__ . '/../vendor/autoload.php';

if (php_sapi_name() !== 'cli' && preg_match('/\.(?:png|jpg|jpeg|gif|ico)$/', $_SERVER['REQUEST_URI'])) {
    // On demande à PHP de servir le fichier demandé directement
    return false;
}


use App\Router;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


// Doctrine
(new Dotenv())->loadEnv(__DIR__ . '/../.env'); // Configuration, variables d'environnement
$paths = [__DIR__ . '/../src/Entity']; // Indique à Doctrine dans quel dossier aller chercher & analyser les entités
$isDevMode = ($_ENV['APP_ENV'] === 'dev');
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);

$entityManager = EntityManager::create([
    'driver'   => $_ENV['DB_DRIVER'],
    'host'     => $_ENV['DB_HOST'],
    'port'     => $_ENV['DB_PORT'],
    'user'     => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'dbname'   => $_ENV['DB_DBNAME']
], $config);


// Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
    'debug' => ($_ENV['APP_ENV'] === 'dev'),
    'cache' => __DIR__ . '/../var/twig',
]);

$router = new Router($entityManager, $twig);

define('POST', 'POST');
define('GET', 'GET');
define('PUT', 'PUT');
define('HEAD', 'HEAD');
define('DELETE', 'DELETE');
define('CONNECT', 'CONNECT');
define('OPTIONS', 'OPTIONS');
define('TRACE', 'TRACE');
define('PATCH', 'PATCH');

include __DIR__ . '/../src/routes.php'; // charge les routes
$router->execute($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);


<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Route;

class HomeController extends AbstractController
{
    // Ici, j'ai une dépendance envers l'entity manager
    public function index($id = 0, EntityManager $em)
    {
        $user = new User();
        $user->setName("Bob");
        // Persist permet uniquement de dire au gestionnaire d'entités de gérer l'entité passée en paramètre
        // Persist ne déclenche pas automatiquement une insertion
        $em->persist($user);
        // Pour déclencher l'insertion, on doit appeler la méthode "flush" sur le gestionnaire d'entités
        // $em->flush();

		// $test_url = '/edit/password/7/user-toto';
		// // $test_url = '/4/7';
		// $routes = [
		// 	'/edit/password/{id}/user-{toto}',
		// 	// '/edit/post/{post_name}',
		// ];

		// function multiexplode (array $delimiters, string $string) {
		// 	$ready = str_replace($delimiters, $delimiters[0], $string);
		// 	$launch = explode($delimiters[0], $ready);
		// 	return  $launch;
		// }

		
		// foreach ($routes as $route) {
		// 	// Admettons : '/edit/password/{user_id}/token-{token}' et '/edit/password/7/token-h6rk85jsi79f6h'
		// 	preg_match_all('/\{(.*?)\}/', $route , $out, PREG_PATTERN_ORDER);
		// 	$route_fragments = $out[0]; // tableau des fragments de la route --> ['{user_id}', '{token}']
		// 	$route_parameters = $out[1]; // tableau des paramètres de la route ['user_id', 'token']

		// 	echo '<pre>' . var_export($route_fragments, true) . '</pre>';
		// 	echo '<pre>' . var_export($route_parameters, true) . '</pre>';
			
		// 	if (count($route_fragments) > 0) { // s'il y a des paramètres
		// 		$route_without_fragments = multiexplode($route_fragments, $route); // tableau de la route sans les fragments --> ['/edit/password/', '/token-']
		// 		$url_without_parameters = multiexplode($route_without_fragments, $test_url); // tableau de l'url sans la route auxquelles on a enlevé les fragments --> ['7', 'h6rk85jsi79f6h']
		// 		foreach ($url_without_parameters as $k => $item) if ($item == '') unset($url_without_parameters[$k]); // on enlève les colonnes vides du tableau

		// 		echo '<pre>' . var_export($route_without_fragments, true) . '</pre>';
		// 		echo '<pre>' . var_export($url_without_parameters, true) . '</pre>';

		// 		if (count($route_fragments) == count($url_without_parameters)) { // s'il y a autant de paramètres dans l'url que dans la route
					
		// 		}
		// 	} else {
		// 		return false;
		// 	}
		// }


		// '/edit/password/{id}/user-{name}' --> $route_parts = ['/edit/password/', '/user-']
		// '/edit/password/7/user-toto' --> url_parameters = ['7', 'toto']

		// if (count($route_parts) === count($url_parameters))
		// '/edit/password/{id}/user-{name}' --> 'id', 'name'
		// function ($id, $name, $other) { ... } ou function ($other, $id, $name) { ... }







        echo $this->render('index.html.twig', ['user' => $user, 'id' => $id]);
    }

    public function contact()
    {
		echo $this->render('contact.html.twig', ['title' => 'Contact']);
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Route;
use Doctrine\ORM\Repository\RepositoryFactory;

class HomeController extends AbstractController
{
    public function index(EntityManager $em)
    {
		$userRepository = $em->getRepository(User::class);
		$users = $userRepository->findAll();

        $this->render('home.html.twig', [
			'title' => 'Accueil',
			'users' => $users
		]);
    }

    public function test($id, $name)
    {
		echo '<pre>' . var_export($id, true) . '</pre>';
		echo '<pre>' . var_export($name, true) . '</pre>';
	}
}

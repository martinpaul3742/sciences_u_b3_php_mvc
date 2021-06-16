<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Route;

class UserController extends AbstractController
{
    public function newUserRandom(EntityManager $em, $gender)
    {
        if ($gender == 'male' || $gender == 'female') {
            // Le service faker API est HS
            // $url = "https://fakerapi.it/api/v1/persons?_quantity=1&_gender={$gender}";
            // $opts = array('https' => ['method'  => 'GET']);
            // $context = stream_context_create($opts); 
    
            // $result = file_get_contents($url, false, $context);
            // $response = json_decode($result, true)['data'];
            $gender = $gender == 'female' ? 'women' : 'men';

            $name = $gender == 'women' ? 'Tata' : 'Toto';
            $picture = "https://randomuser.me/api/portraits/{$gender}/" . rand(0, 99) . ".jpg";
            
            $user = new User();
            $user->setName($name);
            $user->setPicture($picture);
            $em->persist($user);
            $em->flush();
            var_dump($user);
        }
        // die;
        $this->redirectToPath($this->rootUrl);
    }

    public function delete(EntityManager $em, $id)
    {
        if (is_int($id / 1)) {
            $userRepository = $em->getRepository(User::class);
            $user = $userRepository->findOneBy(['id' => $id]);

            if ($user) {
                $em->remove($user);
                $em->flush();
            }
        }
        
        $this->redirectToPath($this->rootUrl);
    }
}

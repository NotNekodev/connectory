<?php

namespace App\Controller;

use App\Service\UserManagementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RootController extends AbstractController {
    #[Route('/', name: 'root')]
    public function root(UserManagementService $ums): Response {
        $session = $ums->getSession();
        if ($session === null) {
            return $this->render('index.html.twig', [
                'user' => "Not logged in",
                'usrtxt2' => "Log in or sign up",
                'isSignedIn' => false,
            ]);
        }

        $user = $session->getUser();

        return $this->render('index.html.twig', [
            'user' => $user->getUsername(),
            'usrtxt2' => $user->getEmail(),
            'isSignedIn' => true,
        ]);
    }
}
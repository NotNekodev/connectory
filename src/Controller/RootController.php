<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RootController extends AbstractController {
    #[Route('/', name: 'root')]
    public function root(Request $request, SessionRepository $repo): Response {

        $token = $request->cookies->get('connectory_session');
        if (!$token) {
            return $this->render('index.html.twig', [
                'user' => "Not logged in",
                'usrtxt2' => "Sign up or login",
                'isSignedIn' => false,
            ]);
        }

        $session = $repo->find($token);
        if (!$session) {
            return $this->render('index.html.twig', [
                'user' => "Not logged in",
                'usrtxt2' => "Sign up or login",
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
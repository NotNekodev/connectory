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
            return $this->redirectToRoute('login');
        }

        $session = $repo->find($token);
        if (!$session) {
            return $this->redirectToRoute('login');
        }

        $user = $session->getUser();

        return new Response("Hello, " . $user->getUsername() . "!", Response::HTTP_OK);
    }
}
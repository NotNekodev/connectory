<?php

namespace App\Controller\Authentication;

use App\Repository\SessionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'logout')]
    public function login_router(Request $request, SessionRepository $sessionRepository): Response {

        $token = $request->cookies->get('connectory_session');
        if (!$token) {
            return $this->redirectToRoute('login');
        }

        $session = $sessionRepository->find($token);
        if (!$session) {
            return $this->redirectToRoute('login');
        }

        $sessionRepository->delete($session);

        // delete the cookie
        setcookie('connectory_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        return $this->redirectToRoute('root');
    }
}
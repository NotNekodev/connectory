<?php
namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login_router(Request $request): Response {
        $errors = $request->getSession()->get('login_errors', []);
        $last_username = $request->getSession()->get('last_username', '');
        
        // Clear session data after retrieving it
        $request->getSession()->remove('login_errors');
        $request->getSession()->remove('last_username');
        
        return $this->render('login.html.twig', [
            'errors' => $errors,
            'last_username' => $last_username
        ]);
    }
}

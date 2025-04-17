<?php

namespace App\Controller\Pages;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\UserManagementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminPageController extends AbstractController {

    #[Route('/admin', name: 'admin')]
    public function adminPage(UserManagementService $ums): Response
    {
        $user = $ums->getUser();

        if (!$user) {
            return $this->redirectToRoute('root');
        }

        if (!$user->isAdmin()) {
            return $this->render('error/http-error.html.twig', [
                'error_str' => "403 Forbidden",
                'error_num' => Response::HTTP_FORBIDDEN
            ]);
        }

        return $this->render('admin/admin-home.html.twig', [
            'title' => 'Admin Page',
            'user' => $this->getUser(),
        ]);
    }
    #[Route('/ash', name: 'admin_submit_handler', methods: ['POST'])]
    public function adminSubmitHandler(Request $request, SessionRepository $sessionRepository, UserRepository $userRepository): Response {
        $session_token = $request->cookies->get('connectory_session');
        $session = $sessionRepository->findOneBy(['id' => $session_token]);
        $user = $session->getUser();

        if (!$user->isAdmin()) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }

        $search = $request->request->get('search');
        $type = $request->request->get('type');

        $user = null;

        if ($type === 'username') {
            $user = $userRepository->findOneBy(['username' => $search]);
        } else if ($type === 'email') {
            $user = $userRepository->findOneBy(['email' => $search]);
        } else if ($type === 'phone') {
            $user = $userRepository->findOneBy(['telephone' => $search]);
        } else if ($type === 'session') {
            $user = $sessionRepository->findOneBy(['id' => $search])->getUser();
        } else if ($type === 'uid') {
            $user = $userRepository->findOneBy(['id' => $search]);
        } else if ($type === 'uuid') {
            $user = $userRepository->findOneBy(['uuid' => $search]);
        }

        return $this->render('admin/admin-home.html.twig', [
            'user' => $user
        ]);
    }
}
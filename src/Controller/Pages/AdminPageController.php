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
            $session = $ums->getSession();
            if ($session === null) {
                return $this->render('error/not-allowed.html.twig', [
                    'user' => "Not logged in",
                    'usrtxt2' => "Log in or sign up",
                    'isSignedIn' => false,
                ]);
            }

            $user = $session->getUser();

            return $this->render('error/not-allowed.html.twig', [
                'user' => $user->getUsername(),
                'usrtxt2' => $user->getEmail(),
                'isSignedIn' => true,
            ]);
        }

        if (!$user->isAdmin()) {
            $session = $ums->getSession();
            if ($session === null) {
                return $this->render('error/not-allowed.html.twig', [
                    'user' => "Not logged in",
                    'usrtxt2' => "Log in or sign up",
                    'isSignedIn' => false,
                ]);
            }

            $user = $session->getUser();

            return $this->render('error/not-allowed.html.twig', [
                'user' => $user->getUsername(),
                'usrtxt2' => $user->getEmail(),
                'isSignedIn' => true,
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

    #[Route('/admin_manage', name: 'admin_manage', methods: ['POST'])]
    public function adminDeleteUser(Request $request, UserManagementService $ums, UserRepository $userRepository): Response {

        if (!$ums->getUser()->isAdmin()) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }

        $action = $request->query->get('action');

        $userId = $request->query->get('uuid');
        $user = $userRepository->findOneBy(['uuid' => $userId]);

        if ($action === 'delete') {
            $ums->deleteUser($user);
        } else if ($action === 'promote') {
            $user->setIsAdmin(true);
        } else if ($action === 'demote') {
            $user->setIsAdmin(false);
        }

        return $this->redirectToRoute('admin');
    }
}
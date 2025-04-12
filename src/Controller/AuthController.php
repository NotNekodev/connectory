<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/login', name: 'login')]
    public function login_router(): Response {
        return $this->render('login.html.twig');
    }

    #[Route('/auth', name: 'auth_handler', methods: ['POST'])]
    public function handleAuth(Request $request): Response
    {
        $email = $request->request->get('username');
        $password = $request->request->get('password');
        $action = $request->request->get('action');

        if ($action === 'login') {
            return $this->login($email, $password);
        } elseif ($action === 'register') {
            return $this->register($email, $password);
        }

        return new Response('Invalid action.', 400);
    }

    private function login(string $email, string $password): Response
    {
        $user = $this->userRepository->findUserByEmail($email);
        if (!$user) {
            return new Response('User not found.', 404);
        }

        $uid = $user->getId();
        $username = $user->getUsername();
        $created = $user->getCreated();
        $passwd_hash = $user->getPasswordHash();
        $uuid = $user->getUuid();
        $uemail = $user->getEmail();
        if (!$user->getTelephone()) {
            $user->setTelephone('');
        }
        $tel = $user->getTelephone();


        return new Response("
            UserID: $uid </br>
            Username: $username </br>
            Password hash: $passwd_hash </br>
            UUID (GUID): $uuid </br>
            Email: $uemail </br>
            Telephone Number: $tel </br>
        ");
    }

    private function register(string $email, string $password): Response
    {
        return new Response("Unimplemented", Response::HTTP_SERVICE_UNAVAILABLE);
    }
}

<?php

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Ramsey\Uuid\Guid\GuidInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegisterController extends AbstractController {

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    #[Route('/register', 'register')]
    public function registerAction(): Response {
        return $this->render('register.html.twig', []);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register_handler', name: 'register_handler', methods: ['POST'])]
    public function register_handler(Request $request, RouterInterface $router, HttpClientInterface $httpClient): Response {
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $telephone = $request->request->get('phone');

        $passwd = $request->request->get('password');
        $passwd_confirm = $request->request->get('confirm_password');

        if ($passwd !== $passwd_confirm) {
            echo "<script type='text/javascript'>alert('Passwords dont match');</script>";
            return $this->render('register.html.twig', []);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setTelephone($telephone);
        $user->setPasswordHash(password_hash($passwd, PASSWORD_BCRYPT));
        $user->setCreated(new DateTime());
        $user->setUuid(Uuid::uuid4()->toString());

        $this->userRepository->save($user);

        return $this->redirectToRoute('root');
    }
}
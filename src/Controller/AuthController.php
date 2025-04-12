<?php
namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{

    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    public function __construct(UserRepository $userRepository, SessionRepository $sessionRepository)
    {
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    #[Route('/login', name: 'login')]
    public function login_router(): Response {
        return $this->render('login.html.twig');
    }

    /**
     * @throws RandomException
     */
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

        return $this->render('error/http-error.html.twig', [
            'error_num' => Response::HTTP_BAD_REQUEST,
            'error_str' => 'Bad request'
        ]);
    }

    /**
     * @throws RandomException
     */
    private function login(string $email, string $password): Response
    {
        $user = $this->userRepository->findUserByEmail($email);
        if (!$user) {
            echo "<script type='text/javascript'>alert('User not found');</script>";
            return $this->redirectToRoute('login', []);

        }

        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);

        $uid = $user->getId();
        $username = $user->getUsername();
        $created = $user->getCreated();
        $passwd_hash = $user->getPasswordHash();
        $uuid = $user->getUuid();
        $uemail = $user->getEmail();
        if (!$user->getTelephone()) {
            $user->setTelephone('{NO DATA}');
        }
        $tel = $user->getTelephone();


        if (!password_verify($password, $passwd_hash)) {
            return $this->redirectToRoute('login', []);
        }

        $date_str = $created->format('Y-m-d H:i:s');

        $token = bin2hex(random_bytes(32));

        $session = new Session();
        $session
            ->setUser($user)
            ->setCreatedAt(new DateTimeImmutable())
            ->setId($token);

        $this->sessionRepository->save($session);

        setcookie('connectory_session', $token, [
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        return new Response("
            UserID: $uid </br>
            Username: $username </br>
            Password hash: $passwd_hash </br>
            UUID (GUID): $uuid </br>
            Email: $uemail </br>
            Telephone Number: $tel </br>
            Created: $date_str </br>
        ");
    }

    private function register(string $email, string $password): Response
    {
        return $this->redirectToRoute('register', []);
    }
}

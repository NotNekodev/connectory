<?php


namespace App\Controller\Authentication;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
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

        return $this->redirectToRoute('root');
    }

}
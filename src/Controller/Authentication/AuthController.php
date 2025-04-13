<?php


namespace App\Controller\Authentication;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
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

        $remember_me = $request->request->get('remember_me', '0') === '1';

        return $this->login($email, $password, $remember_me, $request);
    }

    /**
     * @throws RandomException
     */
    private function login(string $email, string $password, bool $remember_me, Request $request): Response
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


        if ($remember_me) {
            /*setcookie('connectory_session', $token, [
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'expires' => time() + 60 * 60 * 24 * 365 * 5 // 5 years
            ]);*/
            setcookie('connectory_session', $token, time() + 60 * 60 * 24 * 365 * 5, '/', '', false, true);

        } else {
            setcookie('connectory_session', $token, 0, '/', '', false, true);
        }

        return $this->redirectToRoute('root');
    }

}
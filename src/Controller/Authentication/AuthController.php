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

        $remember_me = $request->request->get('remember_me') === 'on';

        return $this->login($email, $password, $remember_me, $request);
    }

    /**
     * @throws RandomException
     */
    private function login(string $email, string $password, bool $remember_me, Request $request): Response
    {
        $errors = [];

        if (empty($email)) {
            $errors['username'] = 'Email or username cannot be empty';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password cannot be empty';
        }
        
        if (!empty($errors)) {
            return $this->render('login.html.twig', [
                'errors' => $errors,
                'last_username' => $email
            ]);
        }

        $user = null;

        if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            $user = $this->userRepository->findUserByEmail($email);
        } else {
            $user = $this->userRepository->findOneBy(['username' => $email]);
        }
        
        if (!$user) {
            $errors['username'] = 'User not found';
            return $this->render('login.html.twig', [
                'errors' => $errors,
                'last_username' => $email
            ]);
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
            $errors['password'] = 'Invalid password';
            return $this->render('login.html.twig', [
                'errors' => $errors,
                'last_username' => $email
            ]);
        }
        $token = bin2hex(random_bytes(32));

        $session = new Session();
        $session
            ->setUser($user)
            ->setCreatedAt(new DateTimeImmutable())
            ->setId($token);

        $this->sessionRepository->save($session);

        if ($remember_me) {
            setcookie('connectory_session', $token, time() + 60 * 60 * 24 * 365 * 5, '/', '', false, true);
        } else {
            setcookie('connectory_session', $token, 0, '/', '', false, true);
        }

        return $this->redirectToRoute('root');
    }
}

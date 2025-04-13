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
    public function registerAction(Request $request): Response {
        return $this->render('register.html.twig', [
            'errors' => $request->getSession()->get('registration_errors', []),
            'old_data' => $request->getSession()->get('old_data', [])
        ]);
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
        
        $errors = [];
        $old_data = [
            'username' => $username,
            'email' => $email,
            'phone' => $telephone,
        ];
        
        // Validate username
        if (empty($username)) {
            $errors['username'] = 'Username cannot be empty';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif ($this->userRepository->findOneBy(['username' => $username])) {
            $errors['username'] = 'Username already taken';
        }
        
        // Validate email
        if (empty($email)) {
            $errors['email'] = 'Email cannot be empty';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->userRepository->findUserByEmail($email)) {
            $errors['email'] = 'Email already registered';
        }
        
        // Validate phone (optional)
        if (!empty($telephone) && !preg_match('/^\+[1-9]\d{1,14}$/', $telephone)) {
            $errors['phone'] = 'Invalid phone number format';
        }
        
        // Validate password
        if (empty($passwd)) {
            $errors['password'] = 'Password cannot be empty';
        } elseif (strlen($passwd) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif ($passwd !== $passwd_confirm) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $request->getSession()->set('registration_errors', $errors);
            $request->getSession()->set('old_data', $old_data);
            return $this->redirectToRoute('register');
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setTelephone($telephone);
        $user->setPasswordHash(password_hash($passwd, PASSWORD_BCRYPT));
        $user->setCreated(new DateTime());
        $user->setUuid(Uuid::uuid4()->toString());

        $this->userRepository->save($user);
        
        // Clear session data
        $request->getSession()->remove('registration_errors');
        $request->getSession()->remove('old_data');

        return $this->redirectToRoute('root');
    }
}

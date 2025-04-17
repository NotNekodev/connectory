<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;

class UserManagementService {
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    public function __construct(UserRepository $userRepository, SessionRepository $sessionRepository) {
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * Gets the current active session per cookie
     * @return Session|null
     */
    public function getSession(): ?Session {

        if (!isset($_COOKIE['connectory_session'])) {
            return null;
        }

        $token = $_COOKIE['connectory_session'];

        $session = $this->sessionRepository->find($token);
        if (!$session) {
            return null;
        }

        return $session;
    }

    /**
     * Changes the password of a user
     * @param User $user The user whose password is to be changed
     * @param string $newPassword The new password to set
     * @return int 0 if the password was changed, 1 if the new password is the same as the old one
     */
    public function changeUserPassword(User $user, string $newPassword): int {
        $oldPassword = $user->getPasswordHash();

        $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->setPasswordHash($hashedNewPassword);

        if ($user->getPasswordHash() === $oldPassword) {
            return 1;
        }

        return 0;
    }

    /**
     * Deletes a user and all their sessions
     * @param User $user The user to delete
     * @return int 0 if the user was deleted, 1 if the user was not found
     */
    public function deleteUser(User $user): int {
        $uuid = $user->getUuid();
        $session = $this->sessionRepository->findBy(['user' => $uuid]);
        if ($session) {
            foreach ($session as $s) {
                $this->sessionRepository->delete($s);
            }
        }

        $this->userRepository->delete($user);

        return 0;
    }

    public function getUser(): ?User {
        $session = $this->getSession();
        if (!$session) {
            return null;
        }

        $user = $this->userRepository->find($session->getUser());
        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * Retrieves all sessions for a user
     * @param User $user The user whose sessions are to be retrieved
     * @return array An array of sessions for the user
     */
    public function getUserSessions(User $user): array {
        return $this->sessionRepository->findBy(['user' => $user->getUuid()]);
    }
}
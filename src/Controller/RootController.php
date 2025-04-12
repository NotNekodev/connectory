<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RootController extends AbstractController {
    #[Route('/', name: 'root')]
    public function root(): Response {
        return $this->redirectToRoute('login', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
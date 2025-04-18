<?php

namespace App\Controller;

use App\Service\UserManagementService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ErrorController
{
    private Environment $twig;
    private $errorRenderer;
    private UserManagementService $ums;

    private array $errorClientQuotes = [
        "Your request makes no sense to our server. It's not you, it's... actually, it might be you.",
        "Our server can't understand what you're asking for. We speak HTTP, not gibberish.",
        "Your request was lost in translation.",
        "Our server received your request but has no idea what to do with it.",
        "You're speaking a language our server doesn't understand.",
        "Your request is like a puzzle with missing pieces.",
        "This request is syntactically incorrect, semantically erroneous, or just plain confusing.",
        "Even our most senior developer couldn't figure out what you're asking for.",
        "We'd love to help, but your request is like asking for a square circle.",
        "Your request's logic has left the building.",
        "Your browser sent something our server finds utterly baffling.",
        "This request appears to be a riddle wrapped in an enigma.",
        "Our server is scratching its digital head at your request.",
        "Your browser and our server are having a communication breakdown.",
        "Your request doesn't compute. Have you tried speaking more clearly?"
    ];

    private array $errorServerQuotes = [
        "The server is having an existential crisis.",
        "We tried to produce the best code, but even servers we bad days.",
        "Hey you, yes you machine, calm down. We are working on it.",
        "Something broke on our end. Our code monkeys are frantically typing.",
        "The code has gone where no debugger has gone before.",
        "Our server just ran into a wall of code it couldn't process.",
        "The server encountered an unexpected condition that prevented it from fulfilling the request... or as we like to call it, a Tuesday.",
        "We've hit a digital pothole. Our engineers are filling it as we speak.",
        "Internal Server Error: Our servers are experiencing technical difficulties. We should try turning it on and off again",
        "Our stack is currently overflow-ing with issues.",
        "The server is experiencing a moment of technical confusion. It'll be back to normal shortly.",
        "The request was perfect. Our response? Not so much.",
        "Our server had a kernel panic attack. It's currently in therapy.",
        "Looks like our code decided to take an unscheduled vacation.",
        "The server is currently questioning its life choices. Please check back soon."
    ];

    public function __construct(Environment $twig, ErrorRendererInterface $errorRenderer, UserManagementService $ums) {
        $this->twig = $twig;
        $this->errorRenderer = $errorRenderer;
        $this->ums = $ums;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function show(FlattenException $exception): Response {
        $httpExceptionCode = $exception->getStatusCode();
        $httpExceptionMessage = $exception->getMessage();

        if ($httpExceptionCode / 100 >= 5 && $httpExceptionCode / 100 < 6) {
            $quote = $this->errorServerQuotes[array_rand($this->errorServerQuotes)];

            $session = $this->ums->getSession();
            if ($session === null) {
                return new Response($this->twig->render('error/500-error.html.twig', [
                    'user' => "Not Logged In",
                    'usrtxt2' => "Log in or sign up today",
                    'isSignedIn' => false,
                    'error_number' => $httpExceptionCode,
                    'error_text' => $httpExceptionMessage,
                    'random_quote' => $quote,
                ]), $httpExceptionCode);
            }

            $user = $session->getUser();

            return new Response($this->twig->render('error/500-error.html.twig', [
                'user' => $user->getUsername(),
                'usrtxt2' => $user->getEmail(),
                'isSignedIn' => false,
                'error_number' => $httpExceptionCode,
                'error_text' => $httpExceptionMessage,
                'random_quote' => $quote,
            ]), $httpExceptionCode);
        } else if ($httpExceptionCode / 100 >= 4 && $httpExceptionCode / 100 < 5) {
            $quote = $this->errorClientQuotes[array_rand($this->errorClientQuotes)];

            $session = $this->ums->getSession();
            if ($session === null) {
                return new Response($this->twig->render('error/400-error.html.twig', [
                    'user' => "Not Logged In",
                    'usrtxt2' => "Log in or sign up today",
                    'isSignedIn' => false,
                    'error_number' => $httpExceptionCode,
                    'error_text' => $httpExceptionMessage,
                    'random_quote' => $quote,
                ]), $httpExceptionCode);
            }

            $user = $session->getUser();

            return new Response($this->twig->render('error/400-error.html.twig', [
                'user' => $user->getUsername(),
                'usrtxt2' => $user->getEmail(),
                'isSignedIn' => false,
                'error_number' => $httpExceptionCode,
                'error_text' => $httpExceptionMessage,
                'random_quote' => $quote,
            ]), $httpExceptionCode);
        } else {

            $text = sprintf(
                "An error occurred: %s (%d)",
                $httpExceptionMessage,
                $httpExceptionCode
            );

            return new Response($text);
        }
    }
}
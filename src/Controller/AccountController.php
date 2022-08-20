<?php

namespace App\Controller;

use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Exception;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class AccountController extends AbstractController
{

    #[Route('/account', name: 'app_account')]
    public function index(VideoRepository $repo): Response
    {
        $user = $this->getUser();
        $videos = $repo->findByOwnerField($user->getId());

        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'videos' => $videos,
            'user' => $user,
            'email' => $this->obfuscate_email($user->getEmail())
        ]);
    }

    #[Route('/account/verify', name: 'app_account_verify', methods: ['GET'])]
    public function verify(): Response
    {
        $this->addFlash('danger', "");
        return $this->redirectToRoute('app_account');
    }

    //https://stackoverflow.com/a/20545505/7276681
    function obfuscate_email($email)
    {
        $em   = explode("@", $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len  = floor(strlen($name) / 2);

        return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
    }
}

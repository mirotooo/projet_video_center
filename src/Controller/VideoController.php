<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\CreateFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        if ($this->isGranted('ROLE_USER') === true) {
            $videos = $doctrine->getRepository(Video::class)->findAll();
        } else {
            $videos = $doctrine->getRepository(Video::class)->findBy(['premiumVideo' => false]);
        }

        return $this->render('video/index.html.twig', [
            'controller_name' => 'VideoController',
            'videos' => $videos
        ]);
    }

    #[Route('/welcome', name: 'app_video_welcome')]
    public function welcome(ManagerRegistry $doctrine): Response
    {
        if ($this->getUser()) {
            $this->addFlash('info', 'Welcome ' . $this->getUser()->getFirstname());
            $videos = $doctrine->getRepository(Video::class)->findAll();
        } else {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('video/index.html.twig', [
            'controller_name' => 'VideoController',
            'videos' => $videos
        ]);
    }

    #[Route('/video/create', name: 'app_video_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $video = new Video();
        $form = $this->createForm(CreateFormType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video->setOwner($this->security->getUser());
            $entityManager->persist($video);
            $entityManager->flush();
            $this->addFlash('success', 'Video successfully created!');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('video/create.html.twig', [
            'createForm' => $form->createView(),
        ]);
    }

    #[Route('/video/{id<[0-9]+>}', name: 'app_video_show', methods: ['GET'])]
    public function show(Video $video, ManagerRegistry $doctrine): Response
    {
        $video = $doctrine->getRepository(Video::class)->find($video);

        if ($video->isPremiumVideo() && !$this->getUser()) {
            $this->addFlash('danger', 'You must Log in or Register, if you want to see a premium video !');
            return $this->redirectToRoute('app_login');
        }

        $userId = $this->getUser()->getId();
        $owner = $video->getOwner()->getId();

        $displayActions = $userId === $owner ? true : false;

        return $this->render('video/show.html.twig', [
            'controller_name' => 'VideoController',
            'video' => $video,
            'displayActions' => $displayActions
        ]);
    }

    #[Route('/video/{id<[0-9]+>}/edit', name: 'app_video_edit')]
    public function edit(Request $request, Video $video, ManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {
        $video = $doctrine->getRepository(Video::class)->find($video);

        $userId = null;
        if ($this->getUser()) {
            $userId = $this->getUser()->getId();
        }

        $owner = $video->getOwner()->getId();

        if ($userId !== $owner) {
            $this->addFlash('danger', 'Unable to edit video! [Wrong owner]: current user [' . strval($userId) . '] and video owner [' . strval($owner) . ']');
            return $this->redirectToRoute('app_account');
        }

        $form = $this->createForm(CreateFormType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video->setOwner($this->security->getUser());
            $entityManager->persist($video);
            $entityManager->flush();
            $this->addFlash('success', 'Video successfully edited!');
            return $this->redirectToRoute('app_home');
        }
        
        return $this->render('video/edit.html.twig', [
            'controller_name' => 'VideoController',
            'createForm' => $form->createView(),
            'video' => $video
        ]);
    }

    #[Route('/video/{id<[0-9]+>}/delete', name: 'app_video_delete', methods: ['GET'])]
    public function delete(Video $video, EntityManagerInterface $entityManager): Response
    {
        $userId = null;
        if ($this->getUser()) {
            $userId = $this->getUser()->getId();
        }
        $owner = $video->getOwner()->getId();
        if ($userId !== $owner) {
            $this->addFlash('danger', 'Unable to delete video! [Wrong owner]: current user [' . strval($userId) . '] and video owner [' . strval($owner) . ']');
            return $this->redirectToRoute('app_account');
        }

        $entityManager->remove($video);
        $entityManager->flush();
        $this->addFlash('info', 'Video successfully deleted!');
        return $this->redirectToRoute('app_account');
    }
}

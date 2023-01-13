<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\UploadFileService;

/**
 * @Route("/utilisateur")
 */
class UtilisateurController extends AbstractController
{

    public function __construct(
        UploadFileService $uploadFileService
    )
    {
        $this->uploadFileService = $uploadFileService; 
    }
    /**
     * @Route("/", name="utilisateur_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas accès à cette page")
     */
    public function index(UtilisateurRepository $utilisateurRepository, Session $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //besoin de droits admin
        $utilisateur = $this->getUser();
        if(!$utilisateur)
        {
                $session->set("message", "Merci de vous connecter");
                return $this->redirectToRoute('app_login');
        }

        else if(in_array('ROLE_ADMIN', $utilisateur->getRoles())){
                return $this->render('utilisateur/index.html.twig', [
                        'utilisateurs' => $utilisateurRepository->findAll(),
                ]);
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/new", name="utilisateur_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->uploadFileService->uploadFile($form, $utilisateur, "profil", "upload_images_users_directory");
            $entityManager = $this->getDoctrine()->getManager();
            $utilisateur->setPassword(
                $userPasswordHasherInterface->hashPassword($utilisateur, $utilisateur->getPassword())
            );
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

     /**
     * @Route("/badge/{id}", name="utilisateur_badge", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function badge(Utilisateur $badge): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('utilisateur/badge.html.twig', [
            'badge' => $badge,
        ]);
    }

    /**
     * @Route("/{id}", name="utilisateur_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="utilisateur_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, $id, Session $session, Utilisateur $utilisateur, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $utilisateur->setPassword(
                $userPasswordHasherInterface->hashPassword($utilisateur, $utilisateur->getPassword())
            );
            return $this->redirectToRoute('utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="utilisateur_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, $id, Session $session, Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $utilisateur = $this->getUser();
        if($utilisateur->getId() != $id )
        {
                // un utilisateur ne peut pas en supprimer un autre
                $session->set("message", "Vous ne pouvez pas supprimer cet utilisateur");
                return $this->redirectToRoute('membre');
        }
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}

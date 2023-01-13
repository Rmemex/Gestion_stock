<?php

namespace App\Controller;

use App\Entity\Historique;
use App\Entity\Utilisateur;
use App\Repository\HistoriqueRepository;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, Session $session, HistoriqueRepository $historiqueRepository): Response
    {     
        $lastUsername = $authenticationUtils->getLastUsername();  
        // $histo = new Historique();
        // $date = new DateTime('', new DateTimeZone('Africa/Nairobi'));
        // $histo->setHistoDate($date);
        // $histo->setHistoType("0");
        // $histo->setHistoRefExterne($lastUsername);
        // $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->persist($histo);
        // $entityManager->flush();
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user

        if($session->has('message'))
        {
                $message = $session->get('message');
                $session->remove('message'); //on vide la variable message dans la session
                $return['message'] = $message; //on ajoute à l'array de paramètres notre message
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnex", name="deconnecte", methods={"GET","POST"})
     */
    public function deconnex(Request $request, Session $session, AuthenticationUtils $authenticationUtils): Response
    {
        $user = $request->get('user');
        $histo = new Historique();
        $date = new DateTime('', new DateTimeZone('Africa/Nairobi'));
        $histo->setHistoDate($date);
        $histo->setHistoRefExterne($user);
        $histo->setHistoType("0");
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($histo);
        $entityManager->flush();
        $session->clear();
        return $this->redirectToRoute('app_login');
    }
}

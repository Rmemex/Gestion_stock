<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\Session;

class NavigationController extends AbstractController
{
    /**
     * @Route("/navigation", name="home")
     */
    public function index(Session $session): Response
    {   
        $return = [];

        if($session->has('message'))
        {
                $message = $session->get('message');
                $session->remove('message'); //on vide la variable message dans la session
                $return['message'] = $message; //on ajoute à l'array de paramètres notre message
        }
        return $this->render('navigation/index.html.twig', $return);
    }

    /**
     * @Route("/admin", name="admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function admin(Session $session): Response
    {   
        $utilisateur = $this->getUser();
                if(!$utilisateur)
                {
                        $session->set("message", "Merci de vous connecter");
                        return $this->redirectToRoute('app_login');
                }

                else if(in_array('ROLE_ADMIN', $utilisateur->getRoles())){
                        return $this->render('navigation/admin.html.twig');
                }
                $session->set("message", "Vous n'avez pas le droit d'acceder à la page admin");
                if($session->has('message'))
                {
                        $message = $session->get('message');
                        $session->remove('message'); //on vide la variable message dans la session
                        $return['message'] = $message; //on ajoute à l'array de paramètres notre message
                }
                return $this->redirectToRoute('home', $return);
    }

    /**
     * @Route("/membre", name="membre")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function membre(Session $session): Response
    {
        $return = [];

        if($session->has('message'))
        {
                $message = $session->get('message');
                $session->remove('message'); //on vide la variable message dans la session
                $return['message'] = $message; //on ajoute à l'array de paramètres notre message
        }
        return $this->render('navigation/membre.html.twig', $return);
    }
}

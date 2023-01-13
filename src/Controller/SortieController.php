<?php

namespace App\Controller;

use App\Entity\Mouvement;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\ClientRepository;
use App\Repository\ProduitRepository;
use App\Repository\SortieRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/sortie")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/", name="sortie_index", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository, ClientRepository $clientRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
            'client' => $clientRepository->findAll(),
        ]);
    }

    /**
     * @Route("/filter", name="sortie_filter")
     */
    public function sortieFilter(SortieRepository $sortieRepository, Request $request): Response
    {   
        $dateFilter = $request->get('date');
        $clientFilter = $request->get('client');
        $sorties = $sortieRepository->findSearch($dateFilter,$clientFilter);
        return new JsonResponse([
            'table' => $this->renderView('sortie/fetch.html.twig', [
                'sorties' => $sorties,
            ]),
        ]);
    }

     /**
     * @Route("/liste/{date}/{client}", name="sortie_liste", methods={"GET"})
     */
    public function liste(SortieRepository $sortieRepository, Request $request, $date, $client): Response
    {
        $sorties = $sortieRepository->findSearch($date,$client);
        return new JsonResponse([
            'table' => $this->renderView('sortie/liste.html.twig', [
                'listes' => $sorties,
            ]),
        ]);
    }

    /**
     * @Route("/new", name="sortie_new", methods={"GET","POST"})
     */
    public function new(Request $request,EntityManagerInterface $entityManager,SortieRepository $sortieRepository,ClientRepository $clientRepository,ProduitRepository $produitRepository): Response
    {
        $response= new JsonResponse();
        $type = 0;
        $post = $request->request;
        $activation =$request->get('allData');
        foreach((array)$activation as $res){
            $client = $res['client'];
            $prod = $res['produit'];
            $prix = $res['prix'];
            $qte = $res['quantite'];
        }
        
        $sortie = new sortie();
        $sortie->setClient($clientRepository->find($client));
        $produit = $produitRepository->find($prod);
        $sortie->setProduit($produit);
        $sortie->setSortDate(new \DateTime());
        $sortie->setSortPrix($prix);
        $sortie->setSortQuantite($qte);

        $newMouvement = new Mouvement();
        $newMouvement->setProduit($produit);
        $newMouvement->setMouvDate(new DateTime('', new DateTimeZone('Africa/Nairobi')));
        $newMouvement->setMouvType($type);
        $newMouvement->setMouvQuantite($qte);
        $stock = intval($produit->getProQuantite()) - intval($qte);
        if($stock < 0) {
            $dataProduit['name'] = $produit->getProNom();
            $dataProduit['message'] = (intval($produit->getProQuantite()) == 0)? 'Rupture de stock' : 'stock insuffisant ['.intval($produit->getProQuantite()).']';
            $this->addFlash('error','Stock insuffisant');
            return $this->redirectToRoute('sortie_ajout');
        }else{
            $produit->setProQuantite($stock);
            $entityManager->persist($produit);
            $entityManager->persist($sortie);
            $entityManager->persist($newMouvement);
            $entityManager->flush();
        }

        // recupérer list
        $template = $this->renderView('sortie/_liste_sortie.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);        
        
        $result = ['templateList' => $template];

        $response->setData($result);

        return $response;
    }


    /**
     * @Route("/ajout", name="sortie_ajout", methods={"GET"})
     */
    public function ajout(SortieRepository $sortieRepository, ClientRepository $clientRepository, ProduitRepository $produitRepository): Response
    {
        return $this->render('sortie/ajout.html.twig', [
            'sorties' => $sortieRepository->findAll(),
            'clients'=>$clientRepository->findAll(),
            'produits'=>$produitRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_show", methods={"GET"})
     */
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/facture/{date}/{client}", name="sortie_facture", methods={"GET"})
     */
    public function facture(Sortie $facture, SortieRepository $sortieRepository, Request $request, $date, $client): Response
    {
        $sorties = $sortieRepository->findSearch($date,$client);
        return $this->render('sortie/facture.html.twig', [
            'factures' => $sorties,
            'facture' => $facture
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sortie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Sortie $sortie): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_delete", methods={"POST"})
     */
    public function delete(Request $request, Sortie $sortie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
    }
}

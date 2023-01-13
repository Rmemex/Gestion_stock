<?php

namespace App\Controller;

use App\Entity\Entree;
use App\Entity\Mouvement;
use App\Form\EntreeType;
use App\Repository\EntreeRepository;
use App\Repository\FournisseurRepository;
use App\Repository\ProduitRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/entree")
 */
class EntreeController extends AbstractController
{
    /**
     * @Route("/", name="entree_index", methods={"GET"})
     */
    public function index(EntreeRepository $entreeRepository, FournisseurRepository $fournisseurRepository): Response
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        return $this->render('entree/index.html.twig', [
            'entrees' => $entreeRepository->findAll(),
            'fournisseur' => $fournisseurRepository->findAll(),
            'date' => null,
            'fournisseurs' => null,
        ]);
    }

    /**
     * @Route("/filter", name="entree_filter")
     */
    public function entreeFilter(EntreeRepository $entreeRepository, Request $request): Response
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $dateFilter = $request->get('date');
        $fournisseurFilte = $request->get('fournisseur');
        $entrees = $entreeRepository->findSearch($dateFilter,$fournisseurFilte);
        return new JsonResponse([
            'table' => $this->renderView('entree/fetch.html.twig', [
                'entrees' => $entrees,
                'dates' => $dateFilter,
                'fournisseurs' => $fournisseurFilte
            ]),
        ]);
    }

    /**
     * @Route("/liste", name="entree_liste", methods={"GET"})
     */
    public function liste(EntreeRepository $entreeRepository, Request $request, $date, $fournisseur): Response
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $dateFilter = $request->get('date');
        $fournisseurFilte = $request->get('fournisseur');
        $entrees = $entreeRepository->findSearch($dateFilter,$fournisseurFilte);
        return new JsonResponse([
            'table' => $this->renderView('entree/liste.html.twig', [
                'listes' => $entrees,
                'dates' => $dateFilter,
                'fournisseurs' => $fournisseurFilte
            ]),
        ]);
    }

    /**
     * @Route("/ajout", name="entree_ajout", methods={"GET"})
     */
    public function ajout(EntreeRepository $entreeRepository, FournisseurRepository $fournisseurRepository, ProduitRepository $produitRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        return $this->render('entree/ajout.html.twig', [
            'entrees' => $entreeRepository->findAll(),
            'fournisseurs' => $fournisseurRepository->findAll(),
            'produits' => $produitRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="entree_new", methods={"GET","POST"})
     */
    public function new(Request $request,EntityManagerInterface $entityManager, EntreeRepository $entreeRepository, FournisseurRepository $fournisseurRepository, ProduitRepository $produitRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $response = new JsonResponse();
        $post = $request->request;
        $formData = $post->get('dataEntree');
        foreach($formData as $item) {
            $entree = new Entree();
            $entree->setFournisseur($fournisseurRepository->find($item['fournisseur']));
            $produit = $produitRepository->find($item['produit']);
            $entree->setProduit($produitRepository->find($item['produit']));
            $entree->setProduit($produit);
            $entree->setEntrDate(new \DateTime());
            $entree->setEntrPrix($item['prix']);
            $entree->setEntrQuantite($item['quantite']);
            $produit->setProQuantite(intval($produit->getProQuantite()) + intval($item['quantite']));
            
            $newMouvement = new Mouvement();
            $newMouvement->setFournisseur($fournisseurRepository->find($item['fournisseur']));
            $newMouvement->setProduit($produit);
            $newMouvement->setMouvDate(new DateTime('', new DateTimeZone('Africa/Nairobi')));
            $newMouvement->setMouvType(1);
            $newMouvement->setMouvQuantite($item['quantite']);

            $entityManager->persist($newMouvement);
            $entityManager->persist($entree);
        }
        $entityManager->flush();

        // recupérer list
        $listeA = $entreeRepository->findAll();
        $template = $this->renderView('entree/_list.html.twig', [
            'entrees' => $entreeRepository->findAll(),
        ]);        
        
        $result = ['templateList' => $template];

        $response->setData($result);

        return $response;
    }

    /**
     * @Route("/{id}", name="entree_show", methods={"GET"})
     */
    public function show(Entree $entree): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        return $this->render('entree/show.html.twig', [
            'entree' => $entree,
        ]);
    }

    /**
     * @Route("/commande/{date}/{fournisseur}", name="entree_commande", methods={"GET"})
     */
    public function commande(Entree $commande, Request $request, EntreeRepository $entreeRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $dateFilter = $request->get('date');
        $fournisseurFilte = $request->get('fournisseur');
        $entrees = $entreeRepository->findSearch($dateFilter,$fournisseurFilte);
        return $this->render('entree/commande.html.twig', [
            'commandes' => $entrees,
            'commande' => $commande
        ]);
    }

    /**
     * @Route("/{id}/edit", name="entree_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Entree $entree): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $form = $this->createForm(EntreeType::class, $entree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('entree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('entree/edit.html.twig', [
            'entree' => $entree,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="entree_delete", methods={"POST"})
     */
    public function delete(Request $request, Entree $entree): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        if ($this->isCsrfTokenValid('delete'.$entree->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($entree);
            $entityManager->flush();
        }

        return $this->redirectToRoute('entree_index', [], Response::HTTP_SEE_OTHER);
    }
}

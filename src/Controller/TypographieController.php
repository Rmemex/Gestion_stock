<?php

namespace App\Controller;

use App\Entity\Typographie;
use App\Form\TypographieType;
use App\Repository\TypographieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/typographie")
 */
class TypographieController extends AbstractController
{
    /**
     * @Route("/", name="typographie_index", methods={"GET"})
     */
    public function index(TypographieRepository $typographieRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        return $this->render('typographie/index.html.twig', [
            'typographies' => $typographieRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="typographie_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $typographie = new Typographie();
        $form = $this->createForm(TypographieType::class, $typographie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($typographie);
            $entityManager->flush();

            return $this->redirectToRoute('typographie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('typographie/new.html.twig', [
            'typographie' => $typographie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="typographie_show", methods={"GET"})
     */
    public function show(Typographie $typographie): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        return $this->render('typographie/show.html.twig', [
            'typographie' => $typographie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="typographie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Typographie $typographie): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        $form = $this->createForm(TypographieType::class, $typographie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('typographie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('typographie/edit.html.twig', [
            'typographie' => $typographie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="typographie_delete", methods={"POST"})
     */
    public function delete(Request $request, Typographie $typographie): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', 'ROLE_USER');
        if ($this->isCsrfTokenValid('delete'.$typographie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($typographie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('typographie_index', [], Response::HTTP_SEE_OTHER);
    }
}

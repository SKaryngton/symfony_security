<?php

namespace App\Controller;

use App\Entity\CheeseListing;
use App\Form\CheeseListingType;
use App\Repository\CheeseListingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cheeses')]
class CheeseListingController extends AbstractController
{
    #[Route('/', name: 'app_cheeses', methods: ['GET'])]
    public function index(CheeseListingRepository $cheeseListingRepository): Response
    {
        return $this->render('cheese_listing/index.html.twig', [
            'cheese_listings' => $cheeseListingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cheese_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request, CheeseListingRepository $cheeseListingRepository): Response
    {
        $cheeseListing = new CheeseListing();
        $form = $this->createForm(CheeseListingType::class, $cheeseListing,['user'=>$this->getUser()?->getEmail()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cheeseListingRepository->save($cheeseListing, true);

            return $this->redirectToRoute('app_cheeses', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cheese_listing/new.html.twig', [
            'cheese_listing' => $cheeseListing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cheese_show', methods: ['GET'])]
    public function show(CheeseListing $cheeseListing): Response
    {
        return $this->render('cheese_listing/show.html.twig', [
            'cheese_listing' => $cheeseListing,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cheese_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CheeseListing $cheeseListing, CheeseListingRepository $cheeseListingRepository): Response
    {
        $form = $this->createForm(CheeseListingType::class, $cheeseListing,['user'=>$this->getUser()?->getEmail()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cheeseListingRepository->save($cheeseListing, true);

            return $this->redirectToRoute('app_cheeses', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cheese_listing/edit.html.twig', [
            'cheese_listing' => $cheeseListing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cheese_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, CheeseListing $cheeseListing, CheeseListingRepository $cheeseListingRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cheeseListing->getId(), $request->request->get('_token'))) {
            $cheeseListingRepository->remove($cheeseListing, true);
        }

        return $this->redirectToRoute('app_cheeses', [], Response::HTTP_SEE_OTHER);
    }
}

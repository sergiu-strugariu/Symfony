<?php

namespace App\Controller\Dashboard;

use App\Entity\Lead;
use App\Form\Type\LeadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class LeadController extends AbstractController
{
    #[Route('/dashboard/leads', name: 'dashboard_lead_index')]
    public function index(): Response
    {
        return $this->render('dashboard/lead/index.html.twig');
    }

    #[Route('/dashboard/lead/create', name: 'dashboard_lead_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $lead = new Lead();

        $form = $this->createForm(LeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lead->setUuid(Uuid::v4());

            $em->persist($lead);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new lead.');
            return $this->redirectToRoute('dashboard_lead_index');
        }

        return $this->render('dashboard/lead/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/lead/{uuid}/edit', name: 'dashboard_lead_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
    {
        $lead = $em->getRepository(Lead::class)->findOneBy(['uuid' => $uuid]);
        if (null === $lead) {
            return $this->redirectToRoute('dashboard_lead_index');
        }

        $form = $this->createForm(LeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lead);
            $em->flush();

            $this->addFlash('success', 'You have successfully edited the lead');
            return $this->redirectToRoute('dashboard_lead_index');
        }

        return $this->render('dashboard/lead/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/lead/{uuid}/delete', name: 'dashboard_lead_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $lead = $em->getRepository(Lead::class)->findOneBy(['uuid' => $uuid]);
        if (null === $lead) {
            return $this->redirectToRoute('dashboard_lead_index');
        }

        $lead->setDeletedAt(new \DateTime());
        $em->persist($lead);
        $em->flush();

        $this->addFlash('success', 'The lead has been successfully deleted');
        return $this->redirectToRoute('dashboard_lead_index');
    }
}

<?php

namespace App\Controller\Dashboard;

use App\Entity\Refund;
use App\Form\Type\RefundType;
use App\Helper\PayUAPIHelper;
use App\Helper\SmartBillAPIHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RefundController extends AbstractController
{
    #[Route('/dashboard/refund', name: 'app_dashboard_refund')]
    public function index(): Response
    {
        return $this->render('dashboard/refund/index.html.twig');
    }

    #[Route('/dashboard/refund/{uuid}/edit', name: 'dashboard_refund_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }

        $form = $this->createForm(RefundType::class, $refund);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($refund);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully edited the refund.');
            return $this->redirectToRoute('app_dashboard_refund');
        }

        return $this->render('dashboard/refund/edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $refund,
            'editMode' => true
        ]);
    }


    #[Route('/dashboard/refund/{uuid}/delete', name: 'dashboard_refund_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }

        $em->remove($refund);
        $em->flush();

        $this->addFlash('success', 'The refund has been successfully deleted');
        return $this->redirectToRoute('app_dashboard_refund');
    }
    
    #[Route('/dashboard/refund/{uuid}/approve', name: 'dashboard_refund_approve')]
    public function approve(EntityManagerInterface $em, PayUAPIHelper $payUAPIHelper, SmartBillAPIHelper $smartBillAPIHelper, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }
        
        if (Refund::STATUS_APPROVED === $refund->getStatus()) {
            $this->addFlash('danger', "You've already approved this refund");
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }
        
        $payuPaymentReference = $refund->getPayuPaymentReference();
        $originalAmount = $refund->getAmount();
        $approvedAmount = $refund->getApprovedAmount();
        
        if (null === $payuPaymentReference) {
            $this->addFlash('danger', 'Please complete first the PayU reference number');
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }
        
        if (null === $approvedAmount) {
            $this->addFlash('danger', 'Please complete first the approved ammount');
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }

        $payUData = [
            'payuPaymentReference' => $payuPaymentReference,
            'originalAmount' => $originalAmount,
            'currency' => 'RON',
            'amount' => $approvedAmount
        ];

        try {
            $payUResponse = $payUAPIHelper->refundPayment($payUData);
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }

        if (isset($payUResponse['code']) && $payUResponse['code'] !== 200) {
            $this->addFlash('danger', $payUResponse['message']);
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }
        
        $refund->setStatus(Refund::STATUS_APPROVED);
        $em->persist($refund);
        $em->flush();

        if ($originalAmount == $approvedAmount) {
            $smartBillData = [
                'number' => $refund->getInvoiceNumber(),
                'issueDate' => (new \DateTime())->format('Y-m-d')
            ];

            try {
                $smartBillResponse = $smartBillAPIHelper->generateStornoInvoice($smartBillData);
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
            }

            if (isset($smartBillResponse['errorText']) && !empty($smartBillResponse['errorText'])) {
                $this->addFlash('danger', $smartBillResponse['errorText']);
                return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
            }
        }

        $this->addFlash('success', 'Congratulations, you have successfully approved the refund.');
        return $this->redirectToRoute('app_dashboard_refund');
    }
    
    #[Route('/dashboard/refund/{uuid}/reject', name: 'dashboard_refund_reject')]
    public function reject(EntityManagerInterface $em, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }
        
        $refund->setStatus(Refund::STATUS_REJECTED);
        $em->persist($refund);
        $em->flush();

        $this->addFlash('success', 'Congratulations, you have successfully rejected the refund.');
        return $this->redirectToRoute('app_dashboard_refund');
    }
}

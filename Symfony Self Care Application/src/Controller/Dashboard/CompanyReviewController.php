<?php

namespace App\Controller\Dashboard;

use App\Entity\Company;
use App\Entity\CompanyReview;
use App\Helper\MailHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyReviewController extends AbstractController
{
    #[Route('/dashboard/review', name: 'dashboard_review_index')]
    public function index(): Response
    {
        return $this->render('dashboard/company/reviews/index.html.twig', [
            'pageTitle' => 'Reviews'
        ]);
    }

    #[Route('/dashboard/review/{uuid}', name: 'dashboard_review_preview')]
    public function preview(EntityManagerInterface $em, $uuid, TranslatorInterface $translator)
    {
        /** @var CompanyReview $review */
        $review = $em->getRepository(CompanyReview::class)->findOneBy(['uuid' => $uuid]);

        if (!isset($review)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_review_index');
        }

        return $this->render('frontend/pages/review.html.twig', [
            'company' => $review->getCompany(),
            'review' => $review
        ]);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/dashboard/review/actions/{action}/{uuid}', name: 'dashboard_review_actions')]
    public function actions(EntityManagerInterface $em, $action, $uuid, TranslatorInterface $translator, MailHelper $mail): RedirectResponse
    {
        /** @var CompanyReview $review */
        $review = $em->getRepository(CompanyReview::class)->findOneBy(['uuid' => $uuid]);
        $subject = $translator->trans('mail.subject_published_review', [], 'messages');

        if (!isset($review)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_review_index');
        }

        switch ($action) {
            case 'moderate':
                // Send email only published review
                if ($review->getStatus() !== CompanyReview::STATUS_APPROVED && !$review->isEmailSent()) {
                    // Send email to @user
                    $emailSend = $mail->sendMail(
                        $review->getEmail(),
                        $subject,
                        'frontend/emails/published-review.html.twig',
                        [
                            'pageTitle' => $subject,
                            'name' => $review->getName(),
                            'surname' => $review->getSurname(),
                            'review' => $review->getReview(),
                            'slug' => $review->getCompany()->getSlug(),
                        ]
                    );

                    // Update flag
                    $review->setEmailSent(true);

                    // Check send email
                    if (!$emailSend) {
                        // Set flash message
                        $this->addFlash('danger', $translator->trans('form.default.required', [], 'messages'));
                        return $this->redirectToRoute('dashboard_review_index');
                    }
                }

                $review->setStatus($review->getStatus() === CompanyReview::STATUS_APPROVED ? CompanyReview::STATUS_PENDING : CompanyReview::STATUS_APPROVED);
                break;
            case 'draft':
                $review->setStatus(CompanyReview::STATUS_DRAFT);
                break;
            case 'remove':
                $review->setDeletedAt(new DateTime());
                break;
            default:
                // Set flash message
                $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
                return $this->redirectToRoute('dashboard_review_index');
        }

        // Update data
        $em->persist($review);
        $em->flush();

        /** @var Company $company */
        $company = $review->getCompany();
        $company->updateCompanyReviewsRating();

        // Update data
        $em->persist($company);
        $em->flush();

        // Set flash message
        $this->addFlash('success', $translator->trans('controller.success_updated', [], 'messages'));
        return $this->redirectToRoute('dashboard_review_index');
    }
}

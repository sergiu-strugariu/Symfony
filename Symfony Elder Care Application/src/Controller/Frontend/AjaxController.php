<?php

namespace App\Controller\Frontend;

use App\Helper\FormValidatorHelper;
use App\Helper\MailHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     * @Route("/ajax/contact-mail", name="ajax_contact_mail")
     * @throws TransportExceptionInterface
     */
    public function index(Request $request, MailHelper $mailHelper, FormValidatorHelper $validatorHelper): Response
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $emailSend = false;

        // Retrieve form data from request
        $formData = $request->request->all();

        // Check errors
        if ($request->isMethod('POST')) {
            $validate = $validatorHelper->validate($formData);

            if (!$validate['checkErrors']) {
                // Send email to @appEmail
                $emailSend = $mailHelper->sendMail(
                    $this->getParameter('app_email'),
                    'Contact',
                    'frontend/emails/contact.html.twig', $formData
                );
            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $emailSend ? 'Felicitări! Cererea ta a fost trimisă cu succes. Vom reveni în cel mai scurt timp posibil' : 'A intervenit o eroare neprevăzută. Te rugăm să încerci din nou sau mai târziu'
        ]);
    }
}

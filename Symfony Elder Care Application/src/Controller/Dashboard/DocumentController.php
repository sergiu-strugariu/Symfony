<?php

namespace App\Controller\Dashboard;

use App\Repository\DocumentRepository;
use App\Repository\PacientFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController {

    const TABS = [
        [
            'path' => 'dashboard_documents_draft',
            'path_params' => [],
            'name' => 'Intocmire documente',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_RECEPTION']
        ],
        [
            'path' => 'dashboard_documents',
            'path_params' => [
                'type' => 1
            ],
            'name' => 'Contracte',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_documents',
            'path_params' => [
                'type' => 2
            ],
            'name' => 'Acord GDPR',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_documents',
            'path_params' => [
                'type' => 12
            ],
            'name' => 'Decizii admitere in centru',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ]
    ];

    /**
     * @Route("/dashboard/documents", name="dashboard_documents")
     */
    public function index(Request $request): Response {
        if ($this->isGranted('ROLE_RECEPTION')) {
            return $this->redirectToRoute('dashboard_documents_draft');
        }

        $type = $request->get('type');
        return $this->render('dashboard/document/index.html.twig', [
                    'tabs' => self::TABS,
                    'type' => $type
        ]);
    }

    /**
     * @Route("/dashboard/documents/draft", name="dashboard_documents_draft")
     */
    public function draft(Request $request, PacientFileRepository $pacientFileRepository): Response {
        $date = $request->get('date', date('m-Y'));
        $deadline = $request->get('deadline', true);
        $user = $this->getUser();
        $filter = explode('-', $date);
        $month = $filter[0];
        $year = $filter[1];

        $pacientFilesWithoutDeadlineCount = $pacientFileRepository->findPacientFilesByResponsibleUser($user, false, $month, $year, true);
        $pacientFilesWithDeadlineCount = $pacientFileRepository->findPacientFilesByResponsibleUser($user, true, $month, $year, true);
        $pacientFiles = $pacientFileRepository->findPacientFilesByResponsibleUser($user, $deadline, $month, $year);
        $pacientFilesDeadlines = $pacientFileRepository->findPacientFilesDeadlinesByResponsibleUser($user, $month, $year);

        return $this->render('dashboard/document/draft.html.twig', [
                    'tabs' => self::TABS,
                    'deadline' => $deadline,
                    'date' => $date,
                    'pacientFiles' => $pacientFiles,
                    'pacientFilesWithDeadlineCount' => $pacientFilesWithDeadlineCount,
                    'pacientFilesWithoutDeadlineCount' => $pacientFilesWithoutDeadlineCount,
                    'pacientFilesDeadlines' => $pacientFilesDeadlines
        ]);
    }

    /**
     * @Route("/dashboard/document/{slug}", name="dashboard_document")
     */
    public function document(DocumentRepository $documentRepository, $slug): Response {
        $template = 'dashboard/document/document.html.twig';
        $document = $documentRepository->findOneBy(['slug' => $slug]);
        
        if (null === $document) {
            return $this->redirectToRoute('dashboard');
        }
        
        $params = [
            'slug' => $slug
        ];

       $params['name'] = $document->getName();

        return $this->render($template, $params);
    }
}

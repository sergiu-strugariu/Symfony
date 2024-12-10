<?php

namespace App\Controller\Dashboard;

use App\Entity\City;
use App\Entity\County;
use App\Entity\Document;
use App\Entity\DocumentData;
use App\Entity\DocumentNumber;
use App\Entity\Email;
use App\Entity\Menu;
use App\Entity\Nps;
use App\Entity\NursingHome;
use App\Entity\NursingHomeRoom;
use App\Entity\Pacient;
use App\Entity\PacientClinicalExam;
use App\Entity\PacientCookFood;
use App\Entity\PacientDischarge;
use App\Entity\PacientFile;
use App\Entity\PacientFileView;
use App\Entity\PacientGeneralData;
use App\Entity\PacientMedicalData;
use App\Entity\PacientMedication;
use App\Entity\PacientMedicationDetails;
use App\Entity\PacientMonitoringMedical;
use App\Entity\PacientOrderlyData;
use App\Entity\PacientPhysicalData;
use App\Entity\PacientRoom;
use App\Entity\PacientSample;
use App\Entity\PacientVisitCalendar;
use App\Entity\Prospect;
use App\Entity\SummaryNotification;
use App\Entity\SummaryPacient;
use App\Entity\SummaryType;
use App\Entity\User;
use App\Entity\UserBillingData;
use App\Entity\UserRelation;
use App\Form\Type\ChangePasswordFormType;
use App\Form\Type\NursingHomeFormType;
use App\Form\Type\NursingHomeRoomFormType;
use App\Form\Type\PacientClinicalExamFormType;
use App\Form\Type\PacientCookFoodFormType;
use App\Form\Type\PacientDiagnosisFormType;
use App\Form\Type\PacientDischargeFormType;
use App\Form\Type\PacientFileFormType;
use App\Form\Type\PacientGeneralDataFormType;
use App\Form\Type\PacientMedicalDataFormType;
use App\Form\Type\PacientMedicationDetailsFormType;
use App\Form\Type\PacientMonitoringMedicalFormType;
use App\Form\Type\PacientMonitoringOrderlyFormType;
use App\Form\Type\PacientMonitoringPhysicalFormType;
use App\Form\Type\PacientPersonalDataFormType;
use App\Form\Type\PacientSampleFormType;
use App\Form\Type\ProspectFormType;
use App\Form\Type\SummaryPacientFormType;
use App\Form\Type\UserPersonalDataFormType;
use App\Helper\DatatableHelper;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\FormValidatorHelper;
use App\Helper\TokenGenerator;
use App\Mailer\TwigMailer;
use App\Repository\CityRepository;
use App\Repository\CookMenuRepository;
use App\Repository\CountyRepository;
use App\Repository\EmailRepository;
use App\Repository\MenuRepository;
use App\Repository\NursingHomeRepository;
use App\Repository\NursingHomeRoomRepository;
use App\Repository\PacientAdmissionRepository;
use App\Repository\PacientClinicalExamRepository;
use App\Repository\PacientCookFoodRepository;
use App\Repository\PacientDiagnosisRepository;
use App\Repository\PacientDischargeRepository;
use App\Repository\PacientFileGroupRepository;
use App\Repository\PacientFileRepository;
use App\Repository\PacientGeneralDataRepository;
use App\Repository\PacientMedicalDataRepository;
use App\Repository\PacientMedicationDetailsRepository;
use App\Repository\PacientMedicationRepository;
use App\Repository\PacientMonitoringMedicalRepository;
use App\Repository\PacientOrderlyDataRepository;
use App\Repository\PacientPhysicalDataRepository;
use App\Repository\PacientRepository;
use App\Repository\PacientSampleRepository;
use App\Repository\PacientVisitCalendarRepository;
use App\Repository\PageRepository;
use App\Repository\ProspectRepository;
use App\Repository\SummaryPacientRepository;
use App\Repository\SummaryRepository;
use App\Repository\UserRelationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

class AjaxController extends AbstractController
{
    /**
     * @Route("/dashboard/ajax/users", name="dashboard_ajax_users")
     */
    public function users(Request $request, UserRepository $userRepository, DatatableHelper $datatableHelper): Response
    {
        $user = $this->getUser();
        $params = $request->query->all();

        $role = 'ROLE_ADMIN';
        if (!empty($params['role'])) {
            $role = $params['role'];
        }

        $data = $userRepository->findUsersByRole($user, $role);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacients", name="dashboard_ajax_pacients")
     */
    public function pacients(Request $request, PacientRepository $pacientRepository, PacientFileRepository $pacientFileRepository, DatatableHelper $datatableHelper): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $params = $request->query->all();
        $showAdmittedOnly = false;

        if ($this->isGranted('ROLE_ASSISTANCE_MEDICAL') || $this->isGranted('ROLE_MEDIC') || $this->isGranted('ROLE_PHYSICAL_THERAPY')) {
            $showAdmittedOnly = true;
        }

        $data = $pacientRepository->findPacientsForDatatable($user, $showAdmittedOnly);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $data = array_map(function ($item) use ($pacientFileRepository) {
            // get files count
            $item['uploadedFilesCount'] = $pacientFileRepository->findFilesCountByPacientIdAndStatus($item['id'], PacientFile::STATUS_UPLOADED);
            $item['waitingFilesCount'] = $pacientFileRepository->findFilesCountByPacientIdAndStatus($item['id'], PacientFile::STATUS_WAITING);
            $item['role'] = $this->getUser()->getRole();

            return $item;
        }, $data);

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/cook-menus", name="dashboard_ajax_cook_menus")
     */
    public function cookMenus(Request $request, CookMenuRepository $cookMenuRepository, DatatableHelper $datatableHelper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $params = $request->query->all();

        $data = $cookMenuRepository->findDataForDatatable($user);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacients/admissions", name="dashboard_ajax_pacients_admissions")
     */
    public function admissions(Request $request, PacientAdmissionRepository $pacientAdmissionRepository, DatatableHelper $datatableHelper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $params = $request->query->all();

        $data = $pacientAdmissionRepository->findDataForDatatable($user);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacients/discharges", name="dashboard_ajax_pacients_discharges")
     */
    public function discharges(Request $request, PacientDischargeRepository $pacientDischargeRepository, DatatableHelper $datatableHelper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $params = $request->query->all();

        $data = $pacientDischargeRepository->findDataForDatatable(null, $user);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacients/prospects/{type}", name="dashboard_ajax_pacients_prospects")
     */
    public function pacientsProspects(Request $request, ProspectRepository $prospectRepository, DatatableHelper $datatableHelper, $type): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $params = $request->query->all();

        $status = (isset($params['status']) && !empty($params['status'])) ? $params['status'] : null;
        $date = (isset($params['date']) && !empty($params['date'])) ? $params['date'] : null;

        $data = $prospectRepository->findProspectsForDatatable($user, $type, $date, '%m-%Y', $status);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/documents/{type}", name="dashboard_ajax_documents")
     */
    public function documents(Request $request, PacientFileRepository $pacientFileRepository, DatatableHelper $datatableHelper, $type): Response
    {
        $params = $request->query->all();


        $data = $pacientFileRepository->findDocumentsForDatatable($type);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/visits", name="dashboard_ajax_visits")
     */
    public function visits(Request $request, PacientVisitCalendarRepository $pacientVisitCalendarRepository, DatatableHelper $datatableHelper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $params = $request->query->all();

        $data = $pacientVisitCalendarRepository->findVisitsForDatatable($user, $this->isGranted('ROLE_MEDIC'), $params['filter']);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/visits/calendar", name="dashboard_ajax_visits_calendar")
     */
    public function visitsCalendar(Request $request, PacientVisitCalendarRepository $pacientVisitCalendarRepository): Response
    {
        $params = $request->query->all();
        $nursingHomeUuid = $params['location'];
        $startDate = new \DateTime($params['start']);
        $endDate = new \DateTime($params['end']);
        $showAdmittedOnly = false;

        if ($this->isGranted('ROLE_ASSISTANCE_MEDICAL')) {
            $showAdmittedOnly = true;
        }

        $data = $pacientVisitCalendarRepository->findVisitsByDateAndNursingHome($showAdmittedOnly, $nursingHomeUuid, $startDate, $endDate);

        return $this->json($data);
    }

    /**
     * @Route("/dashboard/ajax/visits/calendar/add", name="dashboard_ajax_visits_calendar_add")
     */
    public function visitsCalendarAdd(Request $request, PacientRepository $pacientRepository, NursingHomeRepository $nursingHomeRepository, EntityManagerInterface $em): Response
    {
        $params = $request->request->all();

        // check request params
        if (empty($params['pacientUuid']) ||
            empty($params['locationUuid']) ||
            empty($params['startDate']) ||
            empty($params['endDate']) ||
            empty($params['type'])
        ) {

            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa completezi toate datele'
            ]);
        }

        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $params['pacientUuid']]);
        // check if user exists
        if (null === $pacient || $this->isGranted('ROLE_ASSISTANCE_MEDICAL')) {
            return $this->json([
                'success' => false,
                'message' => 'Pacient invalid'
            ]);
        }

        // find location by UUID
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['locationUuid']]);
        // check if user exists
        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Locatie invalida'
            ]);
        }

        $uuid = Uuid::v4();
        $visit = new PacientVisitCalendar();
        $visit->setUid($uuid);
        $visit->setPacient($pacient);
        $visit->setNursingHome($nursingHome);
        $visit->setStatus(PacientVisitCalendar::VISIT_STATUS_PENDING);
        $visit->setStartDate(new \DateTime($params['startDate']));
        $visit->setEndDate(new \DateTime($params['endDate']));
        $visit->setCreatedAt(new \DateTime());
        $visit->setType($params['type']);
        if (!empty($params['observations'])) {
            $visit->setObservations($params['observations']);
        }

        $em->persist($visit);
        $em->flush();

        return $this->json([
            'success' => true,
            'uuid' => $uuid
        ]);
    }

    /**
     * @Route("/dashboard/ajax/visits/calendar/{uuid}/edit", name="dashboard_ajax_visits_calendar_edit")
     */
    public function visitsCalendarEdit(Request $request, PacientVisitCalendarRepository $pacientVisitCalendarRepository, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        $visit = $pacientVisitCalendarRepository->findOneBy(['uid' => $uuid]);
        if (null === $visit || $this->isGranted('ROLE_ASSISTANCE_MEDICAL')) {
            return $this->json([
                'success' => false,
                'message' => 'Vizita invalida'
            ]);
        }

        $params = $request->request->all();

        // check request params
        if (empty($params['pacientUuid']) || empty($params['startDate']) || empty($params['endDate'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa completezi toate datele'
            ]);
        }

        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $params['pacientUuid']]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Pacient invalid'
            ]);
        }

        $visit->setPacient($pacient);
        $visit->setStartDate(new \DateTime($params['startDate']));
        $visit->setEndDate(new \DateTime($params['endDate']));
        if (!empty($params['observations'])) {
            $visit->setObservations($params['observations']);
        }

        $em->persist($visit);
        $em->flush();

        return $this->json([
            'success' => true,
            'uuid' => $uuid
        ]);
    }

    /**
     * @Route("/dashboard/ajax/visits/calendar/{uuid}/delete", name="dashboard_ajax_visits_calendar_delete")
     */
    public function visitsCalendarDelete(PacientVisitCalendarRepository $pacientVisitCalendarRepository, EntityManagerInterface $em, $uuid): Response
    {
        $visit = $pacientVisitCalendarRepository->findOneBy(['uid' => $uuid]);
        if (null === $visit || $this->isGranted('ROLE_ASSISTANCE_MEDICAL')) {
            return $this->json([
                'success' => false,
                'message' => 'Vizita invalida'
            ]);
        }

        $em->remove($visit);
        $em->flush();

        return $this->json([
            'success' => true
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-homes", name="dashboard_ajax_nursing_homes")
     */
    public function nursingHomes(Request $request, NursingHomeRepository $nursingHomeRepository, DatatableHelper $datatableHelper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $params = $request->query->all();

        $data = $nursingHomeRepository->findNursingHomesForDatatable($user);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/data/{type}", name="dashboard_ajax_pacient_data")
     */
    public function getPacientData(Request $request, PacientRepository $pacientRepository, PacientGeneralDataRepository $pacientGeneralDataRepository, PacientMedicalDataRepository $pacientMedicalDataRepository, PacientClinicalExamRepository $pacientClinicalExamRepository, PacientDischargeRepository $pacientDischargeRepository, DatatableHelper $datatableHelper, $uuid, $type): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $params = $request->query->all();

        switch ($type) {
            case 'general':
                $data = $pacientGeneralDataRepository->findDataForDatatable($pacient);
                break;
            case 'medical':
                $data = $pacientMedicalDataRepository->findDataForDatatable($pacient);
                break;
            case 'clinical-exam':
                $data = $pacientClinicalExamRepository->findDataForDatatable($pacient);
                break;
            case 'discharge':
                $data = $pacientDischargeRepository->findDataForDatatable($pacient, $user);
                break;
            default:
                $data = [];
                break;
        }

        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/relations", name="dashboard_ajax_pacient_relations")
     */
    public function pacientRelations(Request $request, PacientRepository $pacientRepository, UserRelationRepository $userRelationRepository, DatatableHelper $datatableHelper, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $params = $request->query->all();

        $data = $userRelationRepository->findRelationsByUser($pacient);
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/daily-monitoring/medical", name="dashboard_ajax_pacient_daily_monitoring_medical")
     */
    public function pacientDailyMonitoringMedical(Request $request, UserRepository $userRepository, PacientMonitoringMedicalRepository $pacientMonitoringMedicalRepository, DatatableHelper $datatableHelper, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $userRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $params = $request->query->all();

        $data = $pacientMonitoringMedicalRepository->findMonitoringMedicalByPacient($pacient->getId(), null, 'DESC');
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacient/medication-details/{id}/form", name="dashboard_ajax_pacient_medication_details_form")
     */
    public function loadPacientMedicationDetailsForm(PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, $id): Response
    {
        $pacientMedicationDetails = $pacientMedicationDetailsRepository->find($id);
        if (null === $pacientMedicationDetails) {
            return new Response('');
        }

        $form = $this->createForm(PacientMedicationDetailsFormType::class, $pacientMedicationDetails);

        return $this->render('dashboard/pacient/_pacient_medication_details_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/medication-details/{id}/update", name="dashboard_ajax_pacient_medication_details_update")
     */
    public function updatePacientMedicationDetails(Request $request, PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, EntityManagerInterface $em, $id): Response
    {
        $pacientMedicationDetails = $pacientMedicationDetailsRepository->find($id);
        if (null === $pacientMedicationDetails) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm(PacientMedicationDetailsFormType::class, $pacientMedicationDetails);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($pacientMedicationDetails);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/medication-details/bulk-update", name="dashboard_ajax_pacient_medication_details_bulk_update")
     */
    public function bulkUpdatePacientMedicationDetails(Request $request, PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, EntityManagerInterface $em): Response
    {
        $medications = $request->get('medications');

        if (empty($medications)) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        foreach ($medications as $medicationId) {
            $medication = $pacientMedicationDetailsRepository->find($medicationId);
            if (null !== $medication) {
                $medication->setStatus(PacientMedicationDetails::STATUS_ADMINISTERED);
                $em->persist($medication);
                $em->flush();
            }
        }

        return $this->json([
            'success' => true,
            'message' => ''
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/diagnosis/{id}/form", name="dashboard_ajax_pacient_diagnosis_form")
     */
    public function loadPacientDiagnosisForm(PacientDiagnosisRepository $pacientDiagnosisRepository, $id): Response
    {
        $pacientDiagnosis = $pacientDiagnosisRepository->find($id);
        if (null === $pacientDiagnosis) {
            return new Response('');
        }

        $form = $this->createForm(PacientDiagnosisFormType::class, $pacientDiagnosis);

        return $this->render('dashboard/pacient/_pacient_diagnosis.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/diagnosis/{id}/update", name="dashboard_ajax_pacient_diagnosis_update")
     */
    public function updatePacientDiagnosis(Request $request, PacientDiagnosisRepository $pacientDiagnosisRepository, EntityManagerInterface $em, $id): Response
    {
        $pacientDiagnosis = $pacientDiagnosisRepository->find($id);
        if (null === $pacientDiagnosis) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm(PacientDiagnosisFormType::class, $pacientDiagnosis);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($pacientDiagnosis);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/diagnosis/{id}/delete", name="dashboard_ajax_pacient_diagnosis_delete")
     */
    public function deletePacientDiagnosis(PacientDiagnosisRepository $pacientDiagnosisRepository, EntityManagerInterface $em, $id): Response
    {
        $pacientDiagnosis = $pacientDiagnosisRepository->find($id);
        if (null === $pacientDiagnosis) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // soft delete
        $pacientDiagnosis->setDeletedAt(new \DateTime());
        // write changes to DB
        $em->persist($pacientDiagnosis);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Diagnosticul a fost sters cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-homes/add-nursing-home", name="dashboard_nursing_home_ajax_add_nursing_home")
     */
    public function addNursingHome(Request $request, EntityManagerInterface $em, DefaultHelper $helper): Response
    {
        $nursingHome = new NursingHome();

        $form = $this->createForm(NursingHomeFormType::class, $nursingHome);
        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->json([
                    'success' => false,
                    'message' => $helper->parseBackendError($form->getErrors(true))
                ]);
            }
        }

        // Set user
        $nursingHome->setUser($user);

        // Persist and save
        $em->persist($nursingHome);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Căminul a fost adăugat cu success.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/quick-add-user", name="dashboard_ajax_quick_add_user")
     */
    public function quickAddUser(Request $request, UserRepository $userRepository, NursingHomeRepository $nursingHomeRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // get request data
        $params = $request->request->all();
        $uuid = Uuid::v4();

        // check request data
        if (empty($params['email']) || empty($params['role']) || empty($params['nursing-home'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }
        // check if email already exists in the database
        $userExists = $userRepository->findOneBy(['email' => $params['email']]);

        if (null !== $userExists) {
            return $this->json([
                'success' => false,
                'message' => 'Ne pare rau, dar exista deja un utilizator înregistrat cu aceasta adresa de email.'
            ]);
        }

        /** @var NursingHome $nursingHome */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['nursing-home']]);

        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Ne pare rau, dar căminul selectat nu exista.'
            ]);
        }

        // create new User
        $user = new User();
        $user->setEmail($params['email']);
        $user->addRole($params['role']);
        $user->setUid($uuid);
        $user->setCreatedAt(new \DateTime());
        $user->setStatus(User::STATUS_ACTIVE);
        $user->setNursingHome($nursingHome);

        // generate random password
        $randomPassword = TokenGenerator::generateToken(8);
        $hashedPassword = $passwordHasher->hashPassword($user, $randomPassword);

        $user->setPassword($hashedPassword);

        // save user to DB
        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => '',
            'redirect' => $this->generateUrl('dashboard_user_view', ['uuid' => $uuid])
        ]);
    }

    /**
     * @Route("/dashboard/ajax/quick-add-pacient", name="dashboard_ajax_quick_add_pacient")
     */
    public function quickAddPacient(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, NursingHomeRepository $nursingHomeRepository): Response
    {
        // get request data
        $params = $request->request->all();

        // check request data
        if (empty($params['firstName']) || empty($params['lastName']) || empty($params['nursing-home'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        // get nursing home for logged in user
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['nursing-home']]);

        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Ne pare rau, dar căminul selectat nu exista.'
            ]);
        }

        // generate UUID
        $uuid = Uuid::v4();

        // create new User
        $pacient = new Pacient();
        $pacient->setFirstName($params['firstName']);
        $pacient->setLastName($params['lastName']);
        $pacient->setUid($uuid);
        $pacient->setCreatedAt(new \DateTime());
        $pacient->setStatus(Pacient::STATUS_PENDING);
        $pacient->setNursingHome($nursingHome);
        $sluggedNursingHomeName = $slugger->slug($nursingHome->getName(), '-');


        $sluggedName = $slugger->slug(sprintf('%s %s', $params['lastName'], $params['firstName']), '.');
        $email = sprintf('%s@pacient-%s.ro', strtolower($sluggedName), strtolower($sluggedNursingHomeName));
        $pacient->setEmail($email);

        // save user to DB
        $em->persist($pacient);
        $em->flush();

        return $this->json([
            'success' => true
        ]);
    }

    /**
     * @Route("/dashboard/ajax/quick-add-prospect", name="dashboard_ajax_quick_add_prospect")
     */
    public function quickAddProspect(Request $request, EntityManagerInterface $em, NursingHomeRepository $nursingHomeRepository): Response
    {
        // get request data
        $params = $request->request->all();

        // check request data
        if (empty($params['relationName']) || empty($params['nursing-home'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        /**
         * Get nursing home for logged in user
         * @var User $user
         */
        $user = $this->getUser();

        /** @var NursingHome $nursingHome */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['nursing-home']]);

        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Ne pare rau, dar căminul selectat nu exista.'
            ]);
        }

        // generate UUID
        $uuid = Uuid::v4();

        // create new Prospect
        $prospect = new Prospect();
        $prospect->setRelationName($params['relationName']);
        $prospect->setUid($uuid);
        $prospect->setCreatedAt(new \DateTime());
        $prospect->setStatus(Prospect::STATUS_IN_PROGRESS);
        $prospect->setAddedBy($user);
        $prospect->setNursingHome($nursingHome);

        // write prospect to DB
        $em->persist($prospect);
        $em->flush();

        return $this->json([
            'success' => true,
            'redirect' => $this->generateUrl('dashboard_pacient_prospect', ['uuid' => $uuid])
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/quick-add-relation", name="dashboard_ajax_pacient_quick_add_relation")
     */
    public function quickAddRelation(Request $request, UserRepository $userRepository, PacientRepository $pacientRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }
        // get request data
        $params = $request->request->all();
        // check request data
        if (empty($params['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }
        // check if user already exists in the database
        $user = $userRepository->findOneBy(['email' => $params['email']]);
        if (null === $user) {
            // get nursing home for logged in user 
            $nursingHome = $this->getUser()->getNursingHome();
            // generate UUID
            $relationUuid = Uuid::v4();
            // create new User
            $user = new User();
            $user->setEmail($params['email']);
            $user->addRole('ROLE_RELATION');
            $user->setUid($relationUuid);
            $user->setCreatedAt(new \DateTime());
            $user->setStatus(User::STATUS_ACTIVE);
            if (null !== $nursingHome) {
                $user->setNursingHome($nursingHome);
            }
            if (!empty($params['firstName'])) {
                $user->setFirstName($params['firstName']);
            }
            if (!empty($params['lastName'])) {
                $user->setLastName($params['lastName']);
            }
            if (!empty($params['phoneNumber'])) {
                $user->setPhoneNumber($params['phoneNumber']);
            }

            // generate random password
            $randomPassword = TokenGenerator::generateToken(8);
            $hashedPassword = $passwordHasher->hashPassword(
                $user, $randomPassword
            );

            $user->setPassword($hashedPassword);
        }

        $userRelation = new UserRelation();
        $userRelation->setUid(Uuid::v4());
        $userRelation->setPacient($pacient);
        $userRelation->setPacientRelation($user);
        $userRelation->setCreatedAt(new \DateTime());

        // write user to DB
        $em->persist($user);
        $em->persist($userRelation);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => '',
            'redirect' => $this->generateUrl('dashboard_pacient_relations', ['uuid' => $uuid])
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/quick-add-data/{type}", name="dashboard_ajax_pacient_quick_add_data")
     */
    public function quickAddPacientData(PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid, $type): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        // get logged in user 
        $user = $this->getUser();
        // generate UUID
        $dataUuid = Uuid::v4();
        $pacientData = null;

        switch ($type) {
            case 'general':
                // create new PacientGeneralData
                $pacientData = new PacientGeneralData();
                $pacientData->setPacient($pacient);
                $pacientData->setAddedBy($user);
                $pacientData->setUid($dataUuid);
                $pacientData->setRequiresDiapers(false);
                $pacientData->setRequiresMedicalBed(false);
                $pacientData->setCreatedAt(new \DateTime());
                $route = 'dashboard_pacient_general_data';
                break;
            case 'medical':
                // create new PacientMedicalData
                $pacientData = new PacientMedicalData();
                $pacientData->setPacient($pacient);
                $pacientData->setAddedBy($user);
                $pacientData->setUid($dataUuid);
                $pacientData->setVaccinatedAgainstCovid(false);
                $pacientData->setHadCovid(false);
                $pacientData->setCreatedAt(new \DateTime());
                $route = 'dashboard_pacient_medical_data';
                break;
            case 'clinical-exam':
                // create new PacientClinicalExam
                $pacientData = new PacientClinicalExam();
                $pacientData->setPacient($pacient);
                $pacientData->setAddedBy($user);
                $pacientData->setUid($dataUuid);
                $pacientData->setCreatedAt(new \DateTime());
                $route = 'dashboard_pacient_clinical_exam';
                break;
            case 'discharge':
                // create new PacientClinicalExam
                $pacientData = new PacientDischarge();
                $pacientData->setPacient($pacient);
                $pacientData->setDischargedBy($user);
                $pacientData->setUid($dataUuid);
                $pacientData->setDischargeDate(new \DateTime());
                $route = 'dashboard_pacient_discharge_data';
                break;
            default:
                break;
        }

        if (null === $pacientData) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // write data to DB
        $em->persist($pacientData);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => '',
            'redirect' => $this->generateUrl($route, ['uuid' => $uuid, 'edit' => $dataUuid])
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/diagnoses", name="dashboard_ajax_pacient_get_diagnoses")
     */
    public function getPacientDiagnoses(PacientRepository $pacientRepository, PacientDiagnosisRepository $pacientDiagnosisRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $pacientDiagnoses = $pacientDiagnosisRepository->findDiagnosesByPacient($pacient);

        return $this->json([
            'success' => true,
            'data' => $pacientDiagnoses
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/delete", name="dashboard_ajax_pacient_delete")
     */
    public function deletePacient(PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        try {
            $em->remove($pacient);
            $em->flush();
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Acest pacient nu poate fi sters in mod automat'
            ]);
        }

        return $this->json([
            'success' => true
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/archive", name="dashboard_ajax_pacient_archive")
     */
    public function archivePacient(PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $pacient->setStatus(Pacient::STATUS_ARCHIVED);
        $em->persist($pacient);
        $em->flush();

        return $this->json([
            'success' => true
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/medication/add", name="dashboard_ajax_pacient_add_medication")
     */
    public function addPacientMedication(Request $request, PacientRepository $pacientRepository, PacientDiagnosisRepository $pacientDiagnosisRepository, PacientMedicationRepository $pacientMedicationRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $params = $request->request->all();
        $asNecessary = false;
        if (isset($params['as-necessary']) && $params['as-necessary'] == 1) {
            $asNecessary = true;
            if (empty($params['diagnosis']) || empty($params['drug']) || empty($params['dose']) || empty($params['asNecessaryObservations'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Date invalide'
                ]);
            }
        } else {
            if (empty($params['diagnosis']) || empty($params['drug']) || empty($params['dose']) || empty($params['startDate']) || empty($params['endDate']) || empty($params['details'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Date invalide'
                ]);
            }
        }

        // find diagnosis by id
        $pacientDiagnosis = $pacientDiagnosisRepository->find($params['diagnosis']);
        // check if pacient diagnosis exists
        if (null === $pacientDiagnosis) {
            return $this->json([
                'success' => false,
                'message' => 'Diagnostic invalid'
            ]);
        }

        // check if add or update action
        $medicationId = $params['medicationId'];
        $update = false;
        if (!empty($medicationId)) {
            $pacientMedication = $pacientMedicationRepository->find($medicationId);
            if (null === $pacientMedication) {
                return $this->json([
                    'success' => false,
                    'message' => 'Date invalide'
                ]);
            }

            $update = true;
        }

        $loggedUser = $this->getUser();
        if (!empty($params['notificationDate'])) {
            $summaryNotification = new SummaryNotification();
            $summaryNotification->setPacient($pacient);
            $summaryNotification->setPacientDiagnosis($pacientDiagnosis);
            $summaryNotification->setCreatedBy($loggedUser);
            $summaryNotification->setNotificationDate(\DateTime::createFromFormat('Y-m-d', $params['notificationDate']));
            $summaryNotification->setCreatedAt(new \DateTime());
            if (!empty($params['notificationObservations'])) {
                $summaryNotification->setObservations($params['notificationObservations']);
            }

            $em->persist($summaryNotification);
            $em->flush();
        }

        $medicationDetails = '';
        if ($asNecessary) {
            $medicationDetails = $params['asNecessaryObservations'];
        } else {
            $scheduledMedicationCount = count($params['details']);
            foreach ($params['details'] as $key => $item) {
                $medicationDetails .= $item['hour'];
                if (!empty($item['observations'])) {
                    $medicationDetails .= ' (' . $item['observations'] . ')';
                }
                $medicationDetails .= ' ';
            }
        }

        if (!$update) {
            $pacientMedication = new PacientMedication();
            $pacientMedication->setStatus(PacientMedication::STATUS_ACTIVE);
            $pacientMedication->setCreatedAt(new \DateTime());
            $pacientMedication->setPacient($pacient);
            $pacientMedication->setMedic($loggedUser);
        }

        $pacientMedication->setPacientDiagnosis($pacientDiagnosis);
        if ($asNecessary) {
            $pacientMedication->setDetails(sprintf('Se administreaza la nevoie %s / %s cu urmatoarele observatii: %s', $params['drug'], $params['dose'], $medicationDetails));
        } else {
            $pacientMedication->setDetails(sprintf('Din data %s pana la %s se administreaza %s / %s de %s ori pe zi la ora %s', $params['startDate'], $params['endDate'], $params['drug'], $params['dose'], $scheduledMedicationCount, $medicationDetails));
        }

        $pacientMedication->setAsNecessary($asNecessary);
        $em->persist($pacientMedication);
        $em->flush();

        if ($update) {
            $pacientMedicationDetails = $pacientMedication->getPacientMedicationDetails();
            foreach ($pacientMedicationDetails as $pacientMedicationDetail) {
                $em->remove($pacientMedicationDetail);
            }
            $em->flush();
        }

        if ($asNecessary) {
            $pacientMedicationDetails = new PacientMedicationDetails();
            $pacientMedicationDetails->setPacient($pacient);
            $pacientMedicationDetails->setPacientMedication($pacientMedication);
            $pacientMedicationDetails->setDrug($params['drug']);
            $pacientMedicationDetails->setDose($params['dose']);
            $pacientMedicationDetails->setObservations($params['asNecessaryObservations']);
            $pacientMedicationDetails->setStatus(PacientMedicationDetails::STATUS_PLANNED);

            $em->persist($pacientMedicationDetails);
            $em->flush();
        } else {
            $dateRange = $this->getBetweenDates($params['startDate'], $params['endDate']);
            foreach ($dateRange as $date) {
                foreach ($params['details'] as $key => $item) {
                    $pacientMedicationDetails = new PacientMedicationDetails();
                    $pacientMedicationDetails->setPacient($pacient);
                    $pacientMedicationDetails->setPacientMedication($pacientMedication);
                    $pacientMedicationDetails->setDrug($params['drug']);
                    $pacientMedicationDetails->setDose($params['dose']);
                    $pacientMedicationDetails->setDate(\DateTime::createFromFormat('Y-m-d H:i', sprintf('%s %s', $date, $item['hour'])));
                    $pacientMedicationDetails->setObservations($item['observations']);
                    $pacientMedicationDetails->setStatus(PacientMedicationDetails::STATUS_PLANNED);

                    $em->persist($pacientMedicationDetails);
                    $em->flush();
                }
            }
        }

        return $this->json([
            'success' => true,
            'message' => 'Medicamentatia a fost adaugata cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/medication/{id}/stop", name="dashboard_ajax_pacient_stop_medication")
     */
    public function stopPacientMedication(PacientRepository $pacientRepository, PacientMedicationRepository $pacientMedicationRepository, EntityManagerInterface $em, $uuid, $id): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        // find medication by id & pacient
        $pacientMedication = $pacientMedicationRepository->findOneBy([
            'pacient' => $pacient,
            'id' => $id
        ]);
        // check if pacient medication exists
        if (null === $pacientMedication) {
            return $this->json([
                'success' => false,
                'message' => 'Tratament invalid'
            ]);
        }

        $pacientMedication->setStatus(PacientMedication::STATUS_STOPPED);
        $em->persist($pacientMedication);
        $em->flush();

        $filteredPacientMedicationDetails = $pacientMedication->getFilteredPacientMedicationDetails(PacientMedicationDetails::STATUS_PLANNED);
        foreach ($filteredPacientMedicationDetails as $pacientMedicationDetails) {
            $em->remove($pacientMedicationDetails);
            $em->flush();
        }

        return $this->json([
            'success' => true,
            'message' => 'Schema de tratament a fost intrerupta cu succes'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/summary-notification/add", name="dashboard_ajax_pacient_add_summary_notification")
     */
    public function addSummaryNotification(Request $request, PacientRepository $pacientRepository, PacientDiagnosisRepository $pacientDiagnosisRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $params = $request->request->all();
        if (empty($params['notificationDate']) || empty($params['notificationObservations'])) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // find diagnosis by id
        $pacientDiagnosis = $pacientDiagnosisRepository->find($params['diagnosis']);
        // create summary notification object
        $summaryNotification = new SummaryNotification();
        $summaryNotification->setPacient($pacient);
        $summaryNotification->setPacientDiagnosis($pacientDiagnosis);
        $summaryNotification->setCreatedBy($this->getUser());
        $summaryNotification->setNotificationDate(\DateTime::createFromFormat('Y-m-d', $params['notificationDate']));
        $summaryNotification->setObservations($params['notificationObservations']);
        $summaryNotification->setCreatedAt(new \DateTime());
        if (null !== $pacientDiagnosis) {
            $summaryNotification->setPacientDiagnosis($pacientDiagnosis);
        }

        $em->persist($summaryNotification);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Reminder-ul a fost adaugat cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-home/{nursingUuid}/room/{roomUuid?}/actions", name="dashboard_ajax_nursing_home_room_actions")
     */
    public function addNursingHomeRoom(Request $request, EntityManagerInterface $em, $nursingUuid, $roomUuid): Response
    {
        /** @var NursingHome $nursingHome */
        $nursingHome = $em->getRepository(NursingHome::class)->findOneBy(['uid' => $nursingUuid]);

        /** @var NursingHomeRoom $nursingHomeRoom */
        $nursingHomeRoom = empty($roomUuid) ? new NursingHomeRoom() : $em->getRepository(NursingHomeRoom::class)->findOneBy(['uid' => $roomUuid]);

        // Check data
        if (null === $nursingHome || null === $nursingHomeRoom) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm(NursingHomeRoomFormType::class, $nursingHomeRoom);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $nursingHomeRoom->setNursingHome($nursingHome);
                $nursingHomeRoom->setUid(Uuid::v4());
                // write data to db
                $em->persist($nursingHomeRoom);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Camera a fost adăugată cu succes!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa încerci mai târziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-home/{nursingUuid?}/room/load-form", name="dashboard_ajax_nursing_home_room_load_form")
     */
    public function loadNursingHomeForm(NursingHomeRoomRepository $roomRepository, $nursingUuid): Response
    {
        /** @var NursingHomeRoom $pacientFile */
        $room = $roomRepository->findOneBy(['uid' => $nursingUuid]);

        $form = $this->createForm(NursingHomeRoomFormType::class, $room);

        return $this->render('dashboard/nursing_home/__room_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-home/{nursingUuid}/room/{roomUuid}/load-pacients", name="dashboard_ajax_nursing_home_room_load_pacients")
     */
    public function loadNursingHomePacients(EntityManagerInterface $em, $nursingUuid, $roomUuid): JsonResponse
    {
        /** @var NursingHome $nursingHome */
        $nursingHome = $em->getRepository(NursingHome::class)->findOneBy([
            'uid' => $nursingUuid
        ]);

        /** @var NursingHomeRoom $nursingHomeRome */
        $nursingHomeRome = $em->getRepository(NursingHomeRoom::class)->findOneBy([
            'uid' => $roomUuid
        ]);

        // Check exist @nursingHome
        if ($nursingHome === null || null === $nursingHomeRome) {
            return $this->json([
                'success' => false,
                'numberOfBeds' => 0,
                'rows' => [],
                'message' => 'A intervenit o eroare neprevazuta, te rugam sa încerci mai târziu.'
            ]);
        }

        /**
         * Get pacients by @nursingHome
         * Set selected by @nursingHomeRome
         * @var Pacient $pacients
         */
        $pacients = $em->getRepository(Pacient::class)->getPacientsByNursingHome($nursingHome, $nursingHomeRome);

        return $this->json([
            'success' => true,
            'numberOfBeds' => $nursingHomeRome->getNumberOfBeds(),
            'rows' => $pacients
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-home/{nursingUuid}/room/add-pacients", name="dashboard_ajax_nursing_home_room_add_pacients")
     */
    public function addNursingHomePacients(EntityManagerInterface $em, Request $request, PacientRepository $pacientRepository, $nursingUuid): JsonResponse
    {
        $pacients = $request->get('pacients', []);
        $roomUuid = $request->get('nursing_room', 0);
        $existingPacientUids = [];

        /** @var NursingHome $nursingHome */
        $nursingHome = $em->getRepository(NursingHome::class)->findOneBy([
            'uid' => $nursingUuid
        ]);

        /** @var NursingHomeRoom $nursingHomeRoom */
        $nursingHomeRoom = $em->getRepository(NursingHomeRoom::class)->findOneBy([
            'uid' => $roomUuid
        ]);

        // Check exist @nursingHome
        if ($nursingHome === null || null === $nursingHomeRoom) {
            return $this->json([
                'success' => false,
                'message' => 'A intervenit o eroare neprevazuta, te rugam sa încerci mai târziu.'
            ]);
        }

        // Retrieve the list of existing patients in the specified room
        $existingPacients = $pacientRepository->findBy([
            'nursingHome' => $nursingHome,
            'nursingHomeRoom' => $nursingHomeRoom
        ]);

        // Convert the list of existing patients to an array of UIDs for comparison
        foreach ($existingPacients as $pacient) {
            $existingPacientUids[] = $pacient->getUid();
        }

        // Detect newly added patients
        $newPacientUids = array_diff($pacients, $existingPacientUids);

        // Detect removed patients
        $removedPacientUids = array_diff($existingPacientUids, $pacients);

        // Process newly added patients
        foreach ($newPacientUids as $uid) {
            /** @var Pacient $pacient */
            $pacient = $pacientRepository->findOneBy(['uid' => $uid]);

            if ($pacient) {
                $pacient->setNursingHomeRoom($nursingHomeRoom);

                $pacientRoom = new PacientRoom();
                $pacientRoom->setPacient($pacient);
                $pacientRoom->setNursingHomeRoom($nursingHomeRoom);
                $pacientRoom->setCreatedAt(new \DateTime());
                $pacientRoom->setUid(Uuid::v4());

                $em->persist($pacientRoom);
                $em->persist($pacient);
            }
        }

        // Process removed patients
        foreach ($removedPacientUids as $uid) {
            /** @var Pacient $pacient */
            $pacient = $pacientRepository->findOneBy(['uid' => $uid]);

            if ($pacient) {
                $pacient->setNursingHomeRoom(null);
                $em->persist($pacient);
            }
        }

        // Save changes to the database
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Camera a fost actualizată cu succes.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/data/{uuid}/{type}", name="dashboard_ajax_pacient_load_data")
     */
    public function loadPacientData(Request $request, PacientGeneralDataRepository $pacientGeneralDataRepository, PacientMedicalDataRepository $pacientMedicalDataRepository, PacientClinicalExamRepository $pacientClinicalExamRepository, PacientDischargeRepository $pacientDischargeRepository, $uuid, $type): Response
    {
        $viewOnly = $request->get('disabled', true);
        $pacientData = null;
        $options = [];
        if ($viewOnly) {
            $options['disabled'] = true;
        }

        switch ($type) {
            case 'general':
                // find general data by UUID
                $pacientData = $pacientGeneralDataRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientGeneralDataFormType::class;
                $template = 'dashboard/pacient/_pacient_general_data.html.twig';
                $options['require_all_fields'] = false;
                break;
            case 'medical':
                // find medical data by UUID
                $pacientData = $pacientMedicalDataRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientMedicalDataFormType::class;
                $template = 'dashboard/pacient/_pacient_medical_data.html.twig';
                break;
            case 'clinical-exam':
                // find clinical exam data by UUID
                $pacientData = $pacientClinicalExamRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientClinicalExamFormType::class;
                $template = 'dashboard/pacient/_pacient_clinical_exam.html.twig';
                break;
            case 'discharge':
                // find discharge data by UUID
                $pacientData = $pacientDischargeRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientDischargeFormType::class;
                $template = 'dashboard/pacient/_pacient_discharge_data.html.twig';
                $options['require_discharge_reason'] = false;
                break;
            default:
                break;
        }

        if (null === $pacientData) {
            return new Response('');
        }

        $form = $this->createForm($formTypeClass, $pacientData, $options);

        return $this->render($template, [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/data/{uuid}/update/{type}", name="dashboard_ajax_pacient_update_data")
     */
    public function updatePacientData(Request $request, PacientGeneralDataRepository $pacientGeneralDataRepository, PacientMedicalDataRepository $pacientMedicalDataRepository, PacientClinicalExamRepository $pacientClinicalExamRepository, PacientDischargeRepository $pacientDischargeRepository, EntityManagerInterface $em, $uuid, $type): Response
    {
        $pacientData = null;
        $options = [];

        switch ($type) {
            case 'general':
                // find general data by UUID
                $pacientData = $pacientGeneralDataRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientGeneralDataFormType::class;
                $options['require_all_fields'] = false;
                break;
            case 'medical':
                // find medical data by UUID
                $pacientData = $pacientMedicalDataRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientMedicalDataFormType::class;
                break;
            case 'clinical-exam':
                // find medical data by UUID
                $pacientData = $pacientClinicalExamRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientClinicalExamFormType::class;
                break;
            case 'discharge':
                // find discharge data by UUID
                $pacientData = $pacientDischargeRepository->findOneBy(['uid' => $uuid]);
                $formTypeClass = PacientDischargeFormType::class;
                $options['require_discharge_reason'] = false;
                break;
            default:
                break;
        }

        if (null === $pacientData) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm($formTypeClass, $pacientData, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($pacientData);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/data/{uuid}/delete/{type}", name="dashboard_ajax_pacient_delete_data")
     */
    public function deletePacientData(PacientGeneralDataRepository $pacientGeneralDataRepository, PacientMedicalDataRepository $pacientMedicalDataRepository, PacientClinicalExamRepository $pacientClinicalExamRepository, PacientDischargeRepository $pacientDischargeRepository, EntityManagerInterface $em, $uuid, $type): Response
    {
        switch ($type) {
            case 'general':
                // find general data by UUID
                $pacientData = $pacientGeneralDataRepository->findOneBy(['uid' => $uuid]);
                break;
            case 'medical':
                // find medical data by UUID
                $pacientData = $pacientMedicalDataRepository->findOneBy(['uid' => $uuid]);
                break;
            case 'clinical-exam':
                // find clinical exam data by UUID
                $pacientData = $pacientClinicalExamRepository->findOneBy(['uid' => $uuid]);
                break;
            case 'discharge':
                // find discharge data by UUID
                $pacientData = $pacientDischargeRepository->findOneBy(['uid' => $uuid]);
                break;
            default:
                break;
        }

        // check if data exists
        if (null === $pacientData) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // soft delete
        $pacientData->setDeletedAt(new \DateTime());
        // write changes to DB
        $em->persist($pacientData);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Datele au fost sterse cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/file/{uuid}", name="dashboard_ajax_pacient_file_load")
     */
    public function loadPacientFile(PacientFileRepository $pacientFileRepository, $uuid): Response
    {
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacientFile) {
            return new Response('');
        }

        $form = $this->createForm(PacientFileFormType::class, $pacientFile);

        return $this->render('dashboard/pacient/_pacient_file.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/file/{uuid}/update", name="dashboard_ajax_pacient_file_update")
     */
    public function updatePacientFile(Request $request, PacientFileRepository $pacientFileRepository, EntityManagerInterface $em, $uuid): Response
    {
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacientFile) {
            return $this->json([
                'success' => false,
                'message' => 'Datele invalide'
            ]);
        }

        $form = $this->createForm(PacientFileFormType::class, $pacientFile);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($pacientFile);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/file/{uuid}/deadline", name="dashboard_ajax_pacient_file_deadline")
     */
    public function updatePacientFileDeadline(Request $request, PacientFileRepository $pacientFileRepository, EntityManagerInterface $em, $uuid): Response
    {
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $uuid]);
        // check if data exists
        if (null === $pacientFile) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // check request data
        $deadline = $request->get('uploadDeadline');
        if (null === $deadline) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // set deadline
        $pacientFile->setUploadDeadline(\DateTime::createFromFormat('Y-m-d', $deadline));
        // write changes to DB
        $em->persist($pacientFile);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Deadline-ul a fost setat cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/file/{uuid}/delete", name="dashboard_ajax_pacient_file_delete")
     */
    public function deletePacientFile(PacientFileRepository $pacientFileRepository, EntityManagerInterface $em, $uuid): Response
    {
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $uuid]);
        // check if data exists
        if (null === $pacientFile) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // soft delete
        $pacientFile->setDeletedAt(new \DateTime());
        // write changes to DB
        $em->persist($pacientFile);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Documentul a fost sters cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/file/{uuid}/view", name="dashboard_ajax_pacient_file_view")
     */
    public function viewPacientFile(PacientFileRepository $pacientFileRepository, EntityManagerInterface $em, $uuid): Response
    {
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $uuid]);
        // check if data exists
        if (null === $pacientFile) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // create pacient file view
        $pacientFileView = new PacientFileView();
        $pacientFileView->setFile($pacientFile);
        $pacientFileView->setUser($this->getUser());
        $pacientFileView->setCreatedAt(new \DateTime());
        // increment views on file
        $pacientFile->incrementViews();
        // write changes to DB
        $em->persist($pacientFileView);
        $em->persist($pacientFile);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => ''
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/file/{uuid}/responsible/{userUuid}/update", name="dashboard_ajax_pacient_file_responsible_update")
     */
    public function updatePacientFileUserResponsible(Request $request, PacientFileRepository $pacientFileRepository, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid, $userUuid): Response
    {
        // find pacient file by UUID
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacientFile) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // find user by UUID
        $user = $pacientRepository->findOneBy(['uid' => $userUuid]);
        if (null === $user) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // write changes to DB
        $pacientFile->setUserResponsible($user);
        $em->persist($pacientFile);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => ''
        ]);
    }

    /**
     * @Route("/dashboard/ajax/prospect/{uuid}/update", name="dashboard_ajax_prospect_update")
     */
    public function updateProspect(Request $request, ProspectRepository $prospectRepository, PacientVisitCalendarRepository $pacientVisitCalendarRepository, EntityManagerInterface $em, $uuid): Response
    {
        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm(ProspectFormType::class, $prospect);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if visit exists in calendar table
                $visit = $pacientVisitCalendarRepository->findOneBy([
                    'prospect' => $prospect
                ]);
                // check for viewing date
                $scheduledAtStart = $form['scheduledAt']->getData();
                if ($scheduledAtStart instanceof \DateTime) {
                    $scheduledAtEnd = clone $scheduledAtStart;
                    $nursingHome = $this->getUser()->getNursingHome();

                    if (null === $nursingHome) {
                        return $this->json([
                            'success' => false,
                            'message' => 'Date invalide'
                        ]);
                    }

                    if (null === $visit) {
                        $visit = new PacientVisitCalendar();
                        $visit->setUid(Uuid::v4());
                        $visit->setProspect($prospect);
                        $visit->setNursingHome($nursingHome);
                        $visit->setStatus(PacientVisitCalendar::VISIT_STATUS_PENDING);
                        $visit->setObservations('Programare vizionare');
                        $visit->setType(PacientVisitCalendar::VISIT_TYPE_VIEWING);
                        $visit->setCreatedAt(new \DateTime());
                    }

                    $visit->setStartDate($scheduledAtStart);
                    $visit->setEndDate($scheduledAtEnd->modify('+2 hours'));

                    // save changes to db
                    $em->persist($visit);
                    $em->flush();
                } else {
                    if (null !== $visit) {
                        // remove visit from db
                        $em->remove($visit);
                        $em->flush();
                    }
                }

                // save changes to db
                $em->persist($prospect);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/prospect/{uuid}/offer/template", name="dashboard_ajax_prospect_offer_template")
     */
    public function loadProspectOfferTemplate(ProspectRepository $prospectRepository, $uuid): Response
    {
        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->json([
                'success' => false,
                'content' => ''
            ]);
        }

        return $this->json([
            'success' => true,
            'content' => $this->renderView('dashboard/pacient/_prospect_offer_template.html.twig', [
                'prospect' => $prospect
            ])
        ]);
    }

    /**
     * @Route("/dashboard/ajax/prospects/sources", name="dashboard_ajax_prospects_sources")
     */
    public function getProspectsSources(ProspectRepository $prospectRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = [['Lead', 'Count']];

        $sources = $prospectRepository->findProspectsSources($user);

        foreach ($sources as $source) {
            $data[] = [$source['source'], $source['count']];
        }

        return $this->json($data);
    }

    /**
     * @Route("/dashboard/ajax/summary/pacient/{uuid}", name="dashboard_ajax_summary_pacient_load")
     */
    public function loadSummaryPacient(SummaryPacientRepository $summaryPacientRepository, $uuid): Response
    {
        $summaryPacient = $summaryPacientRepository->findOneBy(['uid' => $uuid]);
        if (null === $summaryPacient) {
            return new Response('');
        }

        $form = $this->createForm(SummaryPacientFormType::class, $summaryPacient);

        return $this->render('dashboard/summary/_summary_pacient.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/summary/pacient/{uuid}/update", name="dashboard_ajax_summary_pacient_update")
     */
    public function updateSummaryPacient(Request $request, SummaryPacientRepository $summaryPacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        $summaryPacient = $summaryPacientRepository->findOneBy(['uid' => $uuid]);
        if (null === $summaryPacient) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm(SummaryPacientFormType::class, $summaryPacient);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($summaryPacient);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/summary/pacient/{uuid}/delete", name="dashboard_ajax_summary_pacient_delete")
     */
    public function deleteSummaryPacient(SummaryPacientRepository $summaryPacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        $summaryPacient = $summaryPacientRepository->findOneBy(['uid' => $uuid]);
        if (null === $summaryPacient) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // delete entry from the DB
        $em->remove($summaryPacient);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Borderoul a fost sters cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/summary/{uuid}/settings", name="dashboard_ajax_summary_settings")
     */
    public function getSummarySettings(Request $request, SummaryRepository $summaryRepository, NursingHomeRepository $nursingHomeRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, $uuid): Response
    {
        // find summary by UUID
        $summary = $summaryRepository->findOneBy(['uid' => $uuid]);
        if (null === $summary) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $params = $request->request->all();
        if (empty($params['nursing-home'])) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['nursing-home']]);
        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $floors = $nursingHomeRoomRepository->findFloorsByNursingHome($nursingHome);
        $shifts = [];

        $today = new \DateTime();
        $tomorrow = new \DateTime('tomorrow');
        $dayToday = $today->format('j');
        $monthToday = $today->format('F');
        $yearToday = $today->format('Y');
        $dayTomorrow = $tomorrow->format('j');
        $monthTomorrow = $tomorrow->format('F');
        $yearTomorrow = $tomorrow->format('Y');

        if (SummaryType::SUMMARY_TYPE_COOK != $summary->getType()->getId()) {
            $firstIntervalStart = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '7:00'));
            $firstIntervalEnd = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '13:59'));
            $secondIntervalStart = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '14:00'));
            $secondIntervalEnd = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '18:59'));
            $thirdIntervalStart = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '19:00'));
            $thirdIntervalEnd = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '22:59'));
            $fourthIntervalStart = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayToday, $monthToday, $yearToday, '23:00'));
            $fourthIntervalEnd = \DateTime::createFromFormat('j F Y H:i', sprintf('%s %s %s %s', $dayTomorrow, $monthTomorrow, $yearTomorrow, '6:59'));
            $firstInterval = sprintf('%s %s - %s', $dayToday, $monthToday, 'dimineata (7:00 - 13:59)');
            $secondInterval = sprintf('%s %s - %s', $dayToday, $monthToday, 'pranz (14:00 - 18:59)');
            $thirdInterval = sprintf('%s %s - %s', $dayToday, $monthToday, 'seara (19:00 - 22:59)');
            $fourthInterval = sprintf('%s %s - %s', $dayToday, $monthToday, 'noaptea (23:00 - 6:59)');

            $shifts = [
                sprintf('%s/%s/%s', $firstIntervalStart->format('U'), $firstIntervalEnd->format('U'), $firstInterval) => $firstInterval,
                sprintf('%s/%s/%s', $secondIntervalStart->format('U'), $secondIntervalEnd->format('U'), $secondInterval) => $secondInterval,
                sprintf('%s/%s/%s', $thirdIntervalStart->format('U'), $thirdIntervalEnd->format('U'), $thirdInterval) => $thirdInterval,
                sprintf('%s/%s/%s', $fourthIntervalStart->format('U'), $fourthIntervalEnd->format('U'), $fourthInterval) => $fourthInterval
            ];
        }

        return $this->json([
            'success' => true,
            'message' => '',
            'floors' => $floors,
            'shifts' => $shifts
        ]);
    }

    /**
     * @Route("/dashboard/ajax/summary/{uuid}/{type}", name="dashboard_ajax_summary_by_type")
     */
    public function updateSummaryByType(Request $request, SummaryRepository $summaryRepository, SummaryPacientRepository $summaryPacientRepository, NursingHomeRepository $nursingHomeRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, PacientRepository $pacientRepository, PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, PacientMonitoringMedicalRepository $pacientMonitoringMedicalRepository, PacientSampleRepository $pacientSampleRepository, PacientPhysicalDataRepository $pacientPhysicalDataRepository, PacientOrderlyDataRepository $pacientOrderlyDataRepository, PacientCookFoodRepository $pacientCookFoodRepository, EntityManagerInterface $em, $uuid, $type): Response
    {
        // find summary by UUID
        $summary = $summaryRepository->findOneBy(['uid' => $uuid]);
        if (null === $summary) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $params = $request->request->all();
        if (empty($params['floors']) || empty($params['nursing-home-location'])) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['nursing-home-location']]);
        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $existingExtraData = $summary->getExtraData();
        $diff = strcmp(json_encode($existingExtraData), json_encode($params));

        $summary->setExtraData($params);
        $em->persist($summary);
        $em->flush();

        $shifts = [];
        if (isset($params['shifts']) && !empty($params['shifts'])) {
            foreach ($params['shifts'] as $shiftInterval) {
                $shiftData = explode('/', $shiftInterval);
                $shifts[] = [
                    'start' => \DateTime::createFromFormat('U', $shiftData[0]),
                    'end' => \DateTime::createFromFormat('U', $shiftData[1]),
                    'interval' => $shiftData[2]
                ];
            }
        } else {
            $start = $summary->getSummaryDate();
            $end = \DateTime::createFromFormat('Y-m-d', $start->format('Y-m-d'));
            $end->modify('+7 days');
            $shifts[] = [
                'start' => $start,
                'end' => $end,
                'interval' => sprintf('%s - %s', $start->format('d M'), $end->format('d M'))
            ];
        }

        if ($diff !== 0) {
            // remove all existing pacients from the summary
            $summaryPacients = $summary->getSummaryPacients();
            foreach ($summaryPacients as $summaryPacient) {
                $em->remove($summaryPacient);
                $em->flush();
            }
        }

        $data = [];
        $roomsList = $nursingHomeRoomRepository->findRoomsByNursingHomeAndFloors($nursingHome, $params['floors']);
        foreach ($roomsList as $room) {
            $pacients = $pacientRepository->findPacientsByRoom($room['id']);
            $pacientsData = [];

            foreach ($pacients as $pacient) {
                $user = $pacientRepository->find($pacient['id']);
                if (null !== $user && $diff !== 0) {
                    // add pacient to summary
                    $summaryPacient = new SummaryPacient();
                    $summaryPacient->setUid(Uuid::v4());
                    $summaryPacient->setSummary($summary);
                    $summaryPacient->setPacient($user);
                    $summaryPacient->setAddedBy($this->getUser());
                    $summaryPacient->setUrgent(false);
                    $summaryPacient->setStatus(SummaryPacient::STATUS_IN_PROGRESS);

                    // write changes to DB
                    $em->persist($summaryPacient);
                    $em->flush();
                }

                $summaryPacient = $summaryPacientRepository->findOneBy(['summary' => $summary, 'pacient' => $user]);
                if (null !== $summaryPacient) {
                    $pacientsData[$pacient['id']] = [
                        'name' => $pacient['name'],
                        'uid' => $pacient['uid'],
                        'summaryPacientUid' => $summaryPacient->getUid(),
                        'completed' => $summaryPacient->isCompleted()
                    ];
                }

                switch ($type) {
                    case 'assistance-medical':
                        foreach ($shifts as $shift) {
                            $pacientMedication = $pacientMedicationDetailsRepository->findTreatmentPlansByPacientAndInterval($pacient['id'], $shift['start'], $shift['end']);
                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['medication'] = $pacientMedication;

                            $pacientMonitoringMedical = $pacientMonitoringMedicalRepository->findMonitoringMedicalByPacientAndInterval($pacient['id'], $shift['start'], $shift['end']);
                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['monitoring'] = $pacientMonitoringMedical;

                            $pacientSamples = $pacientSampleRepository->findSamplesByPacientAndInterval($pacient['id'], $shift['start'], $shift['end']);
                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['samples'] = $pacientSamples;

                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['start'] = $shift['start']->format('Y-m-d H:i');
                        }
                        break;
                    case 'physical-therapy':
                        foreach ($shifts as $shift) {
                            $pacientPhysicalData = $pacientPhysicalDataRepository->findPhysicalDataByPacientAndInterval($pacient['id'], $shift['start'], $shift['end']);
                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['physical'] = $pacientPhysicalData;

                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['start'] = $shift['start']->format('Y-m-d H:i');
                        }
                        break;
                    case 'orderly':
                        foreach ($shifts as $shift) {
                            $pacientOrderlyData = $pacientOrderlyDataRepository->findOrderlyDataByPacientAndInterval($pacient['id'], $shift['start'], $shift['end']);
                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['orderly'] = $pacientOrderlyData;

                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['start'] = $shift['start']->format('Y-m-d H:i');
                        }
                    case 'cook':
                        foreach ($shifts as $shift) {
                            $pacientCookData = $pacientCookFoodRepository->findCookDataByPacientAndInterval($pacient['id'], $shift['start'], $shift['end']);
                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['cook'] = $pacientCookData;

                            $pacientsData[$pacient['id']]['shifts'][$shift['interval']]['start'] = $shift['start']->format('Y-m-d');
                        }

                        break;
                    default:
                        break;
                }
            }

            $data[] = [
                'room' => $room['roomNumber'],
                'pacients' => $pacientsData
            ];
        }

        return $this->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
    }

    /**
     * @Route("/dashboard/ajax/summary/{uuid}/pacient/{userUuid}/add", name="dashboard_ajax_summary_pacient_add")
     */
    public function addPacientToSummary(SummaryRepository $summaryRepository, SummaryPacientRepository $summaryPacientRepository, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid, $userUuid): Response
    {
        // find summary by UUID
        $summary = $summaryRepository->findOneBy(['uid' => $uuid]);
        if (null === $summary) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $userUuid]);
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // check if user is already added to summary
        $summaryHasPacient = $summaryPacientRepository->findOneBy([
            'summary' => $summary,
            'pacient' => $pacient
        ]);
        if (null == !$summaryHasPacient) {
            return $this->json([
                'success' => false,
                'message' => 'Acest pacient exista deja in borderou'
            ]);
        }

        $summaryPacient = new SummaryPacient();
        $summaryPacient->setUid(Uuid::v4());
        $summaryPacient->setSummary($summary);
        $summaryPacient->setPacient($pacient);
        $summaryPacient->setAddedBy($this->getUser());
        $summaryPacient->setUrgent(false);
        $summaryPacient->setStatus(SummaryPacient::STATUS_IN_PROGRESS);

        // write changes to DB
        $em->persist($summaryPacient);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => ''
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/sample/{uuid}", name="dashboard_ajax_pacient_sample_load")
     */
    public function loadPacientSample(PacientSampleRepository $pacientSampleRepository, $uuid): Response
    {
        $pacientSample = $pacientSampleRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacientSample) {
            return new Response('');
        }

        $form = $this->createForm(PacientSampleFormType::class, $pacientSample);

        return $this->render('dashboard/summary/_pacient_sample.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/sample/{uuid}/update", name="dashboard_ajax_pacient_sample_update")
     */
    public function updatePacientSample(Request $request, PacientSampleRepository $pacientSampleRepository, EntityManagerInterface $em, $uuid): Response
    {
        $pacientSample = $pacientSampleRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacientSample) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $form = $this->createForm(PacientSampleFormType::class, $pacientSample);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($pacientSample);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/form/cook/food/{id}", name="dashboard_ajax_pacient_cook_food_load")
     */
    public function loadPacientCookFood(PacientCookFoodRepository $pacientCookFoodRepository, $id = 'new'): Response
    {
        if ('new' !== $id) {
            $pacientCookFood = $pacientCookFoodRepository->find($id);
            if (null === $pacientCookFood) {
                return new Response('');
            }
        } else {
            $pacientCookFood = new PacientCookFood();
        }

        $form = $this->createForm(PacientCookFoodFormType::class, $pacientCookFood);

        return $this->render('dashboard/summary/_pacient_cook_food.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/cook/food/{id}", name="dashboard_ajax_pacient_cook_food_update")
     */
    public function updatePacientCookFood(Request $request, PacientRepository $pacientRepository, PacientCookFoodRepository $pacientCookFoodRepository, EntityManagerInterface $em, $uuid, $id = 'new'): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        if ('new' !== $id) {
            $pacientCookFood = $pacientCookFoodRepository->find($id);
            if (null === $pacientCookFood) {
                return $this->json([
                    'success' => false,
                    'message' => 'Date invalide'
                ]);
            }
        } else {
            $pacientCookFood = new PacientCookFood();
            $pacientCookFood->setPacient($pacient);
        }

        $form = $this->createForm(PacientCookFoodFormType::class, $pacientCookFood);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                // save changes to db
                $em->persist($pacientCookFood);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!',
                    'data' => [
                        'id' => $pacientCookFood->getId(),
                        'foodOption' => $pacientCookFood->getFoodOption(),
                        'observations' => $pacientCookFood->getObservations(),
                        'date' => $pacientCookFood->getDate()->format('d M')
                    ]
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/document/{slug}/generate", name="dashboard_ajax_document_generate")
     */
    public function generateDocument(Request $request, EntityManagerInterface $em, $slug): Response
    {
        $params = $request->request->all();
        $params['slug'] = $slug;

        try {
            $objects = $this->getFilteredObjects($params, $em);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        $document = $em->getRepository(Document::class)->findOneBy(['slug' => $slug]);

        if (null === $document) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $extraData = [];
        $documentData = $em->getRepository(DocumentData::class)->findOneBy(['document' => $document, 'pacient' => $objects['beneficiar']]);

        if (null !== $documentData) {
            $extraData = $documentData->getExtraData();
        }

        $template = $document->getTemplate();
        $placeholders = $document->getPlaceholders();
        $accessor = PropertyAccess::createPropertyAccessor();
        $patterns = [];

        foreach ($objects as $type => $object) {
            foreach ($placeholders as $className => $classes) {
                foreach ($classes as $classKey => $data) {
                    if ($this->getClassName($object) == $className && $type == $placeholders[$className][$classKey]['type']) {
                        $placeholders[$className][$classKey]['id'] = $object->getId();
                        foreach ($data['variables'] as $key => $variable) {
                            try {
                                $value = $accessor->getValue($object, $variable['field']);
                            } catch (NoSuchPropertyException $e) {
                                continue;
                            }

                            if ($variable['type'] == 'text' || $variable['type'] == 'textarea') {
                                $placeholders[$className][$classKey]['variables'][$key]['value'] = $value;
                                if (!empty($value)) {
                                    $patterns[$variable['pattern']] = $value;
                                }
                            } elseif ($variable['type'] == 'entity') {
                                if (!empty($value)) {
                                    $placeholders[$className][$classKey]['variables'][$key]['value'] = $value->getId();
                                    try {
                                        $text = $accessor->getValue($object, sprintf("%s.%s", $variable['field'], $variable['display']));
                                    } catch (NoSuchPropertyException $e) {
                                        continue;
                                    }

                                    $placeholders[$className][$classKey]['variables'][$key]['text'] = $text;
                                    $patterns[$variable['pattern']] = $text;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($extraData)) {
            foreach ($placeholders as $className => $classes) {
                foreach ($classes as $classKey => $data) {
                    foreach ($extraData as $extraClassName => $extraVariables) {
                        foreach ($data['variables'] as $key => $variable) {
                            if ($variable['type'] == 'text' || $variable['type'] == 'textarea') {
                                if (isset($extraVariables[$variable['field']])) {
                                    $value = $extraVariables[$variable['field']];
                                    $placeholders[$className][$classKey]['variables'][$key]['value'] = $extraVariables[$variable['field']];
                                    if (!empty($value)) {
                                        $patterns[$variable['pattern']] = $value;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $template = str_replace(array_keys($patterns), array_values($patterns), $template);

        return $this->json([
            'success' => true,
            'message' => '',
            'data' => $placeholders,
            'content' => $template,
            'pacientUuid' => $params['pacient']
        ]);
    }

    /**
     * @Route("/dashboard/ajax/global-update", name="dashboard_ajax_global_update")
     */
    public function globalUpdate(Request $request, EntityManagerInterface $em): Response
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $data = $request->get('fields');
        $pacientUuid = $request->get('pacient');
        $documentSlug = $request->get('document');
        $extraData = [];

        foreach ($data as $className => $variables) {
            $fullClassName = sprintf("App\Entity\%s", $className);
            if (!class_exists($fullClassName)) {
                foreach ($variables as $id => $fields) {
                    foreach ($fields as $name => $value) {
                        $extraData[$className][$name] = $value;
                    }
                }

                continue;
            }
            foreach ($variables as $id => $fields) {
                $entity = $em->getRepository($fullClassName)->find($id);
                if (null === $entity) {
                    continue;
                }
                foreach ($fields as $name => $value) {
                    if (in_array($name, ['county', 'city'])) {
                        $entityClassName = sprintf("App\Entity\%s", ucfirst($name));
                        $value = $em->getRepository($entityClassName)->find($value);
                        if (null === $value) {
                            continue;
                        }
                    }
                    try {
                        $accessor->setValue($entity, $name, $value);
                    } catch (NoSuchPropertyException $e) {
                        $extraData[$className][$name] = $value;
                        continue;
                    }
                }

                $em->persist($entity);
                $em->flush();
            }
        }

        $document = $em->getRepository(Document::class)->findOneBy(['slug' => $documentSlug]);
        $pacient = $em->getRepository(Pacient::class)->findOneBy(['uid' => $pacientUuid]);

        if (null !== $document && null !== $pacient) {
            $documentData = $em->getRepository(DocumentData::class)->findOneBy(['document' => $document, 'pacient' => $pacient]);
            if (null === $documentData) {
                $documentData = new DocumentData();
                $documentData->setPacient($pacient);
                $documentData->setDocument($document);
            }

            $documentData->setExtraData($extraData);

            $em->persist($documentData);
            $em->flush();
        }

        return $this->json([
            'success' => true,
            'message' => 'Datele au fost actualizate cu succes'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pdf/generate", name="dashboard_ajax_pdf_generate")
     */
    public function generatePDF(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $params = $request->request->all();
        $extension = 'pdf';
        if (empty($params['html']) || empty($params['name']) || empty($params['slug'])) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $document = $em->getRepository(Document::class)->findOneBy(['slug' => $params['slug']]);
        $nursingHome = $this->getUser()->getNursingHome();

        if (null !== $document && null !== $nursingHome) {
            $documentNumber = $em->getRepository(DocumentNumber::class)->findOneBy(['document' => $document, 'nursingHome' => $nursingHome]);
            if (null !== $documentNumber) {
                $documentNumber->incrementDocumentNumber();

                $em->persist($documentNumber);
                $em->flush();
            }
        }

        $session->set($params['slug'], true);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $html = $this->renderView('dashboard/shared/pdf.html.twig', [
            'content' => $params['html'],
            'title' => $params['name']
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream(sprintf('%s.%s', $params['name'], $extension), ["Attachment" => true]);
    }

    /**
     * @Route("/dashboard/ajax/user/{uuid}/update-password", name="dashboard_ajax_user_update_password")
     */
    public function updatePassword(Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, $uuid): Response
    {
        $user = $userRepository->findOneBy([
            'uid' => $uuid
        ]);
        if (null === $user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // hash new password
                $hashedPassword = $passwordHasher->hashPassword(
                    $user, $form['new_password']->getData()
                );
                $user->setPassword($hashedPassword);
                // save changes to db
                $em->persist($user);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Parola a fost actualizata cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/user/{uuid}/update-personal-data", name="dashboard_ajax_user_update_personal_data")
     */
    public function userUpdatePersonalData(Request $request, UserRepository $userRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $user = $userRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $form = $this->createForm(UserPersonalDataFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($user);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/update-personal-data", name="dashboard_ajax_pacient_update_personal_data")
     */
    public function pacientUpdatePersonalData(Request $request, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if pacient exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $form = $this->createForm(PacientPersonalDataFormType::class, $pacient);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // save changes to db
                $em->persist($pacient);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost actualizate cu success!'
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/add-file", name="dashboard_ajax_pacient_add_file")
     */
    public function addPacientFile(Request $request, PacientRepository $pacientRepository, PacientFileGroupRepository $pacientFileGroupRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $fileName = $request->get('fileName');
        $group = $request->get('group');
        if (null === $fileName || null === $group) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        $pacientFileGroup = $pacientFileGroupRepository->find($group);
        if (null === $pacientFileGroup) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        // create new pacient file
        $pacientFile = new PacientFile();
        $pacientFile->setUid(Uuid::v4());
        $pacientFile->setPacient($pacient);
        $pacientFile->setUserResponsible($this->getUser());
        $pacientFile->setFileGroup($pacientFileGroup);
        $pacientFile->setFileName($fileName);
        $pacientFile->setStatus('In asteptare');
        $pacientFile->setCreatedAt(new \DateTime());
        $pacientFile->setViews(0);
        // write to DB
        $em->persist($pacientFile);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Fisierul a fost adaugat cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/user/{uuid}/upload-profile-photo", name="dashboard_ajax_user_upload_profile_photo")
     */
    public function uploadProfilePhoto(Request $request, UserRepository $userRepository, EntityManagerInterface $em, FileUploader $fileUploader, $uuid): Response
    {
        // find user by UUID
        $user = $userRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $photo = $request->files->get('photo');
        if ($photo instanceof UploadedFile) {
            // Upload photo
            try {
                $newFilename = $fileUploader->upload($photo, 'uploads');
            } catch (FileException $e) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            // set new photo on user entity
            $user->setPhoto($newFilename);
            // write changes to DB
            $em->persist($user);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Fotografia de profil a fost actualizata cu success!'
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Te rugam sa incarci o fotografie valida'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/digital-record/{recordUuid}/upload", name="dashboard_ajax_pacient_upload_digital_record")
     */
    public function uploadDigitalRecord(Request $request, PacientRepository $pacientRepository, PacientFileRepository $pacientFileRepository, EntityManagerInterface $em, FileUploader $fileUploader, $uuid, $recordUuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }
        // find digital record by UUID
        $pacientFile = $pacientFileRepository->findOneBy(['uid' => $recordUuid]);
        if (null === $pacientFile) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $file = $request->files->get('file');
        if ($file instanceof UploadedFile) {
            $fileExtension = $file->guessExtension();
            // Upload file
            try {
                $newFilename = $fileUploader->upload($file, sprintf('%s/%s', 'records', $uuid));
            } catch (FileException $e) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            // add file data
            $pacientFile->setFilePath($newFilename);
            $pacientFile->setFileExtension($fileExtension);
            $pacientFile->setUploadedAt(new \DateTime());
            $pacientFile->setStatus('Incarcat');
            $pacientFile->setAddedBy($this->getUser());
            // write changes to DB
            $em->persist($pacientFile);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Documentul a fost incarcat cu success!'
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Te rugam sa incarci un document valid'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/room/assign", name="dashboard_ajax_pacient_assign_room")
     */
    public function assignPacientRoom(Request $request, PacientRepository $pacientRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $params = $request->request->all();
        if (empty($params['location']) || empty($params['roomNumber']) || empty($params['createdAt'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        $nursingHomeRoom = $nursingHomeRoomRepository->find($params['roomNumber']);
        if (null === $nursingHomeRoom) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        // create new pacient room
        $pacientRoom = new PacientRoom();
        $pacientRoom->setPacient($pacient);
        $pacientRoom->setNursingHomeRoom($nursingHomeRoom);
        $pacientRoom->setCreatedAt(\DateTime::createFromFormat('Y-m-d', $params['createdAt']));
        $pacientRoom->setObservations($params['observations']);
        $pacientRoom->setUid(Uuid::v4());
        // assign room to pacient
        $pacient->setNursingHomeRoom($nursingHomeRoom);

        // write to DB
        $em->persist($pacientRoom);
        $em->persist($pacient);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Pacientul a fost asignat cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/monitoring/medical/add", name="dashboard_ajax_pacient_monitoring_medical_add")
     */
    public function addPacientMonitoringMedical(Request $request, PacientRepository $pacientRepository, EntityManagerInterface $em, FormFactoryInterface $formFactory, $uuid): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $pacientMonitoringMedical = new PacientMonitoringMedical();
        $form = $formFactory->createNamed('', PacientMonitoringMedicalFormType::class, $pacientMonitoringMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $pacientMonitoringMedical->setPacient($pacient);
                $pacientMonitoringMedical->setAddedBy($this->getUser());
                $pacientMonitoringMedical->setDate(\DateTime::createFromFormat('Y-m-d H:i', $request->get('date')));
                $pacientMonitoringMedical->setCreatedAt(new \DateTime());
                // save changes to db
                $em->persist($pacientMonitoringMedical);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost adaugate cu success!',
                    'data' => [
                        'bloodPressure' => sprintf('%s/%s', empty($pacientMonitoringMedical->getSystolicBloodPressure()) ? '-' : $pacientMonitoringMedical->getSystolicBloodPressure(), empty($pacientMonitoringMedical->getDiastolicBloodPressure()) ? '-' : $pacientMonitoringMedical->getDiastolicBloodPressure()),
                        'heartRate' => empty($pacientMonitoringMedical->getHeartRate()) ? '-' : $pacientMonitoringMedical->getHeartRate(),
                        'temperature' => empty($pacientMonitoringMedical->getTemperature()) ? '-' : $pacientMonitoringMedical->getTemperature(),
                        'saturation' => empty($pacientMonitoringMedical->getSaturation()) ? '-' : $pacientMonitoringMedical->getSaturation(),
                        'glucose' => empty($pacientMonitoringMedical->getGlucose()) ? '-' : $pacientMonitoringMedical->getGlucose(),
                        'infusion' => empty($pacientMonitoringMedical->getInfusion()) ? '-' : $pacientMonitoringMedical->getInfusion()
                    ]
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/sample-request", name="dashboard_ajax_pacient_sample_request")
     */
    public function requestPacientSample(Request $request, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $params = $request->request->all();
        if (empty($params['date']) || empty($params['description'])) {
            return $this->json([
                'success' => false,
                'message' => 'Te rugam sa introduci date valide'
            ]);
        }

        $pacientSample = new PacientSample();
        $pacientSample->setUid(Uuid::v4());
        $pacientSample->setPacient($pacient);
        $pacientSample->setAddedBy($this->getUser());
        $pacientSample->setSampleDate(\DateTime::createFromFormat('Y-m-d H:i', $params['date']));
        $pacientSample->setDescription($params['description']);
        $pacientSample->setStatus(PacientSample::STATUS_REQUESTED);
        $pacientSample->setContactedFamily(false);
        $pacientSample->setCollected(false);
        $pacientSample->setCreatedAt(new \DateTime());

        $em->persist($pacientSample);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Cererea de colectare a fost adaugata cu succes'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/chart/{type}", name="dashboard_ajax_pacient_chart_data")
     */
    public function getPacientChartData(PacientRepository $pacientRepository, PacientMonitoringMedicalRepository $pacientMonitoringMedicalRepository, $uuid, $type): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'data' => [],
                ''
            ]);
        }

        $startDate = new \DateTime('1 month ago');
        $pacientMonitoringMedicalData = $pacientMonitoringMedicalRepository->findMonitoringMedicalDataByPacientForChart($pacient, $type, $startDate);
        $data = [];

        switch ($type) {
            case 'blood-pressure':
                $labels = [];
                $tempDataSystolic = [];
                $tempDataDiastolic = [];
                foreach ($pacientMonitoringMedicalData as $medicalData) {
                    $labels[] = $medicalData['date'];
                    $tempDataSystolic[] = $medicalData['systolicBloodPressure'];
                    $tempDataDiastolic[] = $medicalData['diastolicBloodPressure'];
                }

                $data = [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Tensiunea sistolica',
                            'data' => $tempDataSystolic,
                            'borderColor' => 'red'
                        ],
                        [
                            'label' => 'Tensiunea diastolica',
                            'data' => $tempDataDiastolic,
                            'borderColor' => 'blue'
                        ]
                    ]
                ];

                break;
            case 'saturation':
                $labels = [];
                $tempData = [];
                $backgroundColor = [];
                foreach ($pacientMonitoringMedicalData as $medicalData) {
                    $labels[] = $medicalData['date'];
                    $tempData[] = $medicalData['saturation'];
                    if ((int)$medicalData['saturation'] < 93) {
                        $backgroundColor[] = 'red';
                    } elseif ((int)$medicalData['saturation'] < 96) {
                        $backgroundColor[] = 'yellow';
                    } else {
                        $backgroundColor[] = 'green';
                    }
                }

                $data = [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Saturatie',
                            'data' => $tempData,
                            'backgroundColor' => $backgroundColor
                        ]
                    ]
                ];

                break;
            case 'heart-rate':
                $labels = [];
                $tempData = [];
                $backgroundColor = [];
                foreach ($pacientMonitoringMedicalData as $medicalData) {
                    $labels[] = $medicalData['date'];
                    $tempData[] = $medicalData['heartRate'];
                    if ((int)$medicalData['heartRate'] < 50 || (int)$medicalData['heartRate'] > 100) {
                        $backgroundColor[] = 'red';
                    } elseif (((int)$medicalData['heartRate'] >= 90 && (int)$medicalData['heartRate'] <= 100) || ((int)$medicalData['heartRate'] >= 50 && (int)$medicalData['heartRate'] < 60)) {
                        $backgroundColor[] = 'yellow';
                    } else {
                        $backgroundColor[] = 'green';
                    }
                }

                $data = [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Puls',
                            'data' => $tempData,
                            'backgroundColor' => $backgroundColor
                        ]
                    ]
                ];
                break;
            case 'glucose':
                $labels = [];
                $tempData = [];
                $backgroundColor = [];
                foreach ($pacientMonitoringMedicalData as $medicalData) {
                    $labels[] = $medicalData['date'];
                    $tempData[] = $medicalData['glucose'];
                    if ((int)$medicalData['glucose'] > 180) {
                        $backgroundColor[] = 'red';
                    } elseif ((int)$medicalData['glucose'] > 120) {
                        $backgroundColor[] = 'yellow';
                    } else {
                        $backgroundColor[] = 'green';
                    }
                }

                $data = [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Glicemie',
                            'data' => $tempData,
                            'backgroundColor' => $backgroundColor
                        ]
                    ]
                ];
                break;
            default:
                break;
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/physical/template", name="dashboard_ajax_pacient_physical_monitoring_load")
     */
    public function loadPacientPhysicalMonitoring(): Response
    {
        $pacientPhysicalData = new PacientPhysicalData();
        $form = $this->createForm(PacientMonitoringPhysicalFormType::class, $pacientPhysicalData);

        return $this->render('dashboard/pacient/_pacient_physical_therapy_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/monitoring/physical/add", name="dashboard_ajax_pacient_monitoring_physical_add")
     */
    public function addPacientMonitoringPhysical(Request $request, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);

        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0);

        $summaryCreatedAt = \DateTime::createFromFormat('d.m.Y', $request->get('summary_created_at'));
        $summaryCreatedAt->setTime(0, 0);

        // check if user exists
        if (null === $pacient || $summaryCreatedAt != $currentDate) {
            return $this->json([
                'success' => false,
                'message' => $summaryCreatedAt != $currentDate ? 'Nu poți introduce date pentru un borderou din trecut.' : 'Utilizator invalid'
            ]);
        }

        $pacientPhysicalData = new PacientPhysicalData();
        $form = $this->createForm(PacientMonitoringPhysicalFormType::class, $pacientPhysicalData);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $date = $request->get('date');
                $pacientPhysicalData->setUid(Uuid::v4());
                $pacientPhysicalData->setPacient($pacient);
                $pacientPhysicalData->setAddedBy($this->getUser());
                $pacientPhysicalData->setDate(\DateTime::createFromFormat('Y-m-d H:i', $date));
                $pacientPhysicalData->setCreatedAt(new \DateTime());
                // save changes to db
                $em->persist($pacientPhysicalData);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost adaugate cu success!',
                    'uuid' => $pacientPhysicalData->getUid(),
                    'date' => $date,
                    'data' => [
                        'bodyRepositioningImmobilizedPatients' => $pacientPhysicalData->isBodyRepositioningImmobilizedPatients(),
                        'correctingBodyPostureAndAlignment' => $pacientPhysicalData->isCorrectingBodyPostureAndAlignment(),
                        'groupExercises' => $pacientPhysicalData->isGroupExercises(),
                        'groupExercisesBodyBalanceAndCoordination' => $pacientPhysicalData->isGroupExercisesBodyBalanceAndCoordination(),
                        'groupExercisesJointMobility' => $pacientPhysicalData->isGroupExercisesJointMobility(),
                        'groupExercisesResistanceAndMuscleStrength' => $pacientPhysicalData->isGroupExercisesResistanceAndMuscleStrength(),
                        'increasingBodyCoordinationAndBalance' => $pacientPhysicalData->isIncreasingBodyCoordinationAndBalance(),
                        'increasingJointMobility' => $pacientPhysicalData->isIncreasingJointMobility(),
                        'increasingJointMobilityActive' => $pacientPhysicalData->isIncreasingJointMobilityActive(),
                        'increasingJointMobilityActiveVoluntary' => $pacientPhysicalData->isIncreasingJointMobilityActiveVoluntary(),
                        'increasingJointMobilityAutoPassive' => $pacientPhysicalData->isIncreasingJointMobilityAutoPassive(),
                        'increasingJointMobilityPassive' => $pacientPhysicalData->isIncreasingJointMobilityPassive(),
                        'increasingJointMobilityPassiveActive' => $pacientPhysicalData->isIncreasingJointMobilityPassiveActive(),
                        'increasingMuscleStrengthAndEndurance' => $pacientPhysicalData->isIncreasingMuscleStrengthAndEndurance(),
                        'massage' => $pacientPhysicalData->isMassage(),
                        'multifunctionalDevice' => $pacientPhysicalData->isMultifunctionalDevice(),
                        'rocherCage' => $pacientPhysicalData->isRocherCage(),
                        'scriptotherapy' => $pacientPhysicalData->isScriptotherapy(),
                        'stretching' => $pacientPhysicalData->isStretching(),
                        'tappingMassage' => $pacientPhysicalData->isTappingMassage(),
                        'therapeuticMassage' => $pacientPhysicalData->isTherapeuticMassage(),
                        'trellisExercises' => $pacientPhysicalData->isTrellisExercises(),
                        'walkingExercises' => $pacientPhysicalData->isWalkingExercises(),
                        'walkingExercisesBicycle' => $pacientPhysicalData->isWalkingExercisesBicycle(),
                        'walkingExercisesSteps' => $pacientPhysicalData->isWalkingExercisesSteps(),
                        'walkingExercisesSupport' => $pacientPhysicalData->isWalkingExercisesSupport(),
                        'walkingExercisesWalkingLane' => $pacientPhysicalData->isWalkingExercisesWalkingLane(),
                        'refusal' => $pacientPhysicalData->isRefusal(),
                        'medicalProblem' => $pacientPhysicalData->isMedicalProblem(),
                        'observations' => $pacientPhysicalData->getObservations()
                    ]
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/orderly/template", name="dashboard_ajax_pacient_orderly_monitoring_load")
     */
    public function loadPacientOrderlyMonitoring(): Response
    {
        $pacientOrderlyData = new PacientOrderlyData();
        $form = $this->createForm(PacientMonitoringOrderlyFormType::class, $pacientOrderlyData);

        return $this->render('dashboard/pacient/_pacient_orderly_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/ajax/pacient/{uuid}/monitoring/orderly/add", name="dashboard_ajax_pacient_monitoring_orderly_add")
     */
    public function addPacientMonitoringOrderly(Request $request, PacientRepository $pacientRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        $pacientOrderlyData = new PacientOrderlyData();
        $form = $this->createForm(PacientMonitoringOrderlyFormType::class, $pacientOrderlyData);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $pacientOrderlyData->setUid(Uuid::v4());
                $pacientOrderlyData->setPacient($pacient);
                $pacientOrderlyData->setAddedBy($this->getUser());
                $pacientOrderlyData->setCreatedAt(new \DateTime());
                // save changes to db
                $em->persist($pacientOrderlyData);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datele au fost adaugate cu success!',
                    'date' => $pacientOrderlyData->getDate()->format('d M'),
                    'data' => [
                        'hydrationFood' => $pacientOrderlyData->isHydrationFood(),
                        'diuresis' => $pacientOrderlyData->isDiuresis(),
                        'stool' => $pacientOrderlyData->isStool(),
                        'bathing' => $pacientOrderlyData->isBathing(),
                        'diapers' => $pacientOrderlyData->isDiapers(),
                        'observations' => $pacientOrderlyData->getObservations()
                    ]
                ]);
            } else {
                $messages = [];
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => implode('<br/>', $messages)
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'message' => 'A intervenit o eroare neprevazuta, te rugam sa incerci mai tarziu.'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nursing-home/{uuid}/rooms", name="dashboard_ajax_nursing_home_rooms")
     */
    public function getNursingHomeRooms(NursingHomeRepository $nursingHomeRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, $uuid): Response
    {
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $uuid]);
        if (null === $nursingHome) {
            return $this->json([]);
        }

        $nursingHomeRooms = $nursingHomeRoomRepository->findRoomsByNursingHome($nursingHome);

        return $this->json($nursingHomeRooms);
    }

    /**
     * @Route("/dashboard/ajax/user/{uuid}/remove-profile-photo", name="dashboard_ajax_user_remove_profile_photo")
     */
    public function removeProfilePhoto(UserRepository $userRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find user by UUID
        $user = $userRepository->findOneBy(['uid' => $uuid]);
        // check if user exists
        if (null === $user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilizator invalid'
            ]);
        }

        // remove photo from the server
        unlink(sprintf('%s/%s/%s', $this->getParameter('kernel.project_dir'), 'public/uploads', $user->getPhoto()));
        // remove photo from user entity
        $user->setPhoto(null);
        // write changes to DB
        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Fotografia a fost stearsa cu success!'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/users/search", name="dashboard_ajax_users_search")
     */
    public function searchUsers(Request $request, UserRepository $userRepository): Response
    {
        $q = $request->get('q');

        if (empty($q)) {
            return $this->json([]);
        }

        $role = $request->get('role');
        $status = $request->get('status');
        $nursingHome = $this->getUser()->getNursingHome();
        $result = $userRepository->searchUsers($nursingHome, $q, $role, $status);

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/pacients/search", name="dashboard_ajax_pacients_search")
     */
    public function searchPacients(Request $request, PacientRepository $pacientRepository): Response
    {
        $q = $request->get('q');

        if (empty($q)) {
            return $this->json([]);
        }

        $status = $request->get('status');
        $nursingHome = $this->getUser()->getNursingHome();
        $result = $pacientRepository->searchPacients($nursingHome, $q, $status);

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/visit/pacient/add", name="dashboard_ajax_visit_pacient_add")
     */
    public function addPacientVisit(Request $request, PacientRepository $pacientRepository, NursingHomeRepository $nursingHomeRepository, EntityManagerInterface $em): Response
    {
        $params = $request->request->all();
        if (empty($params['date']) || empty($params['hour']) || empty($params['slot']) || empty($params['locationUuid']) || empty($params['userUuid'])) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // find user by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $params['userUuid']]);
        // check if user exists
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }
        // find location by UUID
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['locationUuid']]);
        if (null === $nursingHome) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // create new pacient visit
        $pacientVisit = new PacientVisit();
        $pacientVisit->setPacient($pacient);
        $pacientVisit->setNursingHome($nursingHome);
        $pacientVisit->setVisitDate(\DateTime::createFromFormat('Y-m-d H:i', sprintf('%s %s:00', $params['date'], $params['hour'])));
        $pacientVisit->setSlot($params['slot']);
        $pacientVisit->setStatus('In asteptare');
        $pacientVisit->setUid(Uuid::v4());

        // write to DB
        $em->persist($pacientVisit);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Pacientul a fost asignat cu success'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nps/template", name="dashboard_ajax_nps_template")
     */
    public function loadNpsTemplate(Request $request, UserRepository $userRepository, PacientRepository $pacientRepository, EmailRepository $emailRepository): Response
    {
        $params = $request->query->all();
        if (empty($params['userUuid']) || empty($params['pacientUuid'])) {
            return $this->json([
                'success' => false,
                'content' => ''
            ]);
        }

        $relation = $userRepository->findOneBy(['uid' => $params['userUuid']]);
        if (null === $relation) {
            return $this->json([
                'success' => false,
                'content' => ''
            ]);
        }

        $pacient = $pacientRepository->findOneBy(['uid' => $params['pacientUuid']]);
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'content' => ''
            ]);
        }

        $recipientEmail = $relation->getEmail();
        $emails = $emailRepository->findEmailsByRecipient($recipientEmail);

        return $this->json([
            'success' => true,
            'recipient' => sprintf('%s <%s>', $relation->getName(), $recipientEmail),
            'emails' => $this->renderView('dashboard/report/_emails_template.html.twig', [
                'emails' => $emails
            ]),
            'content' => $this->renderView('dashboard/report/_nps_template.html.twig', [
                'relation' => $relation,
                'pacient' => $pacient
            ])
        ]);
    }

    /**
     * @Route("/dashboard/ajax/nps/add", name="dashboard_ajax_nps_add")
     */
    public function addNps(Request $request, UserRepository $userRepository, PacientRepository $pacientRepository, EntityManagerInterface $em, TwigMailer $twigMailer): Response
    {
        $params = $request->request->all();
        if (empty($params['subject']) || empty($params['message']) || empty($params['userUuid']) || empty($params['pacientUuid'])) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $relation = $userRepository->findOneBy(['uid' => $params['userUuid']]);
        if (null === $relation) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $pacient = $pacientRepository->findOneBy(['uid' => $params['pacientUuid']]);
        if (null === $pacient) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        // create new NPS
        $uuid = Uuid::v4();
        $nps = new Nps();
        $nps->setUid($uuid);
        $nps->setPacient($pacient);
        $nps->setUser($relation);
        $nps->setCreatedAt(new \DateTime());
        // write to DB
        $em->persist($nps);
        $em->flush();

        $sent = $twigMailer->sendNpsMessage($relation->getEmail(), $params['subject'], $params['message']);
        if (false === $sent) {
            return $this->json([
                'success' => false,
                'message' => 'Mesajul nu a fost trimis!'
            ]);
        }

        // create new email
        $email = new Email();
        $email->setSenderName($this->getParameter('from_sender'));
        $email->setSenderEmail($this->getParameter('from_email'));
        $email->setRecipientName($relation->getName());
        $email->setRecipientEmail($relation->getEmail());
        $email->setSubject($params['subject']);
        $email->setOpened(false);
        $email->setClicks(0);
        $email->setContent($this->renderView('dashboard/shared/email/nps.html.twig', [
            'message' => $params['message']
        ]));
        $email->setCreatedAt(new \DateTime());
        // write to DB
        $em->persist($email);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Mesajul a fost trimis cu succes'
        ]);
    }

    /**
     * @Route("/dashboard/ajax/utilities/nursing-homes", name="dashboard_ajax_utilities_nursing_homes")
     */
    public function utilitiesNursingHomes(NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->json($nursingHomes);
    }

    /**
     * @Route("/dashboard/ajax/utilities/nursing-home/{uuid}/pacients", name="dashboard_ajax_utilities_nursing_home_pacients")
     */
    public function utilitiesNursingHomePacients(NursingHomeRepository $nursingHomeRepository, PacientRepository $pacientRepository, $uuid): Response
    {
        /** @var NursingHome $nursingHome */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $uuid]);

        if (null === $nursingHome) {
            return $this->json([]);
        }

        $pacients = $pacientRepository->findPacients($nursingHome);

        return $this->json($pacients);
    }

    /**
     * @Route("/dashboard/ajax/utilities/pacient/{uuid}/relations", name="dashboard_ajax_utilities_pacient_relations")
     */
    public function utilitiesPacientRelations(PacientRepository $pacientRepository, UserRelationRepository $userRelationRepository, $uuid): Response
    {
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacient) {
            return $this->json([]);
        }

        $relations = $userRelationRepository->findRelationsByUser($pacient);

        return $this->json($relations);
    }

    /**
     * @Route("/dashboard/ajax/utilities/pacient/{uuid}/diagnoses", name="dashboard_ajax_utilities_pacient_diagnoses")
     */
    public function utilitiesPacientDiagnoses(PacientRepository $pacientRepository, PacientDiagnosisRepository $pacientDiagnosisRepository, $uuid): Response
    {
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        if (null === $pacient) {
            return $this->json([]);
        }

        $diagnoses = $pacientDiagnosisRepository->findDiagnosesByPacient($pacient);

        return $this->json($diagnoses);
    }

    /**
     * @Route("/dashboard/ajax/utilities/medication/{id}/data", name="dashboard_ajax_utilities_medication_data")
     */
    public function utilitiesMedicationData(PacientMedicationRepository $pacientMedicationRepository, $id): Response
    {
        $pacientMedication = $pacientMedicationRepository->find($id);
        if (null === $pacientMedication) {
            return $this->json([
                'success' => false,
                'message' => 'Date invalide'
            ]);
        }

        $data = [];
        $asNecessary = $pacientMedication->isAsNecessary();
        $data['asNecessary'] = $asNecessary;
        $pacientMedicationDetails = $pacientMedication->getPacientMedicationDetails();

        $i = 0;
        foreach ($pacientMedicationDetails as $pacientMedicationDetail) {
            $details = [];
            $pacientMedicationDetailDate = $pacientMedicationDetail->getDate();
            if ($i == 0) {
                $data['drug'] = $pacientMedicationDetail->getDrug();
                $data['dose'] = $pacientMedicationDetail->getDose();
                if (!$asNecessary) {
                    $data['startDate'] = $pacientMedicationDetailDate->format('Y-m-d');
                }
            }

            $observations = $pacientMedicationDetail->getObservations();
            if (!$asNecessary) {
                $data['endDate'] = $pacientMedicationDetailDate->format('Y-m-d');
                $hour = $pacientMedicationDetailDate->format('H:i');
                $data['details'][$hour] = $observations;
            } else {
                $data['observations'] = $observations;
            }

            $i++;
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @Route("/dashboard/ajax/counties", name="dashboard_ajax_counties")
     */
    public function counties(CountyRepository $countyRepository): Response
    {
        $counties = $countyRepository->findCounties();

        return $this->json($counties);
    }

    /**
     * @Route("/dashboard/ajax/cities/county/{id}", name="dashboard_ajax_cities_by_county")
     */
    public function citiesByCounty(CountyRepository $countyRepository, CityRepository $cityRepository, $id): Response
    {
        $county = $countyRepository->find($id);
        if (null === $county) {
            return $this->json([]);
        }

        $counties = $cityRepository->findCitiesByCounty($county);

        return $this->json($counties);
    }

    /**
     * @Route("/dashboard/ajax/pages", name="dashboard_ajax_pages")
     */
    public function pages(Request $request, PageRepository $repository, DatatableHelper $datatableHelper): Response
    {
        $params = $request->query->all();

        $data = $repository->getPages();
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/menus", name="dashboard_ajax_menus")
     */
    public function menus(Request $request, MenuRepository $repository, DatatableHelper $datatableHelper): Response
    {
        $params = $request->query->all();

        $data = $repository->getMenus();
        $totalRecords = count($data);

        $data = $this->parseDatatableData($data, $params, $datatableHelper);
        $totalDisplay = count($data);

        // pagination length
        if (isset($params['length'])) {
            $data = array_splice($data, $params['start'], $params['length']);
        }

        $result = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $data
        ];

        return $this->json($result);
    }

    /**
     * @Route("/dashboard/ajax/admin/menu-items/{uuid}", name="dashboard_ajax_menu_items")
     */
    public function getMenuItems(EntityManagerInterface $em, $uuid)
    {
        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            return new JsonResponse([
                'data' => []
            ]);
        }

        $menuItems = $em->getRepository(Menu::class)->getMenuItemsByMenu(
            $menu
        );

        return new JsonResponse([
            'data' => $menuItems
        ]);
    }

    /**
     * @Route("/dashboard/ajax/package/get-billing-address", name="ajax_get_user_billing_address")
     */
    public function getBillingAddress(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $company = [];

        // Retrieve form data from request
        $formData = $request->request->all();

        /** @var User $user */
        $user = $this->getUser();

        // Process form submission
        if ($request->isMethod('POST') && !empty($user)) {
            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors and exist company
            if (!$validate['checkErrors']) {
                $company = $em->getRepository(UserBillingData::class)->getBillingAddress($user, $formData['uuid'], true);

                // Check exist data by @uuid
                if (empty($company)) {
                    return new JsonResponse([
                        'status' => false,
                        'company' => [],
                        'errors' => $validate['errors'],
                        'message' => 'Oops! Ceva nu a mers bine.'
                    ]);
                }
            }
        }

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'errors' => $validate['errors'],
            'company' => $company
        ]);
    }

    /**
     * @Route("/dashboard/ajax/package/user-billing-address-actions", name="ajax_user_billing_address_actions")
     */
    public function userBillingAddressActions(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper): JsonResponse
    {
        // Init default values
        $validate = ['checkErrors' => false, 'errors' => []];

        // Retrieve form data from request
        $formData = $request->request->all();
        $uuid = $formData['uuid'];

        // Remove @uuid
        unset($formData['uuid']);

        // Init propertyAccess
        $accessor = PropertyAccess::createPropertyAccessor();

        /** @var User $user */
        $user = $this->getUser();

        // Get action
        $company = !empty($uuid) ?
            $em->getRepository(UserBillingData::class)->findOneBy(['uuid' => $uuid, 'user' => $user]) :
            new UserBillingData();

        if ($request->isMethod('POST') && !empty($user)) {
            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors and exist company
            if (!$validate['checkErrors']) {

                /** @var County $county */
                $county = $em->getRepository(County::class)->findOneBy(['id' => $formData['county']]);

                /** @var City $city */
                $city = $em->getRepository(City::class)->find($formData['city']);

                // Check exist city and county
                if (empty($county) || empty($city)) {
                    return new JsonResponse([
                        'status' => false,
                        'company' => [],
                        'errors' => $validate['errors'],
                        'message' => 'Oops! Ceva nu a mers bine.'
                    ]);
                }

                // Set object data
                $formData['county'] = $county;
                $formData['city'] = $city;
                $formData['user'] = $user;

                // Parse and set values
                foreach ($formData as $field => $value) {
                    $accessor->setValue($company, $field, $value);
                }

                // Parse fields and save files
                $em->persist($company);
                $em->flush();
            }
        }

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'uuid' => $company->getUuid(),
            'errors' => $validate['errors'],
            'message' => !$validate['checkErrors']
                ? 'Editarea a fost realizată cu succes.'
                : 'Acest câmp este obligatoriu.',
        ]);
    }

    /**
     * @Route("/dashboard/ajax/package/user-billing-address/{action}", name="ajax_get_user_billing_address_action")
     */
    public function userBillingAddressAction(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, $action): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];

        // Retrieve form data from request
        $formData = $request->request->all();

        /** @var User $user */
        $user = $this->getUser();

        // Process form submission
        if ($request->isMethod('POST') && !empty($user)) {
            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors and exist company
            if (!$validate['checkErrors']) {
                /**
                 * Get address by @user and @uuid
                 * @var UserBillingData $company
                 */

                $company = $em->getRepository(UserBillingData::class)->findOneBy([
                    'user' => $user,
                    'uuid' => $formData['uuid']
                ]);

                // Check exist data by @uuid
                if (empty($company)) {
                    return new JsonResponse([
                        'status' => false,
                        'company' => [],
                        'errors' => $validate['errors'],
                        'message' => 'Oops! Ceva nu a mers bine.'
                    ]);
                }

                // Check type and set entity
                switch ($action) {
                    case 'moderate':
                        /**
                         * Get old item by @user
                         * @var UserBillingData $oldCompany
                         */
                        $oldCompany = $em->getRepository(UserBillingData::class)->findOneBy([
                            'user' => $user,
                            'isFavorite' => true
                        ]);

                        // Check exist old item
                        if (!empty($oldCompany)) {
                            // Update old item
                            $oldCompany->setIsFavorite(false);
                            $em->persist($oldCompany);
                        }

                        // Update data
                        $company->setIsFavorite(true);
                        $em->persist($company);
                        $em->flush();
                        break;
                    case 'remove':
                        // Remove item
                        $em->remove($company);
                        $em->flush();
                        break;
                }
            }
        }

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'message' => sprintf('Acest conținut a fost cu succes %s', $action === 'moderate' ? 'moderat' : 'șters')
        ]);
    }

    /**
     * @Route("/dashboard/ajax/package/get-billing-addresses", name="ajax_get_user_billing_addresses")
     */
    public function getBillingAddresses(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (empty($user)) {
            return new JsonResponse([
                'status' => false,
                'companies' => []
            ]);
        }

        /**
         * Get all by @user
         * @var UserBillingData $companies
         */
        $companies = $em->getRepository(UserBillingData::class)->getBillingAddress($user);

        return new JsonResponse([
            'status' => true,
            'companies' => $companies
        ]);
    }

    private function parseDatatableData(&$data, $params, $datatableHelper)
    {
        // filter by general search keyword
        if (isset($params['search']['value']) && $params['search']['value']) {
            $data = $datatableHelper->arraySearch($data, $params['search']['value']);
        }

        // sort
        if (isset($params['order'][0]['column']) && $params['order'][0]['dir']) {
            $column = $params['order'][0]['column'];
            $dir = $params['order'][0]['dir'];
            usort($data, function ($a, $b) use ($column, $dir) {
                $a = array_slice($a, $column, 1);
                $b = array_slice($b, $column, 1);
                $a = array_pop($a);
                $b = array_pop($b);
                $dates = false;

                if ($this->isDate($a) && $this->isDate($b)) {
                    $dates = true;
                    $aDate = strtotime($a);
                    $bDate = strtotime($b);
                }

                if ($dir === 'asc') {
                    if ($dates) {
                        return $aDate > $bDate ? 1 : -1;
                    }
                    return $a > $b ? 1 : -1;
                }

                if ($dates) {
                    return $aDate < $bDate ? 1 : -1;
                }

                return $a < $b ? 1 : -1;
            });
        }

        return $data;
    }

    private function isDate($value)
    {
        if (!$value) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getBetweenDates($startDate, $endDate)
    {
        $range = [];

        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);

        for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
            $date = date('Y-m-d', $currentDate);
            $range[] = $date;
        }

        return $range;
    }

    private function getFilteredObjects(array $params, EntityManagerInterface $em)
    {
        $objects = [];
        if (!empty($params['nursing-home'])) {
            $nursingHome = $em->getRepository(NursingHome::class)->findOneBy(['uid' => $params['nursing-home']]);
            if (null === $nursingHome) {
                throw new \Exception('Date invalide');
            }
            $objects['camin'] = $nursingHome;
        }
        if (!empty($params['pacient'])) {
            $pacient = $em->getRepository(Pacient::class)->findOneBy(['uid' => $params['pacient']]);
            if (null === $pacient) {
                throw new \Exception('Date invalide');
            }
            $objects['beneficiar'] = $pacient;
        }
        if (!empty($params['relation'])) {
            $relation = $em->getRepository(User::class)->findOneBy(['uid' => $params['relation']]);
            if (null === $relation) {
                throw new \Exception('Date invalide');
            }
            $objects['apartinator'] = $relation;
        }
        if (!empty($params['slug'])) {
            $document = $em->getRepository(Document::class)->findOneBy(['slug' => $params['slug']]);
            if (null === $document) {
                throw new \Exception('Date invalide');
            }

            $documentNumber = $em->getRepository(DocumentNumber::class)->findOneBy(['document' => $document, 'nursingHome' => $objects['camin']]);
            if (null === $documentNumber) {
                $documentNumber = new DocumentNumber();
                $documentNumber->setDocument($document);
                $documentNumber->setNursingHome($objects['camin']);
                $documentNumber->setDocumentNumber(1);

                $em->persist($documentNumber);
                $em->flush();
            }
            $objects['document'] = $documentNumber;
        }

        return $objects;
    }

    private function getClassName($object)
    {
        return substr(strrchr(get_class($object), '\\'), 1);
    }
}

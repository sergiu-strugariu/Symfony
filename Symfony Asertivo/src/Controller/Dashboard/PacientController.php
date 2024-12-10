<?php

namespace App\Controller\Dashboard;

use App\Entity\Email;
use App\Entity\NursingHome;
use App\Entity\Pacient;
use App\Entity\PacientAdmission;
use App\Entity\PacientClinicalExam;
use App\Entity\PacientComment;
use App\Entity\PacientCommentFile;
use App\Entity\PacientDiagnosis;
use App\Entity\PacientDischarge;
use App\Entity\PacientFile;
use App\Entity\PacientFileGroup;
use App\Entity\PacientFileType;
use App\Entity\PacientGeneralData;
use App\Entity\PacientMedicalData;
use App\Entity\PacientMedicationDetails;
use App\Entity\PacientRecordView;
use App\Entity\Prospect;
use App\Entity\User;
use App\Entity\UserRelation;
use App\Form\Type\PacientClinicalExamFormType;
use App\Form\Type\PacientDischargeFormType;
use App\Form\Type\PacientGeneralDataFormType;
use App\Form\Type\PacientMedicalDataFormType;
use App\Form\Type\PacientPersonalDataFormType;
use App\Form\Type\ProspectFormType;
use App\Helper\FileUploader;
use App\Mailer\TwigMailer;
use App\Repository\EmailRepository;
use App\Repository\NursingHomeRepository;
use App\Repository\NursingHomeRoomRepository;
use App\Repository\PacientCommentRepository;
use App\Repository\PacientDiagnosisRepository;
use App\Repository\PacientFileGroupRepository;
use App\Repository\PacientFileRepository;
use App\Repository\PacientFileTypeRepository;
use App\Repository\PacientMedicationDetailsRepository;
use App\Repository\PacientMonitoringMedicalRepository;
use App\Repository\PacientRepository;
use App\Repository\PacientRoomRepository;
use App\Repository\PacientVisitCalendarRepository;
use App\Repository\ProspectRepository;
use App\Repository\SummaryNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

class PacientController extends AbstractController
{
    const ADMISSION_WIZARD_STEPS = [
        [
            'title' => 'CNP',
            'subtitle' => 'Completare cod numeric personal'
        ],
        [
            'title' => 'Date personale',
            'subtitle' => 'Completare informatii personale'
        ],
        [
            'title' => 'Apartinatori',
            'subtitle' => 'Completare apartinatori'
        ],
        [
            'title' => 'Date generale',
            'subtitle' => 'Completare informatii generale'
        ],
        [
            'title' => 'Date medicale',
            'subtitle' => 'Completare date medicale'
        ],
        [
            'title' => 'Examen clinic',
            'subtitle' => 'Completare date examen clinic'
        ],
        [
            'title' => 'Acord GDPR',
            'subtitle' => 'Generare acord GDPR'
        ],
        [
            'title' => 'Contract',
            'subtitle' => 'Generare contract'
        ],
        [
            'title' => 'Dosar digital',
            'subtitle' => 'Incarcare documente'
        ],
        [
            'title' => 'Final',
            'subtitle' => 'Finalizeaza internarea'
        ]
    ];
    const DISCHARGE_WIZARD_STEPS = [
        [
            'title' => 'CNP',
            'subtitle' => 'Completare cod numeric personal'
        ],
        [
            'title' => 'Date externare',
            'subtitle' => 'Completare date externare'
        ],
        [
            'title' => 'Documente',
            'subtitle' => 'Incarcare documente externare'
        ],
        [
            'title' => 'Final',
            'subtitle' => 'Finalizeaza externarea'
        ]
    ];
    const PACIENTS_TABS = [
        [
            'path' => 'dashboard_pacients',
            'path_params' => [],
            'name' => 'Lista pacienti',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_RECEPTION', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_ASSISTANCE_MEDICAL_PHARMACY']
        ],
        [
            'path' => 'dashboard_pacients_admissions',
            'path_params' => [],
            'name' => 'Internari',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_ASSISTANCE_MEDICAL_PHARMACY']
        ],
        [
            'path' => 'dashboard_pacients_discharges',
            'path_params' => [],
            'name' => 'Externari',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_RECEPTION', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_ASSISTANCE_MEDICAL_PHARMACY']
        ],
        [
            'path' => 'dashboard_pacients_drugs',
            'path_params' => [],
            'name' => 'Administrare medicamentatie',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_ASSISTANCE_MEDICAL_PHARMACY']
        ],
        [
            'path' => 'dashboard_pacients_daily_monitoring_medical',
            'path_params' => [],
            'name' => 'Monitorizare functii vitale',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_ASSISTANCE_MEDICAL_PHARMACY', 'ROLE_MANAGEMENT']
        ]
    ];
    const PACIENT_TABS = [
        [
            'path' => 'dashboard_pacient_overview',
            'name' => 'Overview',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_RECEPTION', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_PHYSICAL_THERAPY']
        ],
        [
            'path' => '#',
            'name' => 'Date pacient',
            'items' => [
                [
                    'path' => 'dashboard_pacient_personal_data',
                    'name' => 'Date personale'
                ],
                [
                    'path' => 'dashboard_pacient_general_data',
                    'name' => 'Date generale'
                ],
                [
                    'path' => 'dashboard_pacient_medical_data',
                    'name' => 'Date medicale'
                ],
                [
                    'path' => 'dashboard_pacient_clinical_exam',
                    'name' => 'Examen clinic'
                ],
                [
                    'path' => 'dashboard_pacient_discharge_data',
                    'name' => 'Date externare',
                ]
            ],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_SOCIAL_WORKER', 'ROLE_PSYCHOTHERAPY']
        ],
        [
            'path' => '#',
            'name' => 'Monitorizare zilnica',
            'items' => [
                [
                    'path' => 'dashboard_pacient_daily_monitoring_medical',
                    'name' => 'Medical'
                ]
            ],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_PHYSICAL_THERAPY']
        ],
        [
            'path' => 'dashboard_pacient_relations',
            'name' => 'Apartinatori',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_RECEPTION', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_SOCIAL_WORKER', 'ROLE_PSYCHOTHERAPY']
        ],
        [
            'path' => '#',
            'name' => 'Schema tratament',
            'items' => [
                [
                    'path' => 'dashboard_pacient_diagnoses',
                    'name' => 'Diagnostice'
                ],
                [
                    'path' => 'dashboard_pacient_treament_plan',
                    'name' => 'Schema tratament'
                ],
                [
                    'path' => 'dashboard_pacient_medical_records',
                    'name' => 'Fisa medicala'
                ]
            ],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_MEDIC', 'ROLE_PHYSICAL_THERAPY']
        ],
        [
            'path' => 'dashboard_pacient_digital_record',
            'name' => 'Dosar digital',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL', 'ROLE_SOCIAL_WORKER', 'ROLE_PSYCHOTHERAPY']
        ],
        [
            'path' => 'dashboard_pacient_room',
            'name' => 'Camera',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC']
        ],
        [
            'path' => 'dashboard_pacient_reminders',
            'name' => 'Remindere',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_RECEPTION']
        ],
        [
            'path' => 'dashboard_pacient_visits',
            'name' => 'Vizite',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_RECEPTION']
        ]
    ];
    const PROSPECTS_TABS = [
        [
            'path' => 'dashboard_pacients_prospects_overview',
            'path_params' => [],
            'name' => 'Overview',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_pacients_prospects',
            'path_params' => [],
            'name' => 'Lista prospecti',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_pacients_prospects_scheduled',
            'path_params' => [],
            'name' => 'Vizionari programate',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_pacients_prospects_offers',
            'path_params' => [],
            'name' => 'Oferte trimise',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_pacients_prospects_sources',
            'path_params' => [],
            'name' => 'Sursa lead-uri',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ]
    ];
    const PROSPECT_TABS = [
        [
            'path' => 'dashboard_pacient_prospect',
            'name' => 'Editeaza',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_pacient_prospect_offer',
            'name' => 'Propunere financiara',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_pacient_prospect_onboarding',
            'name' => 'Inroleaza ca pacient',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ]
    ];
    const FLOORS = [
        'parter' => 'Parter',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5'
    ];

    /**
     * @Route("/dashboard/pacients/overview", name="dashboard_pacients")
     */
    public function index(PacientRepository $pacientRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        $nursingHome = $this->getUser()->getNursingHome();

        $pendingPacientsCount = $pacientRepository->findPacientsCountByStatus($nursingHome, Pacient::STATUS_PENDING);
        $admittedPacientsCount = $pacientRepository->findPacientsCountByStatus($nursingHome, Pacient::STATUS_ADMITTED);
        $dischargedPacientsCount = $pacientRepository->findPacientsCountByStatus($nursingHome, Pacient::STATUS_DISCHARGED);
        $archivedPacientsCount = $pacientRepository->findPacientsCountByStatus($nursingHome, Pacient::STATUS_ARCHIVED);

        /** @var User $user */
        $user = $this->getUser();

        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/pacient/index.html.twig', [
            'tabs' => self::PACIENTS_TABS,
            'nursingHomes' => $nursingHomes,
            'pendingPacientsCount' => $pendingPacientsCount,
            'admittedPacientsCount' => $admittedPacientsCount,
            'dischargedPacientsCount' => $dischargedPacientsCount,
            'archivedPacientsCount' => $archivedPacientsCount
        ]);
    }

    /**
     * @Route("/dashboard/pacients/drugs", name="dashboard_pacients_drugs")
     */
    public function drugs(Request $request, PacientRepository $pacientRepository, PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, PacientCommentRepository $pacientCommentRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $statusClasses = [
            PacientMedicationDetails::STATUS_PLANNED => 'warning',
            PacientMedicationDetails::STATUS_ADMINISTERED => 'success',
            PacientMedicationDetails::STATUS_REFUSED => 'danger'
        ];
        $date = $request->get('date', date('Y-m-d'));
        $floor = $request->get('floor', false);
        $nursingHome = $this->getUser()->getNursingHome();
        $pacients = $pacientRepository->findPacientsByNursingHome($user, $nursingHome, Pacient::STATUS_ADMITTED, $floor);

        $firstIntervalStart = \DateTime::createFromFormat('Y-m-d', $date)->modify('-1 day')->setTime(23, 0, 0);
        $firstIntervalEnd = \DateTime::createFromFormat('Y-m-d', $date)->setTime(6, 59, 0);
        $secondIntervalStart = \DateTime::createFromFormat('Y-m-d', $date)->setTime(7, 0, 0);
        $secondIntervalEnd = \DateTime::createFromFormat('Y-m-d', $date)->setTime(13, 59, 0);
        $thirdIntervalStart = \DateTime::createFromFormat('Y-m-d', $date)->setTime(14, 0, 0);
        $thirdIntervalEnd = \DateTime::createFromFormat('Y-m-d', $date)->setTime(18, 59, 0);
        $fourthIntervalStart = \DateTime::createFromFormat('Y-m-d', $date)->setTime(19, 0, 0);
        $fourthIntervalEnd = \DateTime::createFromFormat('Y-m-d', $date)->setTime(22, 59, 0);

        $pacientsMedicationDetails = [];
        foreach ($pacients as $pacient) {
            $pacientMedicationDetails = $pacientMedicationDetailsRepository->findTreatmentPlansByPacient($pacient['id'], $date, '%Y-%m-%d');
            $pacientNecessaryMedicationDetails = $pacientMedicationDetailsRepository->findNecessaryTreatmentPlansByPacient($pacient['id']);
            $latestComment = $pacientCommentRepository->findLatestCommentByPacient($pacient['id']);

            $pacientsMedicationDetails[$pacient['id']]['roomNumber'] = $pacient['roomNumber'];
            $pacientsMedicationDetails[$pacient['id']]['name'] = $pacient['name'];
            $pacientsMedicationDetails[$pacient['id']]['uid'] = $pacient['uid'];
            $pacientsMedicationDetails[$pacient['id']]['latestCommentAt'] = null !== $latestComment ? $latestComment->getFormattedCreatedAt('U') : null;
            $pacientsMedicationDetails[$pacient['id']]['necessary'] = [];
            foreach ($pacientMedicationDetails as $pacientMedicationDetail) {
                $treatmentDateFull = \DateTime::createFromFormat('Y-m-d H:i', $pacientMedicationDetail['treatmentDateFull']);
                if ($treatmentDateFull >= $firstIntervalStart && $treatmentDateFull <= $firstIntervalEnd) {
                    $this->addToMedicationDetails($pacientsMedicationDetails, $pacient['id'], 'night', $pacientMedicationDetail, $statusClasses);
                }
                if ($treatmentDateFull >= $secondIntervalStart && $treatmentDateFull <= $secondIntervalEnd) {
                    $this->addToMedicationDetails($pacientsMedicationDetails, $pacient['id'], 'morning', $pacientMedicationDetail, $statusClasses);
                }
                if ($treatmentDateFull >= $thirdIntervalStart && $treatmentDateFull <= $thirdIntervalEnd) {
                    $this->addToMedicationDetails($pacientsMedicationDetails, $pacient['id'], 'noon', $pacientMedicationDetail, $statusClasses);
                }
                if ($treatmentDateFull >= $fourthIntervalStart && $treatmentDateFull <= $fourthIntervalEnd) {
                    $this->addToMedicationDetails($pacientsMedicationDetails, $pacient['id'], 'evening', $pacientMedicationDetail, $statusClasses);
                }
            }
            foreach ($pacientNecessaryMedicationDetails as $pacientNecessaryMedicationDetail) {
                $pacientsMedicationDetails[$pacient['id']]['necessary'][] = [
                    'drug' => $pacientNecessaryMedicationDetail['drug'],
                    'dose' => $pacientNecessaryMedicationDetail['dose'],
                    'observations' => $pacientNecessaryMedicationDetail['observations']
                ];
            }
        }

        return $this->render('dashboard/pacient/drugs.html.twig', [
            'tabs' => self::PACIENTS_TABS,
            'date' => $date,
            'floor' => $floor,
            'floors' => self::FLOORS,
            'pacientsMedicationDetails' => $pacientsMedicationDetails
        ]);
    }

    /**
     * @Route("/dashboard/pacients/daily-monitoring/medical", name="dashboard_pacients_daily_monitoring_medical")
     */
    public function dailyMonitoringMedical(Request $request, PacientRepository $pacientRepository, PacientMonitoringMedicalRepository $pacientMonitoringMedicalRepository, NursingHomeRoomRepository $nursingHomeRoomRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $date = $request->get('date', date('Y-m-d'));
        $floor = $request->get('floor', false);
        $action = $request->get('action');
        $nursingHome = $this->getUser()->getNursingHome();
        $floors = $nursingHomeRoomRepository->findFloorsList($user);
        $pacients = $pacientRepository->findPacientsByNursingHome($user, $nursingHome, Pacient::STATUS_ADMITTED, $floor);
        $pacientsMonitoringMedical = [];
        foreach ($pacients as $pacient) {
            $pacientMonitoringMedicalData = $pacientMonitoringMedicalRepository->findMonitoringMedicalByPacient($pacient['id'], $date);
            $pacientsMonitoringMedical[$pacient['id']]['name'] = $pacient['name'];
            $pacientsMonitoringMedical[$pacient['id']]['roomNumber'] = $pacient['roomNumber'];
            $pacientsMonitoringMedical[$pacient['id']]['uid'] = $pacient['uid'];
            $pacientsMonitoringMedical[$pacient['id']]['data'] = $pacientMonitoringMedicalData;
        }

        if ($action == 'export') {
            $streamedResponse = new StreamedResponse();

            $streamedResponse->setCallback(function () use ($pacientsMonitoringMedical) {
                $handle = fopen('php://output', 'w+');

                // BOM: Allow to display special characters with excel
                fwrite($handle, $bom = chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')));

                // Set header
                fputcsv($handle, array(
                    'Nr crt',
                    'Nume pacient',
                    'Camera',
                    'Temperatura',
                    'Saturatie',
                    'Tensiune arteriala',
                    'Puls',
                    'Glicemie',
                    'Perfuzabile',
                    'Observatii'
                ), ';', '"', '\\');

                $i = 1;
                foreach ($pacientsMonitoringMedical as $pacientMonitoringMedical) {
                    $temperature = [];
                    $saturation = [];
                    $bloodPressure = [];
                    $heartRate = [];
                    $glucose = [];
                    $infusion = [];
                    $observations = [];
                    foreach ($pacientMonitoringMedical['data'] as $key => $pacientMonitoringMedicalData) {
                        $temperature[$key] = !empty($pacientMonitoringMedicalData['temperature']) ? sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['temperature']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                        $saturation[$key] = !empty($pacientMonitoringMedicalData['saturation']) ? sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['saturation']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                        $bloodPressure[$key] = (!empty($pacientMonitoringMedicalData['systolicBloodPressure']) && !empty($pacientMonitoringMedicalData['diastolicBloodPressure'])) ? sprintf('%s - %s cu %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['systolicBloodPressure'], $pacientMonitoringMedicalData['diastolicBloodPressure']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                        $heartRate[$key] = !empty($pacientMonitoringMedicalData['heartRate']) ? sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['heartRate']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                        $glucose[$key] = !empty($pacientMonitoringMedicalData['glucose']) ? sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['glucose']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                        $infusion[$key] = !empty($pacientMonitoringMedicalData['infusion']) ? sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['infusion']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                        $observations[$key] = !empty($pacientMonitoringMedicalData['observations']) ? sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], $pacientMonitoringMedicalData['observations']) : sprintf('%s - %s', $pacientMonitoringMedicalData['monitoringHour'], 'N/A');
                    }

                    fputcsv($handle, array(
                        $i,
                        $pacientMonitoringMedical['name'],
                        $pacientMonitoringMedical['roomNumber'],
                        implode(', ', $temperature),
                        implode(', ', $saturation),
                        implode(', ', $bloodPressure),
                        implode(', ', $heartRate),
                        implode(', ', $glucose),
                        implode(', ', $infusion),
                        implode(', ', $observations)
                    ), ';', '"', '\\');
                    $i++;
                }
                fclose($handle);
            });

            $filename = sprintf('Export_monitorizare_functii_vitale_%s.csv', $date);

            // Setting headers
            $streamedResponse->setStatusCode(Response::HTTP_OK);
            $streamedResponse->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

            return $streamedResponse;
        }

        return $this->render('dashboard/pacient/pacients_daily_monitoring_medical.html.twig', [
            'tabs' => self::PACIENTS_TABS,
            'pacientsMonitoringMedical' => $pacientsMonitoringMedical,
            'floors' => $floors,
            'selectedFloor' => $floor,
            'date' => \DateTime::createFromFormat('Y-m-d', $date)
        ]);
    }

    /**
     * @Route("/dashboard/pacients/prospects", name="dashboard_pacients_prospects")
     */
    public function prospects(Request $request, ProspectRepository $prospectRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        $status = $request->get('status', '');
        $date = $request->get('date', '');

        /** @var User $user */
        $user = $this->getUser();

        $pendingProspectsCount = $prospectRepository->findProspectsCountByStatus(Prospect::STATUS_IN_PROGRESS, $date);
        $waitingProspectsCount = $prospectRepository->findProspectsCountByStatus(Prospect::STATUS_PENDING, $date);
        $rejectedProspectsCount = $prospectRepository->findProspectsCountByStatus(Prospect::STATUS_REJECTED, $date);
        $acceptedProspectsCount = $prospectRepository->findProspectsCountByStatus(Prospect::STATUS_ACCEPTED, $date);
        $archivedProspectsCount = $prospectRepository->findProspectsCountByStatus(Prospect::STATUS_ARCHIVED, $date);

        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/pacient/prospects.html.twig', [
            'tabs' => self::PROSPECTS_TABS,
            'status' => $status,
            'date' => $date,
            'pendingProspectsCount' => $pendingProspectsCount,
            'waitingProspectsCount' => $waitingProspectsCount,
            'rejectedProspectsCount' => $rejectedProspectsCount,
            'acceptedProspectsCount' => $acceptedProspectsCount,
            'archivedProspectsCount' => $archivedProspectsCount,
            'nursingHomes' => $nursingHomes
        ]);
    }

    /**
     * @Route("/dashboard/pacients/prospects/overview", name="dashboard_pacients_prospects_overview")
     */
    public function prospectsOverview(Request $request, ProspectRepository $prospectRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $year = $request->get('year', date('Y'));
        $prospectsCountByMonthAndStatus = $prospectRepository->findProspectsCountByStatusAndMonth($user, $year);
        $totalOffersByMonth = $prospectRepository->findTotalOffersByMonth($user, $year);
        $rejectedOffersByMonth = $prospectRepository->findTotalOffersByMonth($user, $year, true);
        $acceptedOffersByMonth = $prospectRepository->findTotalOffersByMonth($user, $year, false, true);
        $totalViewingsByMonth = $prospectRepository->findViewingsByMonth($user, $year);
        $canceledViewingsByMonth = $prospectRepository->findViewingsByMonth($user, $year, true);
        $scheduledViewingsByMonth = $prospectRepository->findViewingsByMonth($user, $year, false, true);

        $monthlyProspectsCount = [];
        $monthlyOffers = [];
        $monthlyViewings = [];
        for ($i = 12; $i >= 1; $i--) {
            foreach ($prospectsCountByMonthAndStatus as $prospectCountByMonthAndStatus) {
                if ($prospectCountByMonthAndStatus['month'] == $i) {
                    $monthlyProspectsCount[$i][$prospectCountByMonthAndStatus['status']] = $prospectCountByMonthAndStatus['count'];
                }
            }
            foreach ($totalOffersByMonth as $totalOfferByMonth) {
                if ($totalOfferByMonth['month'] == $i) {
                    $monthlyOffers[$i]['count'] = (int)$totalOfferByMonth['count'];
                    $monthlyOffers[$i]['total'] = (int)$totalOfferByMonth['total'];
                }
            }
            foreach ($rejectedOffersByMonth as $rejectedOfferByMonth) {
                if ($rejectedOfferByMonth['month'] == $i) {
                    $monthlyOffers[$i]['rejected'] = (int)$rejectedOfferByMonth['total'];
                    $monthlyOffers[$i]['rejectedCount'] = (int)$rejectedOfferByMonth['count'];
                }
            }
            foreach ($acceptedOffersByMonth as $acceptedOfferByMonth) {
                if ($acceptedOfferByMonth['month'] == $i) {
                    $monthlyOffers[$i]['accepted'] = (int)$acceptedOfferByMonth['total'];
                    $monthlyOffers[$i]['acceptedCount'] = (int)$acceptedOfferByMonth['count'];
                }
            }
            foreach ($totalViewingsByMonth as $totalViewingByMonth) {
                if ($totalViewingByMonth['month'] == $i) {
                    $monthlyViewings[$i]['total'] = (int)$totalViewingByMonth['total'];
                }
            }
            foreach ($canceledViewingsByMonth as $canceledViewingByMonth) {
                if ($canceledViewingByMonth['month'] == $i) {
                    $monthlyViewings[$i]['canceled'] = (int)$canceledViewingByMonth['total'];
                }
            }
            foreach ($scheduledViewingsByMonth as $scheduledViewingByMonth) {
                if ($scheduledViewingByMonth['month'] == $i) {
                    $monthlyViewings[$i]['scheduled'] = (int)$scheduledViewingByMonth['total'];
                }
            }
        }

        $data = [];
        for ($i = 12; $i >= 1; $i--) {
            $data[$i]['inProgressProspectsCount'] = isset($monthlyProspectsCount[$i][Prospect::STATUS_IN_PROGRESS]) ? $monthlyProspectsCount[$i][Prospect::STATUS_IN_PROGRESS] : 0;
            $data[$i]['pendingProspectsCount'] = isset($monthlyProspectsCount[$i][Prospect::STATUS_PENDING]) ? $monthlyProspectsCount[$i][Prospect::STATUS_PENDING] : 0;
            $data[$i]['acceptedProspectsCount'] = isset($monthlyProspectsCount[$i][Prospect::STATUS_ACCEPTED]) ? $monthlyProspectsCount[$i][Prospect::STATUS_ACCEPTED] : 0;
            $data[$i]['rejectedProspectsCount'] = isset($monthlyProspectsCount[$i][Prospect::STATUS_REJECTED]) ? $monthlyProspectsCount[$i][Prospect::STATUS_REJECTED] : 0;
            $data[$i]['archivedProspectsCount'] = isset($monthlyProspectsCount[$i][Prospect::STATUS_ARCHIVED]) ? $monthlyProspectsCount[$i][Prospect::STATUS_ARCHIVED] : 0;
            $data[$i]['totalProspectsCount'] = $data[$i]['inProgressProspectsCount'] + $data[$i]['pendingProspectsCount'] + $data[$i]['acceptedProspectsCount'] + $data[$i]['rejectedProspectsCount'] + $data[$i]['archivedProspectsCount'];
            $data[$i]['sentOffersCount'] = isset($monthlyOffers[$i]['count']) ? $monthlyOffers[$i]['count'] : 0;
            $data[$i]['rejectedOffersCount'] = isset($monthlyOffers[$i]['rejectedCount']) ? $monthlyOffers[$i]['rejectedCount'] : 0;
            $data[$i]['acceptedOffersCount'] = isset($monthlyOffers[$i]['acceptedCount']) ? $monthlyOffers[$i]['acceptedCount'] : 0;
            $data[$i]['sentOffersValue'] = isset($monthlyOffers[$i]['total']) ? $monthlyOffers[$i]['total'] : 0;
            $data[$i]['rejectedOffersValue'] = isset($monthlyOffers[$i]['rejected']) ? $monthlyOffers[$i]['rejected'] : 0;
            $data[$i]['acceptedOffersValue'] = isset($monthlyOffers[$i]['accepted']) ? $monthlyOffers[$i]['accepted'] : 0;
            $data[$i]['totalViewingsCount'] = isset($monthlyViewings[$i]['total']) ? $monthlyViewings[$i]['total'] : 0;
            $data[$i]['canceledViewingsCount'] = isset($monthlyViewings[$i]['canceled']) ? $monthlyViewings[$i]['canceled'] : 0;
            $data[$i]['scheduledViewingsCount'] = isset($monthlyViewings[$i]['scheduled']) ? $monthlyViewings[$i]['scheduled'] : 0;
        }

        return $this->render('dashboard/pacient/prospects_overview.html.twig', [
            'tabs' => self::PROSPECTS_TABS,
            'year' => $year,
            'data' => $data
        ]);
    }

    /**
     * @Route("/dashboard/pacients/prospects/scheduled", name="dashboard_pacients_prospects_scheduled")
     */
    public function prospectsScheduled(Request $request): Response
    {
        $date = $request->get('date', '');

        return $this->render('dashboard/pacient/prospects_scheduled.html.twig', [
            'date' => $date,
            'tabs' => self::PROSPECTS_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacients/prospects/offers", name="dashboard_pacients_prospects_offers")
     */
    public function prospectsOffers(Request $request): Response
    {
        $date = $request->get('date', '');

        return $this->render('dashboard/pacient/prospects_offers.html.twig', [
            'date' => $date,
            'tabs' => self::PROSPECTS_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacients/prospects/sources", name="dashboard_pacients_prospects_sources")
     */
    public function prospectsSources(): Response
    {
        return $this->render('dashboard/pacient/prospects_sources.html.twig', [
            'tabs' => self::PROSPECTS_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacient/prospect/{uuid}", name="dashboard_pacient_prospect")
     */
    public function prospect(ProspectRepository $prospectRepository, $uuid): Response
    {
        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->redirectToRoute('dashboard_pacients_prospects');
        }

        $form = $this->createForm(ProspectFormType::class, $prospect);

        return $this->render('dashboard/pacient/prospect.html.twig', [
            'pacient' => $prospect,
            'tabs' => self::PROSPECT_TABS,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/pacient/prospect/{uuid}/offer", name="dashboard_pacient_prospect_offer")
     */
    public function prospectOffer(Request $request, ProspectRepository $prospectRepository, EmailRepository $emailRepository, TwigMailer $twigMailer, EntityManagerInterface $em, $uuid): Response
    {
        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->redirectToRoute('dashboard_pacients_prospects');
        }

        $emails = $emailRepository->findBy(['recipientEmail' => $prospect->getEmail()]);

        if ($request->isMethod('POST')) {
            $to = [];
            $cc = [];
            $bcc = [];
            $toEmails = $request->get('to');
            $ccEmails = $request->get('cc');
            $bccEmails = $request->get('bcc');
            $subject = $request->get('subject');
            $message = $request->get('message');
            $price = $request->get('price');
            $flashBag = $request->getSession()->getFlashBag();
            if (empty($toEmails) || empty($subject) || empty($message) || empty($price)) {
                $flashBag->set('danger', 'Te rugam sa completezi toate campurile obligatorii');

                return $this->redirectToRoute('dashboard_pacient_prospect_offer', ['uuid' => $uuid]);
            }

            $toEmails = json_decode($toEmails, true);
            foreach ($toEmails as $key => $data) {
                $to[] = $data['value'];
            }
            if (!empty($ccEmails)) {
                $ccEmails = json_decode($ccEmails, true);
                foreach ($ccEmails as $key => $data) {
                    $cc[] = $data['value'];
                }
            }
            if (!empty($bccEmails)) {
                $bccEmails = json_decode($bccEmails, true);
                foreach ($bccEmails as $key => $data) {
                    $bcc[] = $data['value'];
                }
            }

            $message = str_replace('{pret}', $price, $message);
            $attachments = [
                sprintf('%s/public/assets/doc/%s', $this->getParameter('kernel.project_dir'), 'Asertivo_Servicii_Premium_Seniori.pdf')
            ];
            $sent = $twigMailer->sendProspectOfferMessage($to, $subject, $message, $attachments, $cc, $bcc);

            if ($sent === false) {
                $flashBag->set('danger', 'E-mail-ul nu a fost trimis, te rugam sa incerci din nou mai tarziu');
            } else {
                $prospect->setOfferSentAt(new \DateTime());
                $prospect->setOfferPrice($price);
                $em->persist($prospect);
                $em->flush();

                // create new email
                $email = new Email();
                $email->setSenderName($this->getParameter('from_sender'));
                $email->setSenderEmail($this->getParameter('from_email'));
                $email->setRecipientName($prospect->getRelationName());
                $email->setRecipientEmail(implode(',', $to));
                $email->setSubject($subject);
                $email->setOpened(false);
                $email->setClicks(0);
                $email->setContent($this->renderView('dashboard/shared/email/prospect_offer.html.twig', [
                    'message' => $message
                ]));
                $email->setCreatedAt(new \DateTime());
                // write to DB
                $em->persist($email);
                $em->flush();

                $flashBag->set('primary', 'E-mail-ul a fost trimis cu succes!');

                return $this->redirectToRoute('dashboard_pacient_prospect_offer', ['uuid' => $uuid]);
            }
        }

        return $this->render('dashboard/pacient/prospect_offer.html.twig', [
            'pacient' => $prospect,
            'emails' => $emails,
            'tabs' => self::PROSPECT_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacient/prospect/{uuid}/onboarding", name="dashboard_pacient_prospect_onboarding")
     */
    public function prospectOnboarding(Request $request, ProspectRepository $prospectRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger, $uuid): Response
    {
        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->redirectToRoute('dashboard_pacients_prospects');
        }

        if ($request->isMethod('POST')) {
            $name = $prospect->getBeneficiaryName();
            $nursingHome = $prospect->getNursingHome();

            $pacient = new Pacient();
            $pacient->setUid(Uuid::v4());
            $pacient->setFirstName($name);
            $pacient->setNursingHome($prospect->getNursingHome());
            $pacient->setStatus(Pacient::STATUS_PENDING);
            $pacient->setCreatedAt(new \DateTime());

            $sluggedName = $slugger->slug($name, '.');
            $sluggedNursingHomeName = $slugger->slug($nursingHome->getName(), '-');
            $email = sprintf('%s@pacient-%s.ro', strtolower($sluggedName), strtolower($sluggedNursingHomeName));
            $pacient->setEmail($email);

            $prospect->setOnboarded(true);

            $em->persist($pacient);
            $em->persist($prospect);
            $em->flush();

            return $this->redirectToRoute('dashboard_pacient_prospect', ['uuid' => $prospect->getUid()]);
        }

        return $this->render('dashboard/pacient/prospect_onboarding.html.twig', [
            'pacient' => $prospect,
            'tabs' => self::PROSPECT_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacients/admissions", name="dashboard_pacients_admissions")
     */
    public function admissions(): Response
    {
        return $this->render('dashboard/pacient/admissions.html.twig', [
            'tabs' => self::PACIENTS_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacients/discharges", name="dashboard_pacients_discharges")
     */
    public function discharges(): Response
    {
        return $this->render('dashboard/pacient/discharges.html.twig', [
            'tabs' => self::PACIENTS_TABS
        ]);
    }

    /**
     * @Route("/dashboard/pacients/readmission/{uuid}", name="dashboard_pacient_readmission")
     */
    public function pacientReadmission(EntityManagerInterface $em, $uuid): Response
    {
        /** @var Pacient $pacient */
        $pacient = $em->getRepository(Pacient::class)->findOneBy(['uid' => $uuid]);

        if ($pacient === null) {
            $this->addFlash('danger', 'A intervenit o eroare neprevăzută, te rugăm să încerci mai târziu.');
            return $this->redirectToRoute('dashboard_pacients_discharges');
        }

        // Update status
        $pacient->setStatus(Pacient::STATUS_ADMITTED);

        // Persist and save
        $em->persist($pacient);
        $em->flush();

        $this->addFlash('success', 'Pacientul a fost readmis cu success.');
        return $this->redirectToRoute('dashboard_pacients');
    }

    /**
     * @Route("/dashboard/pacients/admission/wizard/{step}", name="dashboard_pacients_admission_wizard")
     */
    public function admissionWizard(Request $request, EntityManagerInterface $em, SessionInterface $session, $step): Response
    {
        $pacient = null;
        $form = null;
        $parameters = [];

        switch ($step) {
            case 1:
                if ($request->isMethod('POST')) {
                    $cnp = $request->get('cnp');
                    $pacient = $em->getRepository(Pacient::class)->findOneBy(['cnp' => $cnp]);
                    if (null === $pacient) {
                        $session->getFlashBag()->set('danger', 'Pacientul nu a fost găsit.');
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                    }

                    // save user id in the session to use in the next steps
                    $session->set('userId', $pacient->getId());

                    $step++;
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                }
                break;
            case 2:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                if ($request->isMethod('POST')) {
                    $step++;
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                }

                $form = $this->createForm(PacientPersonalDataFormType::class, $pacient);
                break;
            case 3:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                if ($request->isMethod('POST')) {
                    $userRelationsCount = $em->getRepository(UserRelation::class)->findRelationsCountByUser($pacient);

                    if (0 == $userRelationsCount) {
                        $session->getFlashBag()->set('danger', 'Te rugam sa adaugi minim un apartinator');
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    }

                    $step++;
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                }

                break;
            case 4:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                $generalDataUuid = $session->get('generalDataUuid');
                $pacientGeneralData = new PacientGeneralData();

                if (null !== $generalDataUuid) {
                    $pacientGeneralData = $em->getRepository(PacientGeneralData::class)->findOneBy(['uid' => $generalDataUuid]);
                    if (null === $pacientGeneralData) {
                        $pacientGeneralData = new PacientGeneralData();
                    }
                }

                $form = $this->createForm(PacientGeneralDataFormType::class, $pacientGeneralData, ['require_all_fields' => true]);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $uuid = null === $generalDataUuid ? Uuid::v4() : $generalDataUuid;
                        $pacientGeneralData->setPacient($pacient);
                        $pacientGeneralData->setAddedBy($this->getUser());
                        $pacientGeneralData->setUid($uuid);
                        $pacientGeneralData->setCreatedAt(new \DateTime());

                        $em->persist($pacientGeneralData);
                        $em->flush();

                        // set pacient general data UUID in the session
                        $session->set('generalDataUuid', $uuid);

                        $step++;
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    }
                }

                break;
            case 5:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                $medicalDataUuid = $session->get('medicalDataUuid');
                $pacientMedicalData = new PacientMedicalData();

                if (null !== $medicalDataUuid) {
                    $pacientMedicalData = $em->getRepository(PacientMedicalData::class)->findOneBy(['uid' => $medicalDataUuid]);
                    if (null === $pacientMedicalData) {
                        $pacientMedicalData = new PacientMedicalData();
                    }
                }

                $form = $this->createForm(PacientMedicalDataFormType::class, $pacientMedicalData);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $uuid = null === $medicalDataUuid ? Uuid::v4() : $medicalDataUuid;
                        $pacientMedicalData->setPacient($pacient);
                        $pacientMedicalData->setAddedBy($this->getUser());
                        $pacientMedicalData->setUid($uuid);
                        $pacientMedicalData->setCreatedAt(new \DateTime());

                        $em->persist($pacientMedicalData);
                        $em->flush();

                        // set pacient general data UUID in the session
                        $session->set('medicalDataUuid', $uuid);

                        $step++;
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    }
                }

                break;
            case 6:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                $clinicalExamUuid = $session->get('clinicalExamUuid');
                $pacientClinicalExam = new PacientClinicalExam();

                if (null !== $clinicalExamUuid) {
                    $pacientClinicalExam = $em->getRepository(PacientClinicalExam::class)->findOneBy(['uid' => $clinicalExamUuid]);
                    if (null === $pacientClinicalExam) {
                        $pacientClinicalExam = new PacientClinicalExam();
                    }
                }

                $form = $this->createForm(PacientClinicalExamFormType::class, $pacientClinicalExam);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $uuid = null === $clinicalExamUuid ? Uuid::v4() : $clinicalExamUuid;
                        $pacientClinicalExam->setPacient($pacient);
                        $pacientClinicalExam->setAddedBy($this->getUser());
                        $pacientClinicalExam->setUid($uuid);
                        $pacientClinicalExam->setCreatedAt(new \DateTime());

                        $em->persist($pacientClinicalExam);
                        $em->flush();

                        // set pacient clinical exam UUID in the session
                        $session->set('clinicalExamUuid', $uuid);

                        $step++;
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    }
                }

                break;
            case 7:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                $nursingHomes = $em->getRepository(NursingHome::class)->findNursingHomesByUser($this->getUser());
                $userRelations = $em->getRepository(UserRelation::class)->findRelationsByUser($pacient);

                $parameters = [
                    'nursingHomes' => $nursingHomes,
                    'userRelations' => $userRelations,
                    'type' => 'gdpr'
                ];

                if ($request->isMethod('POST')) {
                    if ($session->has('gdpr')) {
                        $step++;
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    } else {
                        $session->getFlashBag()->set('danger', 'Te rugam sa generezi si sa descarci acordul GDPR inainte de a merge mai departe');
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    }
                }

                break;
            case 8:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                $nursingHomes = $em->getRepository(NursingHome::class)->findNursingHomesByUser($this->getUser());
                $userRelations = $em->getRepository(UserRelation::class)->findRelationsByUser($pacient);

                $parameters = [
                    'nursingHomes' => $nursingHomes,
                    'userRelations' => $userRelations,
                    'type' => 'contract'
                ];

                if ($request->isMethod('POST')) {
                    if ($session->has('contract')) {
                        $step++;
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    } else {
                        $session->getFlashBag()->set('danger', 'Te rugam sa generezi si sa descarci contractul inainte de a merge mai departe');
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                    }
                }

                break;
            case 9:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                $pacientFileGroups = $em->getRepository(PacientFileGroup::class)->findBy(['id' => [1, 2]]);
                $fileTypes = $em->getRepository(PacientFileType::class)->findAll([], ['position' => 'ASC']);

                foreach ($fileTypes as $fileType) {
                    $hasFile = $em->getRepository(PacientFile::class)->findOneBy([
                        'pacient' => $pacient,
                        'fileType' => $fileType
                    ]);

                    if (null === $hasFile) {
                        $pacientFile = new PacientFile();

                        $pacientFile->setPacient($pacient);
                        $pacientFile->setUid(Uuid::v4());
                        $pacientFile->setUserResponsible($this->getUser());
                        $pacientFile->setFileType($fileType);
                        $pacientFile->setFileGroup($fileType->getFileGroup());
                        $pacientFile->setCreatedAt(new \DateTime());
                        $pacientFile->setFileName(sprintf('%s %s %s', $fileType->getName(), $pacient->getLastName(), $pacient->getFirstName()));
                        $pacientFile->setStatus('In asteptare');
                        $pacientFile->setViews(0);

                        $em->persist($pacientFile);
                        $em->flush();
                    }
                }

                $pacientFiles = $em->getRepository(PacientFile::class)->findFilesByPacient($pacient);

                $parameters = [
                    'files' => $pacientFiles,
                    'groups' => $pacientFileGroups
                ];

                if ($request->isMethod('POST')) {
                    $requiredFileTypes = $em->getRepository(PacientFileType::class)->findBy(['requiredOnAdmission' => true]);
                    foreach ($requiredFileTypes as $requiredFileType) {
                        $requiredFile = $em->getRepository(PacientFile::class)->findOneBy([
                            'pacient' => $pacient,
                            'fileType' => $requiredFileType
                        ]);

                        if (null === $requiredFile->getFilePath()) {
                            $session->getFlashBag()->set('danger', 'Te rugam sa incarci toate fisierele obligatorii (cerere institutionalizare, CI beneficiar, CI apartinator, aranjament de plata, acord GDPR, contract)');
                            return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                        }
                    }

                    $step++;
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => $step]);
                }

                break;
            case 10:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                }

                if ($request->isMethod('POST')) {
                    // change status to "Internat"
                    $pacient->setStatus(Pacient::STATUS_ADMITTED);
                    $em->persist($pacient);

                    $pacientGeneralData = $em->getRepository(PacientGeneralData::class)->findLatestGeneralData($pacient);
                    if (null === $pacientGeneralData) {
                        return $this->redirectToRoute('dashboard_pacients_admission_wizard', ['step' => 1]);
                    }

                    // add admission data
                    $pacientAdmission = new PacientAdmission();
                    $pacientAdmission->setPacient($pacient);
                    $pacientAdmission->setUid(Uuid::v4());
                    $pacientAdmission->setAdmissionDate(new \DateTime());
                    $pacientAdmission->setAdmittedBy($this->getUser());
                    $pacientAdmission->setPacientType($pacientGeneralData->getPacientType());
                    $pacientAdmission->setPacientMobility($pacientGeneralData->getPacientMobility());
                    $pacientAdmission->setDiet($pacientGeneralData->getDiet());
                    $pacientAdmission->setNosocomialInfection($pacientGeneralData->getNosocomialInfection());
                    $pacientAdmission->setRequiresMedicalBed($pacientGeneralData->isRequiresMedicalBed());
                    $pacientAdmission->setRequiresDiapers($pacientGeneralData->isRequiresDiapers());
                    $em->persist($pacientAdmission);

                    $em->flush();

                    // remove data from session
                    $session->remove('userId');
                    $session->remove('generalDataUuid');
                    $session->remove('medicalDataUuid');
                    $session->remove('clinicalExamUuid');
                    $session->remove('gdpr');
                    $session->remove('contract');
                    // redirect to admissions page
                    return $this->redirectToRoute('dashboard_pacients_admissions');
                }
                break;
            default:
                break;
        }

        $defaultParameters = [
            'steps' => self::ADMISSION_WIZARD_STEPS,
            'user' => $pacient,
            'activeStep' => $step,
            'form' => null !== $form ? $form->createView() : null
        ];
        $parameters = array_merge($parameters, $defaultParameters);

        return $this->render('dashboard/pacient/admission_wizard.html.twig', $parameters);
    }

    /**
     * @Route("/dashboard/pacients/discharge/wizard/{step}", name="dashboard_pacients_discharge_wizard")
     */
    public function dischargeWizard(Request $request, EntityManagerInterface $em, SessionInterface $session, $step): Response
    {
        $pacient = null;
        $form = null;
        $parameters = [];

        switch ($step) {
            case 1:
                $selectedCnp = $request->query->get('cnp', '');
                $parameters = [
                    'selectedCnp' => $selectedCnp
                ];
                if ($request->isMethod('POST')) {
                    $cnp = $request->get('cnp');
                    $pacient = $em->getRepository(Pacient::class)->findOneBy(['cnp' => $cnp]);

                    if (null === $pacient) {
                        $session->getFlashBag()->set('danger', 'Pacientul nu a fost gasit');
                        return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => 1]);
                    }

                    // save user id in the session to use in the next steps
                    $session->set('userId', $pacient->getId());

                    $step++;
                    return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => $step]);
                }

                break;
            case 2:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => 1]);
                }

                $dischargeUuid = $session->get('dischargeUuid');
                $pacientDischarge = new PacientDischarge();

                if (null !== $dischargeUuid) {
                    $pacientDischarge = $em->getRepository(PacientDischarge::class)->findOneBy(['uid' => $dischargeUuid]);
                    if (null === $pacientDischarge) {
                        $pacientDischarge = new PacientClinicalExam();
                    }
                }

                $form = $this->createForm(PacientDischargeFormType::class, $pacientDischarge, ['require_discharge_reason' => true]);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $uuid = null === $dischargeUuid ? Uuid::v4() : $dischargeUuid;
                        $pacientDischarge->setPacient($pacient);
                        $pacientDischarge->setDischargedBy($this->getUser());
                        $pacientDischarge->setUid($uuid);

                        $em->persist($pacientDischarge);
                        $em->flush();

                        // set discharge UUID in the session
                        $session->set('dischargeUuid', $uuid);

                        $step++;
                        return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => $step]);
                    }
                }
            case 3:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => 1]);
                }

                $pacientFileGroups = $em->getRepository(PacientFileGroup::class)->findBy(['id' => 4]);
                $fileTypes = $em->getRepository(PacientFileType::class)->findAll([], ['position' => 'ASC']);

                foreach ($fileTypes as $fileType) {
                    $hasFile = $em->getRepository(PacientFile::class)->findOneBy([
                        'pacient' => $pacient,
                        'fileType' => $fileType
                    ]);

                    if (null === $hasFile) {
                        $pacientFile = new PacientFile();

                        $pacientFile->setPacient($pacient);
                        $pacientFile->setUid(Uuid::v4());
                        $pacientFile->setUserResponsible($this->getUser());
                        $pacientFile->setFileType($fileType);
                        $pacientFile->setFileGroup($fileType->getFileGroup());
                        $pacientFile->setCreatedAt(new \DateTime());
                        $pacientFile->setFileName(sprintf('%s %s %s', $fileType->getName(), $pacient->getLastName(), $pacient->getFirstName()));
                        $pacientFile->setStatus('In asteptare');
                        $pacientFile->setViews(0);

                        $em->persist($pacientFile);
                        $em->flush();
                    }
                }

                $pacientFiles = $em->getRepository(PacientFile::class)->findFilesByPacient($pacient);

                $parameters = [
                    'files' => $pacientFiles,
                    'groups' => $pacientFileGroups
                ];

                if ($request->isMethod('POST')) {
                    $requiredFileTypes = $em->getRepository(PacientFileType::class)->findBy(['requiredOnDischarge' => true]);
                    foreach ($requiredFileTypes as $requiredFileType) {
                        $requiredFile = $em->getRepository(PacientFile::class)->findOneBy([
                            'pacient' => $pacient,
                            'fileType' => $requiredFileType
                        ]);

                        if (null === $requiredFile->getFilePath()) {
                            $session->getFlashBag()->set('danger', 'Te rugam sa incarci toate fisierele obligatorii (fisa de externare)');
                            return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => $step]);
                        }
                    }

                    $step++;
                    return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => $step]);
                }

                break;
            case 4:
                $pacient = $this->checkPacient($session, $em);
                if (null === $pacient) {
                    return $this->redirectToRoute('dashboard_pacients_discharge_wizard', ['step' => 1]);
                }

                if ($request->isMethod('POST')) {
                    // change status to "Externat"
                    $pacient->setStatus(Pacient::STATUS_DISCHARGED);
                    $em->persist($pacient);
                    $em->flush();

                    // remove data from session
                    $session->remove('userId');
                    $session->remove('dischargeUuid');
                    // redirect to discharges page
                    return $this->redirectToRoute('dashboard_pacients_discharges');
                }

                break;
            default:
                break;
        }


        $defaultParameters = [
            'steps' => self::DISCHARGE_WIZARD_STEPS,
            'user' => $pacient,
            'activeStep' => $step,
            'form' => null !== $form ? $form->createView() : null
        ];
        $parameters = array_merge($parameters, $defaultParameters);

        return $this->render('dashboard/pacient/discharge_wizard.html.twig', $parameters);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/overview", name="dashboard_pacient_overview")
     */
    public function pacientOverview(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Overview',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/overview.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/personal-data", name="dashboard_pacient_personal_data")
     */
    public function pacientPersonalData(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $form = $this->createForm(PacientPersonalDataFormType::class, $pacient);
        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Date personale',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/personal_data.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/general-data", name="dashboard_pacient_general_data")
     */
    public function pacientGeneralData(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Date generale',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/general_data.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/medical-data", name="dashboard_pacient_medical_data")
     */
    public function pacientMedicalData(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Date medicale',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/medical_data.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/clinical-exam", name="dashboard_pacient_clinical_exam")
     */
    public function pacientClinicalExam(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Examen clinic',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/clinical_exam.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/diagnoses", name="dashboard_pacient_diagnoses")
     */
    public function pacientDiagnoses(Request $request, PacientRepository $pacientRepository, PacientDiagnosisRepository $pacientDiagnosisRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $pacientDiagnoses = $pacientDiagnosisRepository->findDiagnosesByPacient($pacient, false);

        if ($request->isMethod('POST')) {
            $params = $request->request->all();
            if (empty($params['diagnosis'])) {
                return $this->redirectToRoute('dashboard_pacient_diagnoses', ['uuid' => $uuid]);
            }

            $pacientDiagnosis = new PacientDiagnosis();
            $pacientDiagnosis->setDiagnosis($params['diagnosis']);
            $pacientDiagnosis->setPacient($pacient);
            $pacientDiagnosis->setMedic($this->getUser());
            $pacientDiagnosis->setCreatedAt(new \DateTime());
            if (!empty($params['observations'])) {
                $pacientDiagnosis->setObservations($params['observations']);
            }

            // write data to DB
            $em->persist($pacientDiagnosis);
            $em->flush();

            return $this->redirectToRoute('dashboard_pacient_diagnoses', ['uuid' => $uuid]);
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Diagnostice',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/diagnoses.html.twig', [
            'tabs' => self::PACIENT_TABS,
            'pacient' => $pacient,
            'pacientDiagnoses' => $pacientDiagnoses,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/treatment-plan", name="dashboard_pacient_treament_plan")
     */
    public function pacientTreatmentPlan(Request $request, PacientRepository $pacientRepository, PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $date = $request->get('date', (new \DateTime())->format('m-Y'));
        $treatmentPlans = $pacientMedicationDetailsRepository->findTreatmentPlansByPacient($pacient->getId(), $date);
        $plans = [];
        foreach ($treatmentPlans as $plan) {
            $plans[$plan['treatmentDate']][] = [
                'hour' => $plan['treatmentHour'],
                'details' => sprintf('%s (%s) %s', $plan['drug'], $plan['dose'], $plan['observations']),
                'status' => $plan['status'],
                'assistant' => (!empty($plan['firstName'] && !empty($plan['lastName']))) ? sprintf('%s %s', $plan['firstName'], $plan['lastName']) : null
            ];
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Schema tratament',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/treatment_plan.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'treatmentPlans' => $plans,
            'date' => $date,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/medical-records", name="dashboard_pacient_medical_records")
     */
    public function pacientMedicalRecords(Request $request, PacientRepository $pacientRepository, PacientDiagnosisRepository $pacientDiagnosisRepository, PacientMedicationDetailsRepository $pacientMedicationDetailsRepository, PacientCommentRepository $pacientCommentRepository, EntityManagerInterface $em, FileUploader $fileUploader, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        if ($request->isMethod('POST')) {
            $action = $request->get('action');

            switch ($action) {
                case 'validate':
                    $observations = $request->get('observations');
                    $valid = $request->get('valid', false);

                    $pacient->setTreatmentPlanAuditedBy($this->getUser());
                    $pacient->setTreatmentPlanAuditObservations($observations);
                    $pacient->setTreatmentPlanAuditDate(new \DateTime());
                    $pacient->setTreatmentPlanValid(boolval($valid));

                    $em->persist($pacient);
                    $em->flush();

                    break;
                case 'comment':
                    $commentBody = $request->get('comment');
                    $files = $request->files->get('images');

                    $pacientComment = new PacientComment();
                    $pacientComment->setUid(Uuid::v4());
                    $pacientComment->setPacient($pacient);
                    $pacientComment->setAddedBy($this->getUser());
                    $pacientComment->setCommentBody($commentBody);
                    $pacientComment->setCreatedAt(new \DateTime());

                    $em->persist($pacientComment);
                    $em->flush();

                    foreach ($files as $file) {
                        if ($file instanceof UploadedFile) {
                            // Upload file
                            try {
                                $newFilename = $fileUploader->upload($file, sprintf('%s/%s', 'comments', $uuid));
                            } catch (FileException $e) {
                                continue;
                            }

                            $pacientCommentFile = new PacientCommentFile();
                            $pacientCommentFile->setPacientComment($pacientComment);
                            $pacientCommentFile->setFilename($newFilename);

                            // write changes to DB
                            $em->persist($pacientCommentFile);
                            $em->flush();
                        }
                    }

                    break;
                default:
                    break;
            }

            return $this->redirectToRoute('dashboard_pacient_medical_records', ['uuid' => $uuid]);
        }

        // set intervals
        $firstIntervalStart = (new \DateTime())->setTime(0, 0, 1);
        $firstIntervalEnd = (new \DateTime())->setTime(13, 59, 0);
        $secondIntervalStart = (new \DateTime())->setTime(14, 0, 0);
        $secondIntervalEnd = (new \DateTime())->setTime(18, 59, 0);
        $thirdIntervalStart = (new \DateTime())->setTime(19, 0, 0);
        $thirdIntervalEnd = (new \DateTime())->setTime(23, 59, 59);

        $pacientDiagnoses = $pacientDiagnosisRepository->findDiagnosesByPacient($pacient, false);
        $treatmentPlans = $pacientMedicationDetailsRepository->findTreatmentPlansByPacientForMedicalRecords($pacient->getId());

        $plans = [];
        foreach ($treatmentPlans as $plan) {
            if (!isset($plans[$plan['planId']])) {
                $plans[$plan['planId']] = [
                    'drug' => $plan['drug'],
                    'dose' => $plan['dose']
                ];
            }

            $treatmentDateFull = (new \DateTime())->setTime($plan['treatmentHour'], $plan['treatmentMinute'], 0);
            if ($treatmentDateFull >= $firstIntervalStart && $treatmentDateFull <= $firstIntervalEnd) {
                $plans[$plan['planId']]['d'] = !empty($plan['observations']) ? sprintf('x (%s)', $plan['observations']) : 'x';
            }
            if ($treatmentDateFull >= $secondIntervalStart && $treatmentDateFull <= $secondIntervalEnd) {
                $plans[$plan['planId']]['p'] = !empty($plan['observations']) ? sprintf('x (%s)', $plan['observations']) : 'x';
            }
            if ($treatmentDateFull >= $thirdIntervalStart && $treatmentDateFull <= $thirdIntervalEnd) {
                $plans[$plan['planId']]['s'] = !empty($plan['observations']) ? sprintf('x (%s)', $plan['observations']) : 'x';
            }
        }

        $firstDayOfCurrentMonth = (new \DateTime('first day of this month'))->setTime(0, 0, 1);
        $lastDayOfCurrentMonth = (new \DateTime('last day of this month'))->setTime(23, 59, 59);
        $currentMonthTreatmentPlans = $pacientMedicationDetailsRepository->findTreatmentPlansByPacientForMedicalRecords($pacient->getId(), $firstDayOfCurrentMonth, $lastDayOfCurrentMonth);
        $currentPlans = [];

        foreach ($currentMonthTreatmentPlans as $plan) {
            if ($plan['treatmentHour'] >= 0 && $plan['treatmentHour'] < 14) {
                $currentPlans[$plan['treatmentDay']]['d'][] = $plan['drug'];
            }
            if ($plan['treatmentHour'] >= 14 && $plan['treatmentHour'] < 19) {
                $currentPlans[$plan['treatmentDay']]['p'][] = $plan['drug'];
            }
            if ($plan['treatmentHour'] >= 19 && $plan['treatmentHour'] <= 23) {
                $currentPlans[$plan['treatmentDay']]['s'][] = $plan['drug'];
            }
        }

        $pacientComments = $pacientCommentRepository->findBy(['pacient' => $pacient], ['createdAt' => 'DESC']);

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Fisa medicala',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/medical_records.html.twig', [
            'pacient' => $pacient,
            'pacientDiagnoses' => $pacientDiagnoses,
            'plans' => $plans,
            'currentPlans' => $currentPlans,
            'lastDay' => (int)$lastDayOfCurrentMonth->format('d'),
            'comments' => $pacientComments,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("pacient/{uuid}/comment/{commentUuid}/delete", name="dashboard_pacient_comment_delete")
     */
    public function pacientDeleteComment(PacientRepository $pacientRepository, PacientCommentRepository $pacientCommentRepository, EntityManagerInterface $em, $uuid, $commentUuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        // find comment by UUID
        $comment = $pacientCommentRepository->findOneBy(['uid' => $commentUuid]);
        if (null !== $comment) {
            $em->remove($comment);
            $em->flush();
        }

        // redirect to medical records page page if pacient doesn't exist
        return $this->redirectToRoute('dashboard_pacient_medical_records', ['uuid' => $uuid]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/reminders", name="dashboard_pacient_reminders")
     */
    public function pacientReminders(PacientRepository $pacientRepository, SummaryNotificationRepository $summaryNotificationRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $summaryNotifications = $summaryNotificationRepository->findSummaryNotificationsByPacient($pacient);
        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Remindere',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/reminders.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'summaryNotifications' => $summaryNotifications,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/visits", name="dashboard_pacient_visits")
     */
    public function pacientVisits(PacientRepository $pacientRepository, PacientVisitCalendarRepository $pacientVisitCalendarRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $pacientVisits = $pacientVisitCalendarRepository->findVisitsByPacient($pacient);

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Vizite',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/visits.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'pacientVisits' => $pacientVisits,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/daily-monitoring/medical", name="dashboard_pacient_daily_monitoring_medical")
     */
    public function pacientDailyMonitoringMedical(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Monitorizare zilnica medicala',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/daily_monitoring_medical.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/discharge-data", name="dashboard_pacient_discharge_data")
     */
    public function pacientDischargeData(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Date externare',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/discharge_data.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/relations", name="dashboard_pacient_relations")
     */
    public function pacientRelations(PacientRepository $pacientRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Apartinatori',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/relations.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/room", name="dashboard_pacient_room")
     */
    public function pacientRoom(PacientRepository $pacientRepository, PacientRoomRepository $pacientRoomRepository, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $pacientRooms = $pacientRoomRepository->findBy(['pacient' => $pacient], ['createdAt' => 'DESC']);

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Camera',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/room.html.twig', [
            'pacient' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'rooms' => $pacientRooms,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * @Route("/dashboard/pacient/{uuid}/digital-record", name="dashboard_pacient_digital_record")
     */
    public function pacientDigitalRecord(PacientRepository $pacientRepository, PacientFileTypeRepository $pacientFileTypeRepository, PacientFileRepository $pacientFileRepository, PacientFileGroupRepository $pacientFileGroupRepository, EntityManagerInterface $em, $uuid): Response
    {
        // find pacient by UUID
        $pacient = $pacientRepository->findOneBy(['uid' => $uuid]);
        // redirect to listing page if pacient doesn't exist
        if (null === $pacient) {
            return $this->redirectToRoute('dashboard_pacients');
        }

        $pacientFileGroups = $pacientFileGroupRepository->findAll();
        $user = $this->getUser();
        $fileTypes = $pacientFileTypeRepository->findAll([], ['position' => 'ASC']);

        foreach ($fileTypes as $fileType) {
            $hasFile = $pacientFileRepository->findOneBy([
                'pacient' => $pacient,
                'fileType' => $fileType
            ]);

            if (null === $hasFile) {
                $pacientFile = new PacientFile();

                $pacientFile->setPacient($pacient);
                $pacientFile->setUid(Uuid::v4());
                $pacientFile->setUserResponsible($user);
                $pacientFile->setFileType($fileType);
                $pacientFile->setFileGroup($fileType->getFileGroup());
                $pacientFile->setCreatedAt(new \DateTime());
                $pacientFile->setFileName(sprintf('%s %s %s', $fileType->getName(), $pacient->getLastName(), $pacient->getFirstName()));
                $pacientFile->setStatus('In asteptare');
                $pacientFile->setViews(0);

                $em->persist($pacientFile);
                $em->flush();
            }
        }

        // add digital record view
        $pacientRecordView = new PacientRecordView();
        $pacientRecordView->setUser($this->getUser());
        $pacientRecordView->setPacient($pacient);
        $pacientRecordView->setCreatedAt(new \DateTime());

        $em->persist($pacientRecordView);
        $em->flush();

        $pacientFiles = $pacientFileRepository->findFilesByPacient($pacient);

        $breadcrumbs = [
            [
                'name' => 'Pacienti',
                'path' => 'dashboard_pacients',
                'params' => []
            ],
            [
                'name' => $pacient->getName(),
                'path' => 'dashboard_pacient_overview',
                'params' => ['uuid' => $pacient->getUid()]
            ],
            [
                'name' => 'Dosar digital',
                'path' => null,
                'params' => []
            ]
        ];

        return $this->render('dashboard/pacient/digital_record.html.twig', [
            'pacient' => $pacient,
            'user' => $pacient,
            'tabs' => self::PACIENT_TABS,
            'files' => $pacientFiles,
            'groups' => $pacientFileGroups,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    private function addToMedicationDetails(&$pacientsMedicationDetails, $id, $slot, $pacientMedicationDetail, $statusClasses)
    {
        $pacientsMedicationDetails[$id]['data'][$slot][] = [
            'id' => $pacientMedicationDetail['id'],
            'treatmentHour' => $pacientMedicationDetail['treatmentHour'],
            'drug' => $pacientMedicationDetail['drug'],
            'dose' => $pacientMedicationDetail['dose'],
            'observations' => $pacientMedicationDetail['observations'],
            'status' => $pacientMedicationDetail['status'],
            'statusClass' => isset($statusClasses[$pacientMedicationDetail['status']]) ? $statusClasses[$pacientMedicationDetail['status']] : ''
        ];
    }

    private function checkPacient($session, $em)
    {
        $userId = $session->get('userId');

        if (null === $userId) {
            return null;
        }

        $pacient = $em->find(Pacient::class, $userId);

        return $pacient;
    }
}

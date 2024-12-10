<?php

namespace App\Controller\Dashboard;

use App\Entity\NursingHome;
use App\Entity\Pacient;
use App\Entity\PacientFile;
use App\Entity\SummaryPacient;
use App\Entity\SummaryType;
use App\Entity\User;
use App\Repository\ActivityLogRepository;
use App\Repository\CookMenuRepository;
use App\Repository\NpsRepository;
use App\Repository\NursingHomeRepository;
use App\Repository\NursingHomeRoomRepository;
use App\Repository\PacientAdmissionRepository;
use App\Repository\PacientCookFoodRepository;
use App\Repository\PacientDischargeRepository;
use App\Repository\PacientFileRepository;
use App\Repository\PacientMonitoringMedicalRepository;
use App\Repository\PacientOrderlyDataRepository;
use App\Repository\PacientPhysicalDataRepository;
use App\Repository\PacientRepository;
use App\Repository\PacientSampleRepository;
use App\Repository\PacientVisitCalendarRepository;
use App\Repository\SummaryRepository;
use App\Repository\UserRelationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    /**
     * @Route("/dashboard/reports/diagnoses-treatments", name="diagnoses_treatments_report")
     */
    public function diagnosesTreatmentsReport(Request $request, PacientRepository $pacientRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Get request data
        $nursingHomeUid = $request->get('nursing-home', 0);
        $floor = $request->get('floor', false);
        $roomId = $request->get('room_id');
        $status = $request->get('status', Pacient::STATUS_ADMITTED);

        // Get filter data
        $floors = $nursingHomeRoomRepository->findFloorsList($user);
        $rooms = $nursingHomeRoomRepository->findRoomsList($user);
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        // Check nursing home
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        // Get data by filters
        $pacients = $pacientRepository->findPacientsDiagnosesTreatmentsForReport($user, $status, $floor, $roomId, $nursingHome);

        return $this->render('dashboard/report/diagnoses_treatments.html.twig', [
            'nursingHomes' => $nursingHomes,
            'pacients' => $pacients,
            'floors' => $floors,
            'rooms' => $rooms,
            'statuses' => Pacient::getPacientStatuses(),
            'selectedFloor' => $floor,
            'selectedRoomId' => $roomId,
            'selectedStatus' => $status,
            'selectedNursingHome' => $nursingHomeUid
        ]);
    }

    /**
     * @Route("/dashboard/reports/diagnoses-treatments/export", name="diagnoses_treatments_report_export")
     */
    public function diagnosesTreatmentsReportExport(PacientRepository $pacientRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $pacients = $pacientRepository->findPacientsDiagnosesTreatmentsForReport($user);

        $streamedResponse = new StreamedResponse();

        $streamedResponse->setCallback(function () use ($pacients) {
            $handle = fopen('php://output', 'w+');

            // BOM: Allow to display special characters with excel
            fwrite($handle, $bom = chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')));

            // Set header
            fputcsv($handle, array(
                'Nr crt',
                'Nume pacient',
                'Status',
                'Nr diagnostice',
                'Nr medicamente'
            ), ';', '"', '\\');

            $i = 1;
            foreach ($pacients as $pacient) {
                fputcsv($handle, array(
                    $i,
                    $pacient['name'],
                    $pacient['status'],
                    $pacient['diagnosesCount'],
                    $pacient['drugsCount'] > 0 ? sprintf('%s medicamente unice', $pacient['drugsCount']) : 0
                ), ';', '"', '\\');
                $i++;
            }
            fclose($handle);
        });

        $filename = sprintf('Export_diagnostice_si_tratamente_per_pacient_%s.csv', date('d_m_Y_H_i_s'));

        // Setting headers
        $streamedResponse->setStatusCode(Response::HTTP_OK);
        $streamedResponse->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $streamedResponse;
    }

    /**
     * @Route("/dashboard/reports/digital-records", name="digital_records_report")
     */
    public function digitalRecordsReport(Request $request, PacientRepository $pacientRepository, PacientFileRepository $pacientFileRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // get request data
        $floor = $request->get('floor', false);
        $roomId = $request->get('room_id', null);
        $status = $request->get('status', null);
        $nursingHomeUid = $request->get('nursing-home', 0);

        // Get initial data
        $floors = $nursingHomeRoomRepository->findFloorsList($user);
        $rooms = $nursingHomeRoomRepository->findRoomsList($user);
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        // Check nursing home
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $pacients = $pacientRepository->findPacientsByNursingHome($user, $status, $floor, $roomId, $nursingHome);

        $data = [];
        foreach ($pacients as $pacient) {
            $totalDocumentsCount = $pacientFileRepository->findFilesCountByPacientIdAndStatus($pacient['id']);
            $uploadedDocumentsCount = $pacientFileRepository->findFilesCountByPacientIdAndStatus($pacient['id'], PacientFile::STATUS_UPLOADED);
            $waitingDocumentsCount = $pacientFileRepository->findFilesCountByPacientIdAndStatus($pacient['id'], PacientFile::STATUS_WAITING);
            $progress = $totalDocumentsCount > 0 ? ceil($uploadedDocumentsCount * 100 / $totalDocumentsCount) : 0;

            $data[] = [
                'uuid' => $pacient['uid'],
                'cnp' => $pacient['cnp'],
                'name' => $pacient['name'],
                'status' => $pacient['status'],
                'floor' => $pacient['floor'],
                'roomNumber' => $pacient['roomNumber'],
                'totalDocumentsCount' => $totalDocumentsCount,
                'uploadedDocumentsCount' => $uploadedDocumentsCount,
                'waitingDocumentsCount' => $waitingDocumentsCount,
                'progress' => $progress
            ];
        }

        return $this->render('dashboard/report/digital_records.html.twig', [
            'data' => $data,
            'floors' => $floors,
            'rooms' => $rooms,
            'nursingHomes' => $nursingHomes,
            'statuses' => Pacient::getPacientStatuses(),
            'selectedFloor' => $floor,
            'selectedRoomId' => $roomId,
            'selectedStatus' => $status,
            'selectedNursingHome' => $nursingHomeUid
        ]);
    }

    /**
     * @Route("/dashboard/reports/digital-records/export", name="digital_records_report_export")
     */
    public function digitalRecordsReportExport(PacientRepository $pacientRepository, PacientFileRepository $pacientFileRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $pacients = $pacientRepository->findUserPacients($user);

        $data = [];
        foreach ($pacients as $pacient) {
            $totalDocumentsCount = $pacientFileRepository->findFilesCountByPacientIdAndStatus($pacient['id']);
            $uploadedDocumentsCount = $pacientFileRepository->findFilesCountByPacientIdAndStatus($pacient['id'], PacientFile::STATUS_UPLOADED);
            $waitingDocumentsCount = $pacientFileRepository->findFilesCountByPacientIdAndStatus($pacient['id'], PacientFile::STATUS_WAITING);
            $progress = $totalDocumentsCount > 0 ? ceil($uploadedDocumentsCount * 100 / $totalDocumentsCount) : 0;

            $data[] = [
                'name' => $pacient['name'],
                'status' => $pacient['status'],
                'totalDocumentsCount' => $totalDocumentsCount,
                'uploadedDocumentsCount' => $uploadedDocumentsCount,
                'waitingDocumentsCount' => $waitingDocumentsCount,
                'progress' => $progress
            ];
        }

        $streamedResponse = new StreamedResponse();

        $streamedResponse->setCallback(function () use ($data) {
            $handle = fopen('php://output', 'w+');

            // BOM: Allow to display special characters with excel
            fwrite($handle, $bom = chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')));

            // Set header
            fputcsv($handle, array(
                'Nr crt',
                'Nume pacient',
                'Status',
                'Documente total',
                'Documente incarcate',
                'Documente lipsa',
                'Progres'
            ), ';', '"', '\\');

            $i = 1;
            foreach ($data as $row) {
                fputcsv($handle, array(
                    $i,
                    $row['name'],
                    $row['status'],
                    $row['totalDocumentsCount'],
                    $row['uploadedDocumentsCount'],
                    $row['waitingDocumentsCount'],
                    $row['progress'] . '%'
                ), ';', '"', '\\');
                $i++;
            }
            fclose($handle);
        });

        $filename = sprintf('Export_dosare_digitale_per_pacient_%s.csv', date('d_m_Y_H_i_s'));

        // Setting headers
        $streamedResponse->setStatusCode(Response::HTTP_OK);
        $streamedResponse->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $streamedResponse;
    }

    /**
     * @Route("/dashboard/reports/activity-log", name="activity_log_report")
     */
    public function monthlyActivityLogReport(Request $request, ActivityLogRepository $activityLogRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Get request data
        $date = $request->get('date', (new \DateTime())->format('Y-m'));
        $nursingHomeUid = $request->get('nursing-home', 0);

        // Get nursing home by @uuid
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        // Get items by @filter
        $activeMonths = $activityLogRepository->findActiveMonths($user);

        // Get items by @filter
        $logs = $activityLogRepository->findMonthlyActivityLog($user, $date, $nursingHome);

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/report/activity_log.html.twig', [
            'logs' => $logs,
            'date' => $date,
            'nursingHomes' => $nursingHomes,
            'activeMonths' => $activeMonths,
            'selectedNursingHome' => $nursingHomeUid
        ]);
    }

    /**
     * @Route("/dashboard/reports/activity-log/daily", name="activity_log_report_daily")
     */
    public function dailyActivityLogReport(Request $request, ActivityLogRepository $activityLogRepository): Response
    {
        $nursingHome = $this->getUser()->getNursingHome();
        // get request data
        $date = $request->get('date', (new \DateTime())->format('Y-m-d'));
        $logs = $activityLogRepository->findDailyActivityLog($date, $nursingHome);

        return $this->render('dashboard/report/activity_log_daily.html.twig', [
            'logs' => $logs,
            'date' => $date
        ]);
    }

    /**
     * @Route("/dashboard/reports/relations", name="relations_report")
     */
    public function relationsReport(Request $request, NursingHomeRepository $nursingHomeRepository, UserRelationRepository $userRelationRepository, NpsRepository $npsRepository, PacientVisitCalendarRepository $pacientVisitCalendarRepository, PacientAdmissionRepository $pacientAdmissionRepository, PacientDischargeRepository $pacientDischargeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $nursingHomeUid = $request->get('nursing-home', 0);

        // Get nursing home by @uuid
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $relations = $userRelationRepository->findRelationsForReport($user, $nursingHome);

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        $badges = [
            '1' => 'badge-danger',
            '2' => 'badge-danger',
            '3' => 'badge-danger',
            '4' => 'badge-danger',
            '5' => 'badge-danger',
            '6' => 'badge-danger',
            '7' => 'badge-warning',
            '8' => 'badge-warning',
            '9' => 'badge-success',
            '10' => 'badge-success'
        ];

        foreach ($relations as $key => $relation) {
            $relations[$key]['npsNote'] = '';
            $relations[$key]['npsDate'] = '';
            $relations[$key]['npsFeedback'] = '';
            $relations[$key]['latestVisit'] = '';
            $relations[$key]['pacientData'] = '';

            $nps = $npsRepository->findNpsByRelationAndPacientUuids($relation['relationUuid'], $relation['pacientUuid']);
            if (null !== $nps) {
                $relations[$key]['npsNote'] = $nps->getNote();
                $relations[$key]['npsDate'] = $nps->getFormattedCreatedAt('d-m-Y');
                $relations[$key]['npsFeedback'] = $nps->getFeedback();
            }
            $latestVisit = $pacientVisitCalendarRepository->findLatestVisitByPacient($relation['pacientUuid']);
            if (null !== $latestVisit) {
                $relations[$key]['latestVisit'] = $latestVisit->getFormattedStartDate('U');
            }
            $pacientAdmissionData = $pacientAdmissionRepository->findPacientLatestAdmissionData($relation['pacientId']);
            $pacientDischargeData = $pacientDischargeRepository->findPacientLatestDischargeData($relation['pacientId']);

            $pacientData = [
                'admissionDate' => empty($pacientAdmissionData) ? 'N/A' : $pacientAdmissionData['admissionDateFormatted'],
                'dischargeDate' => empty($pacientDischargeData) ? 'N/A' : $pacientDischargeData['dischargeDateFormatted'],
                'dischargeReason' => empty($pacientDischargeData) ? 'N/A' : $pacientDischargeData['dischargeReason']
            ];
            $relations[$key]['pacientData'] = $pacientData;
        }

        array_multisort(array_column($relations, 'latestVisit'), SORT_DESC, $relations);

        return $this->render('dashboard/report/relations.html.twig', [
            'relations' => $relations,
            'nursingHomes' => $nursingHomes,
            'badges' => $badges,
            'selectedNursingHome' => $nursingHomeUid
        ]);
    }

    /**
     * @Route("/dashboard/reports/samples", name="samples_report")
     */
    public function samplesReport(Request $request, PacientSampleRepository $pacientSampleRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $nursingHomeUid = $request->get('nursing-home', 0);
        $date = $request->get('date');

        /**
         * Get nursing home by @uuid
         * @var NursingHome $nursingHome
         */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $samples = $pacientSampleRepository->findSamples($user, $date, $nursingHome);

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/report/samples_report.html.twig', [
            'samples' => $samples,
            'nursingHomes' => $nursingHomes,
            'date' => $date,
            'selectedNursingHome' => $nursingHomeUid
        ]);
    }

    /**
     * @Route("/dashboard/reports/assistance-medical", name="assistance_medical_report")
     */
    public function assistanceMedicalReport(Request $request, UserRepository $userRepository, SummaryRepository $summaryRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $month = $request->get('date', date('m-Y'));

        $medicalAssistants = $userRepository->findUsersByRole($user, 'ROLE_ASSISTANCE_MEDICAL');

        $data = [];
        foreach ($medicalAssistants as $medicalAssistant) {
            $totalSummaries = $summaryRepository->findSummaryDataForReport($medicalAssistant['id'], SummaryType::SUMMARY_TYPE_ASSISTANCE_MEDICAL, $month);
            $processingSummaries = $summaryRepository->findSummaryDataForReport($medicalAssistant['id'], SummaryType::SUMMARY_TYPE_ASSISTANCE_MEDICAL, $month, SummaryPacient::STATUS_IN_PROGRESS);
            $completedSummaries = $summaryRepository->findSummaryDataForReport($medicalAssistant['id'], SummaryType::SUMMARY_TYPE_ASSISTANCE_MEDICAL, $month, SummaryPacient::STATUS_COMPLETED);
            $tempData = [];
            foreach ($totalSummaries as $summary) {
                $tempData[(int)$summary['day']]['total'] = $summary['pacientsCount'];
            }
            foreach ($processingSummaries as $summary) {
                $tempData[(int)$summary['day']]['processing'] = $summary['pacientsCount'];
            }
            foreach ($completedSummaries as $summary) {
                $tempData[(int)$summary['day']]['completed'] = $summary['pacientsCount'];
            }
            $data[] = [
                'name' => $medicalAssistant['name'],
                'data' => $tempData
            ];
        }

        return $this->render('dashboard/report/assistance_medical_report.html.twig', [
            'data' => $data,
            'month' => $month,
            'startDay' => 1,
            'endDay' => 31
        ]);
    }

    /**
     * @Route("/dashboard/reports/medical-monitoring", name="medical_monitoring_report")
     */
    public function medicalMonitoringReport(Request $request, PacientRepository $pacientRepository, NursingHomeRepository $nursingHomeRepository, PacientMonitoringMedicalRepository $pacientMonitoringMedicalRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $month = $request->get('date', date('m-Y'));
        $pacientUuid = $request->get('uuid');
        $nursingHomeUid = $request->get('nursing-home', 0);

        /**
         * Get nursing home by @uuid
         * @var NursingHome $nursingHome
         */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $pacients = $pacientRepository->findPacientsByNursingHome($user, null, false, null, $nursingHome);

        $data = [];

        $monitoringMapping = [
            "temperature" => "Temperatura",
            "saturation" => "Saturatie",
            "glucose" => "Glicemie",
            "infusion" => "Perfuzabile",
            "heartRate" => "Puls",
            "systolicBloodPressure" => "Tensiune sistolica",
            "diastolicBloodPressure" => "Tensiune diastolica"
        ];

        if (!empty($pacientUuid)) {
            $pacient = $pacientRepository->findOneBy(['uid' => $pacientUuid]);
            if (null !== $pacient) {
                $monitoringData = $pacientMonitoringMedicalRepository->findMonitoringMedicalDataByPacientAndMonth($pacient, $month);

                foreach ($monitoringMapping as $monitoringKey => $monitoringName) {
                    $data[$monitoringKey]['name'] = $monitoringName;
                    $data[$monitoringKey]['days'] = [];
                    $data[$monitoringKey]['fullName'] = '';
                    foreach ($monitoringData as $monitoring) {
                        if ($monitoring[$monitoringKey]) {
                            $data[$monitoringKey]['days'][$monitoring['day']][] = sprintf('%s - %s', $monitoring['hour'], $monitoring[$monitoringKey]);
                            $data[$monitoringKey]['fullName'] = $monitoring['lastName'] . ' ' . $monitoring['firstName'];
                        }
                    }
                }
            }
        }

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/report/medical_monitoring_report.html.twig', [
            'data' => $data,
            'pacients' => $pacients,
            'nursingHomes' => $nursingHomes,
            'pacientUuid' => $pacientUuid,
            'month' => $month,
            'selectedNursingHome' => $nursingHomeUid,
            'startDay' => 1,
            'endDay' => 31
        ]);
    }

    /**
     * @Route("/dashboard/reports/physical-therapy", name="physical_therapy_report")
     */
    public function physicalTherapyReport(Request $request, NursingHomeRepository $nursingHomeRepository, PacientRepository $pacientRepository, PacientPhysicalDataRepository $pacientPhysicalDataRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = [];
        $month = $request->get('date', date('m-Y'));
        $nursingHomeUid = $request->get('nursing-home', 0);
        $pacientUuid = $request->get('uuid');

        /**
         * Get nursing home by @uuid
         * @var NursingHome $nursingHome
         */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $pacients = $pacientRepository->findPacientsByNursingHome($user, null, false, null, $nursingHome);

        $procedures = [
            "massage" => "Masaj",
            "therapeuticMassage" => "Masaj terapeutic",
            "tappingMassage" => "Tapotaj",
            "bodyRepositioningImmobilizedPatients" => "Reposturare corporala pacienti imobilizati",
            "correctingBodyPostureAndAlignment" => "Corectarea posturii si aliniamentului corpului",
            "increasingBodyCoordinationAndBalance" => "Cresterea coordonarii si echilibrului corpului",
            "increasingJointMobility" => "Cresterea mobilitatii articulare",
            "increasingJointMobilityPassive" => "Cresterea mobilitatii articulare - mobilizari pasive",
            "increasingJointMobilityPassiveActive" => "Cresterea mobilitatii articulare - mobilizari pasiv-active",
            "increasingJointMobilityActiveVoluntary" => "Cresterea mobilitatii articulare - mobilizari active voluntare",
            "increasingJointMobilityAutoPassive" => "Cresterea mobilitatii articulare - mobilizari autopasive",
            "increasingMuscleStrengthAndEndurance" => "Cresterea fortei si a rezistentei musculare",
            "scriptotherapy" => "Scripetoterapie",
            "rocherCage" => "Scripetoterapie - cusca Rocher",
            "multifunctionalDevice" => "Scripetoterapie - aparatul multifunctional",
            "stretching" => "Intinderi (stretching)",
            "walkingExercises" => "Exercitii de mers",
            "walkingExercisesSteps" => "Exercitii de mers - mersul pe trepte",
            "walkingExercisesSupport" => "Exercitii de mers - mersul cu mijloace de sustinere",
            "walkingExercisesBicycle" => "Exercitii de mers - pedalatul la bicicleta",
            "walkingExercisesWalkingLane" => "Exercitii de mers - banda de mers",
            "groupExercises" => "Exercitii de grup",
            "groupExercisesJointMobility" => "Exercitii de grup - pentru cresterea mobilitatii articulare",
            "groupExercisesBodyBalanceAndCoordination" => "Exercitii de grup - pentru cresterea echilibrului si coordonarii corpului",
            "groupExercisesResistanceAndMuscleStrength" => "Exercitii de grup - pentru cresterea rezistentei si fortei musculare",
            "trellisExercises" => "Exercitii la spalier",
            "refusal" => "Refuz",
            "medicalProblem" => "Probleme medicale",
            "observations" => "Observații"
        ];

        if (!empty($pacientUuid)) {
            $pacient = $pacientRepository->findOneBy(['uid' => $pacientUuid]);
            if (null !== $pacient) {
                $physicalData = $pacientPhysicalDataRepository->findPhysicalDataByPacientAndMonth($pacient, $month);
                foreach ($procedures as $procedureKey => $procedureName) {
                    $data[$procedureKey]['name'] = $procedureName;
                    $data[$procedureKey]['days'] = [];
                    $data[$procedureKey]['observation'] = [];
                    foreach ($physicalData as $procedure) {
                        if ($procedure[$procedureKey]) {
                            $data[$procedureKey]['days'][] = $procedure['day'];
                        }
                        if ($procedure['observations']) {
                            $data[$procedureKey]['observation'][] = ['value' => $procedure['observations'], 'date' => $procedure['createdAt']];
                        }
                    }
                }
            }
        }

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/report/physical_therapy_report.html.twig', [
            'data' => $data,
            'pacients' => $pacients,
            'nursingHomes' => $nursingHomes,
            'month' => $month,
            'pacientUuid' => $pacientUuid,
            'selectedNursingHome' => $nursingHomeUid,
            'startDay' => 1,
            'endDay' => 31
        ]);
    }

    /**
     * @Route("/dashboard/reports/orderly", name="orderly_report")
     */
    public function orderlyReport(Request $request, PacientRepository $pacientRepository, NursingHomeRepository $nursingHomeRepository, PacientOrderlyDataRepository $pacientOrderlyDataRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = [];
        $month = $request->get('date', date('m-Y'));
        $nursingHomeUid = $request->get('nursing-home', 0);
        $pacientUuid = $request->get('uuid');

        /**
         * Get nursing home by @uuid
         * @var NursingHome $nursingHome
         */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $pacients = $pacientRepository->findPacientsByNursingHome($user, null, false, null, $nursingHome);

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        $actions = [
            'hydrationFood' => 'Hidratare/masa',
            'diuresis' => 'Diureza',
            'stool' => 'Scaun',
            'bathing' => 'Baie',
            'diapers' => 'Pampers'
        ];

        if (!empty($pacientUuid)) {
            $pacient = $pacientRepository->findOneBy(['uid' => $pacientUuid]);
            if (null !== $pacient) {
                $orderlyData = $pacientOrderlyDataRepository->findOrderlyDataByPacientAndMonth($pacient, $month);
                foreach ($actions as $actionKey => $actionName) {
                    $data[$actionKey]['name'] = $actionName;
                    $data[$actionKey]['days'] = [];
                    $data[$actionKey]['hours'] = [];
                    foreach ($orderlyData as $action) {
                        if ($action[$actionKey]) {
                            $data[$actionKey]['days'][] = $action['day'];
                            $data[$actionKey]['hours'][] = $action['hour'];
                        }
                    }
                }
            }
        }

        return $this->render('dashboard/report/orderly_report.html.twig', [
            'data' => $data,
            'pacients' => $pacients,
            'nursingHomes' => $nursingHomes,
            'month' => $month,
            'pacientUuid' => $pacientUuid,
            'selectedNursingHome' => $nursingHomeUid,
            'startDay' => 1,
            'endDay' => 31
        ]);
    }

    /**
     * @Route("/dashboard/reports/cook/menus", name="cook_menus_report")
     */
    public function cookMenusReport(Request $request, CookMenuRepository $cookMenuRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = [];
        $startDate = $request->get('startDate', (new \DateTime('last Monday'))->format('Y-m-d'));
        $endDate = $request->get('endDate', (new \DateTime('next Sunday'))->format('Y-m-d'));
        $nursingHomeUid = $request->get('nursing-home', 0);

        /**
         * Get nursing home by @uuid
         * @var NursingHome $nursingHome
         */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $cookMenus = $cookMenuRepository->findCookMenusByInterval($user, $startDate, $endDate, $nursingHome);

        foreach ($cookMenus as $menuKey => $menu) {
            $data[$menuKey] = [
                'name' => $menu->getName(),
                'startDate' => $menu->getStartDate()->format('d-m-Y'),
                'endDate' => $menu->getEndDate()->format('d-m-Y'),
                'observations' => $menu->getObservations()
            ];
            $items = $menu->getCookMenuItems();
            foreach ($items as $item) {
                $day = $item->getDay();
                $data[$menuKey]['items']['breakfast'][$day] = $item->getBreakfast();
                $data[$menuKey]['items']['firstSnack'][$day] = $item->getFirstSnack();
                $data[$menuKey]['items']['lunch'][$day] = $item->getLunch();
                $data[$menuKey]['items']['secondSnack'][$day] = $item->getSecondSnack();
                $data[$menuKey]['items']['dinner'][$day] = $item->getDinner();
                $data[$menuKey]['items']['dz'][$day] = $item->getDz();
            }
        }

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/report/cook_menus_report.html.twig', [
            'data' => $data,
            'nursingHomes' => $nursingHomes,
            'selectedNursingHome' => $nursingHomeUid,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * @Route("/dashboard/reports/cook/food", name="cook_food_report")
     */
    public function cookFoodReport(Request $request, PacientCookFoodRepository $pacientCookFoodRepository, NursingHomeRepository $nursingHomeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $date = $request->get('date', date('Y-m-d'));
        $nursingHomeUid = $request->get('nursing-home', 0);

        /**
         * Get nursing home by @uuid
         * @var NursingHome $nursingHome
         */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $nursingHomeUid]);

        $cookFoodItems = $pacientCookFoodRepository->findCookFoodItemsByDate($user, $date, $nursingHome);
        $data = [];

        foreach ($cookFoodItems as $cookFoodItem) {
            $pacient = $cookFoodItem->getPacient();
            if (null === $nursingHomeRoom = $pacient->getNursingHomeRoom()) {
                continue;
            }

            $floor = $nursingHomeRoom->getFloor();
            $foodOption = $cookFoodItem->getFoodOption();
            $observations = $cookFoodItem->getObservations();
            $data[$floor][$foodOption][] = !empty($observations) ? sprintf('%s (%s)', $pacient->getName(), $observations) : $pacient->getName();
        }

        // Get items by @user
        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/report/cook_food_report.html.twig', [
            'data' => $data,
            'date' => $date,
            'nursingHomes' => $nursingHomes,
            'selectedNursingHome' => $nursingHomeUid
        ]);
    }
}

<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\PacientFile;
use App\Repository\PacientAdmissionRepository;

class PacientAdmissionExtension extends AbstractExtension {

    protected $pacientAdmissionRepository;

    public function __construct(PacientAdmissionRepository $pacientAdmissionRepository) {
        $this->pacientAdmissionRepository = $pacientAdmissionRepository;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions() {
        return array(
            new TwigFunction(
                    'pacient_admissions_count', array($this, 'pacientAdmissionsCount'), array('is_safe' => array('html'))
            ),
        );
    }

    public function pacientAdmissionsCount() {
        $pacientAdmissions = $this->pacientAdmissionRepository->findPacientAdmissions();

        $count = 0;
        foreach ($pacientAdmissions as $pacientAdmission) {
            $pacient = $pacientAdmission->getPacient();

            $pacientClinicalExamsCount = $pacient->getPacientClinicalExams()->count();
            $pacientMedicalDataCount = $pacient->getPacientMedicalData()->count();
            $pacientCompletedAdmissionFilesCount = $pacient->getPacientFiles()->filter(function(PacientFile $pacientFile) {
                        return $pacientFile->getStatus() == PacientFile::STATUS_UPLOADED && $pacientFile->getFileType() !== null && $pacientFile->getFileType()->isRequiredOnAdmission() == true;
                    })->count();

            if ($pacientClinicalExamsCount > 0 && $pacientMedicalDataCount > 0 && $pacientCompletedAdmissionFilesCount == 6) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'pacient_admission';
    }

}

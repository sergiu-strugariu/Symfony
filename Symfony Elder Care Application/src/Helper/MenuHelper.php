<?php

namespace App\Helper;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Document;

/**
 * Menu Helper
 *
 * Utility class to handle menu configuration.
 */
class MenuHelper {

    const ROLES = [
        'ROLE_OWNER' => 'Owner',
        'ROLE_MANAGEMENT' => 'Manager',
        'ROLE_MEDIC' => 'Medic',
        'ROLE_ASSISTANCE_MEDICAL' => 'Asistent medical',
        'ROLE_ASSISTANCE_MEDICAL_PHARMACY' => 'Asistent medical farmacie',
        'ROLE_PHYSICAL_THERAPY' => 'Kinetoterapie',
        'ROLE_ORDERLY' => 'Infirmier',
        'ROLE_SOCIAL_WORKER' => 'Asistenta sociala',
        'ROLE_COOK' => 'Bucatarie',
        'ROLE_RELATION' => 'Apartinator',
        'ROLE_PSYCHOTHERAPY' => 'Psihoterapie',
        'ROLE_CLEANING' => 'Mentenanta',
        'ROLE_RECEPTION' => 'Receptie'
    ];

    public $security;
    public $em;

    public function __construct(Security $security, EntityManagerInterface $em) {
        $this->security = $security;
        $this->em = $em;
    }

    public function getUserRoles() {
        $roles = self::ROLES;
        if ($this->security->isGranted('ROLE_MANAGEMENT')) {
            return [
                'ROLE_MANAGEMENT' => 'Manager',
                'ROLE_MEDIC' => 'Medic',
                'ROLE_ASSISTANCE_MEDICAL' => 'Asistent medical',
                'ROLE_ASSISTANCE_MEDICAL_PHARMACY' => 'Asistent medical farmacie',
                'ROLE_PHYSICAL_THERAPY' => 'Kinetoterapie',
                'ROLE_ORDERLY' => 'Infirmier',
                'ROLE_SOCIAL_WORKER' => 'Asistenta sociala',
                'ROLE_COOK' => 'Bucatarie',
                'ROLE_RELATION' => 'Apartinator',
                'ROLE_PSYCHOTHERAPY' => 'Psihoterapie',
                'ROLE_CLEANING' => 'Mentenanta',
                'ROLE_RECEPTION' => 'Receptie'
            ];
        }

        return $roles;
    }
    
    public function getAvailableDocuments() {
        $documents = $this->em->getRepository(Document::class)->findAvailableDocuments();
        $availableDocuments = [];
        
        foreach ($documents as $document) {
            $availableDocuments[$document['documentType']][] = [
                'name' => $document['name'],
                'slug' => $document['slug']
            ];
        }
        
        return $availableDocuments;
    }
}

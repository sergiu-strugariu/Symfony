<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Favorite;
use App\Entity\Setting;
use App\Helper\DefaultHelper;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingService extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    public function __construct(EntityManagerInterface $em, DefaultHelper $helper)
    {
        $this->em = $em;
        $this->helper = $helper;
    }

    /**
     * @param $value
     * @return Setting|mixed|object|null
     */
    public function getService($value)
    {
        $service = $this->em->getRepository(Setting::class)->findOneBy(['settingName' => $value]);

        return $service ? $service->getSettingValue() : null;
    }
}
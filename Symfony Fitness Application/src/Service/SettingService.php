<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Setting;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingService extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var LanguageHelper
     */
    protected LanguageHelper $languageHelper;

    public function __construct(EntityManagerInterface $em, LanguageHelper $languageHelper)
    {
        $this->em = $em;
        $this->languageHelper = $languageHelper;
    }

    /**
     * @param $value
     * @return Setting|mixed|object|null
     */
    public function getService($value): mixed
    {
        $service = $this->em->getRepository(Setting::class)->findOneBy(['settingName' => $value]);

        return $service?->getSettingValue();
    }

    /**
     * @param $limit
     * @param $locale
     * @return array|null
     */
    public function getArticles($locale, $limit): ?array
    {
        $articles = $this->em->getRepository(Article::class)->getArticles($this->languageHelper->getLanguageByLocale($locale), null, $limit);

        return $articles ?? null;
    }
}
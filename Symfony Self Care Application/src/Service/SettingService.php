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
     * @var LanguageHelper
     */
    protected LanguageHelper $languageHelper;

    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    public function __construct(EntityManagerInterface $em, LanguageHelper $languageHelper, DefaultHelper $helper)
    {
        $this->em = $em;
        $this->languageHelper = $languageHelper;
        $this->helper = $helper;
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
     * @return array|null
     */
    public function getArticles($limit): ?array
    {
        $articles = $this->em->getRepository(Article::class)->getArticles(null, $limit);
        dd($articles);


        return $articles ?? null;
    }

    /**
     * @param $time
     * @return string
     * @throws Exception
     */
    public function getTimeAgo($time): string
    {
        return $this->helper->getTimeAgo($time);
    }

    /**
     * @param $type
     * @param $entityId
     * @return bool
     */
    public function isFavorite($type, $entityId): bool
    {
        /** @var Favorite $favorite */
        $favorite = $this->em->getRepository(Favorite::class)->findOneBy([
            'user' => $this->getUser(),
            'entityId' => $entityId,
            'type' => $type
        ]);

        return empty($favorite);
    }
}
<?php

namespace App\Helper;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class LanguageHelper
{
    /**
     * @param LanguageRepository $languageRepository
     * @param $defaultLocale
     */
    public function __construct(protected LanguageRepository $languageRepository, protected $defaultLocale)
    {

    }

    /**
     * @return Language[]
     */
    public function getAllLanguage(): array
    {
        return $this->languageRepository->findBy(['deletedAt' => null]);
    }

    /**
     * @return Language|null
     */
    public function getDefaultLanguage(): ?Language
    {
        return $this->getLanguageByLocale($this->defaultLocale);
    }

    /**
     * @param $locale
     * @return Language|null
     */
    public function getLanguageByLocale($locale): ?Language
    {
        return $this->languageRepository->findOneBy(['locale' => $locale]);
    }
}
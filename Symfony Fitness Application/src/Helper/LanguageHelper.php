<?php

namespace App\Helper;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LanguageHelper
{
    public function __construct(
            protected EntityManagerInterface $em,
            protected LanguageRepository $languageRepository,
            protected $defaultLocale,
            protected RequestStack $requestStack
    ) 
    {

    }
    
    public function getDefaultLanguage()
    {
        return $this->getLanguageByLocale($this->defaultLocale);
    }

    public function getLocaleFromRequest()
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    public function getLanguageFromLocaleRequest()
    {
        return $this->getLanguageByLocale($this->requestStack->getCurrentRequest()->getLocale());
    }
    
    public function getLanguageByLocale($locale)
    {
        return $this->languageRepository->findOneBy(['locale' => $locale]);
    }

    public function getAllLanguages(): array
    {
        return $this->em->getRepository(Language::class)->findActiveLanguages();
    }
}
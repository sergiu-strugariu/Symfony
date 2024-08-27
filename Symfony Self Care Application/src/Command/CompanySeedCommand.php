<?php

namespace App\Command;

use App\Entity\CategoryCare;
use App\Entity\CategoryCareTranslation;
use App\Entity\CategoryService;
use App\Entity\CategoryServiceTranslation;
use App\Entity\CompanyReview;
use App\Entity\County;
use App\Entity\Company;
use App\Entity\User;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class CompanySeedCommand extends Command
{
    const CARE_CATEGORIES = [
        'Elderly Care',
        'Child Care',
        'Special Needs Care',
        'Medical Care',
        'Rehabilitation Care'
    ];


    const SERVICE_CATEGORIES = [
        'Cleaning Services',
        'Catering Services',
        'Maintenance Services',
        'Transport Services',
        'Security Services'
    ];


    const COMPANIES = [
        [
            'title' => 'Care Plus',
            'email' => 'contact@careplus.ro',
            'phone' => '+40721234567',
            'address' => 'Str. Îngrijirii Nr. 1, București',
            'postalCode' => '010101',
            'locationType' => Company::LOCATION_TYPE_CARE,
            'price' => '2000 RON',
            'website' => 'https://www.careplus.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '95',
            'services' => ['Elderly Care', 'Medical Care'],
            'body' => 'Providing comprehensive elderly and medical care services.',
            'shortDescription' => 'Providing comprehensive elderly and medical care services.',
            'category' => 'Elderly Care',
            'county' => 'București'
        ],
        [
            'title' => 'Clean Home',
            'email' => 'info@cleanhome.ro',
            'phone' => '+40759876543',
            'address' => 'Str. Curățeniei Nr. 5, Cluj-Napoca',
            'postalCode' => '400123',
            'locationType' => Company::LOCATION_TYPE_CARE,
            'price' => '1500 RON',
            'website' => 'https://www.cleanhome.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '85',
            'services' => ['Cleaning Services', 'Maintenance Services'],
            'body' => 'Offering top-notch cleaning and maintenance services.',
            'shortDescription' => 'Offering top-notch cleaning and maintenance services.',
            'category' => 'Child Care',
            'county' => 'Cluj'
        ],
        [
            'title' => 'HealthCare Solutions',
            'email' => 'support@healthcare.ro',
            'phone' => '+40761234567',
            'address' => 'Str. Sănătății Nr. 3, Iași',
            'postalCode' => '700234',
            'locationType' => Company::LOCATION_TYPE_CARE,
            'price' => '2500 RON',
            'website' => 'https://www.healthcare.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '35',
            'services' => ['Medical Care', 'Rehabilitation Care'],
            'body' => 'Specialized in medical and rehabilitation care.',
            'shortDescription' => 'Specialized in medical and rehabilitation care.',
            'category' => 'Special Needs Care',
            'county' => 'Iași'
        ],
        [
            'title' => 'Family Care',
            'email' => 'info@familycare.ro',
            'phone' => '+40751239876',
            'address' => 'Str. Familiei Nr. 6, Constanța',
            'postalCode' => '900345',
            'locationType' => Company::LOCATION_TYPE_CARE,
            'price' => '1800 RON',
            'website' => 'https://www.familycare.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '55',
            'services' => ['Child Care', 'Special Needs Care'],
            'body' => 'Providing comprehensive care for children and special needs individuals.',
            'shortDescription' => 'Providing comprehensive care for children and special needs individuals.',
            'category' => 'Medical Care',
            'county' => 'Constanța'
        ],
        [
            'title' => 'SeniorCare',
            'email' => 'help@seniorcare.ro',
            'phone' => '+40721234568',
            'address' => 'Str. Seniorilor Nr. 9, București',
            'postalCode' => '010203',
            'locationType' => Company::LOCATION_TYPE_CARE,
            'price' => '2200 RON',
            'website' => 'https://www.seniorcare.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '35',
            'services' => ['Elderly Care', 'Medical Care'],
            'body' => 'Quality care services for seniors.',
            'shortDescription' => 'Quality care services for seniors.',
            'category' => 'Rehabilitation Care',
            'county' => 'București'
        ],
        [
            'title' => 'Safe Transport',
            'email' => 'booking@safetransport.ro',
            'phone' => '+40734123456',
            'address' => 'Str. Transporturilor Nr. 2, Timișoara',
            'postalCode' => '300567',
            'locationType' => Company::LOCATION_TYPE_PROVIDER,
            'price' => '1000 RON',
            'website' => 'https://www.safetransport.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '75',
            'services' => ['Transport Services', 'Security Services'],
            'body' => 'Reliable transport and security services.',
            'shortDescription' => 'Reliable transport and security services.',
            'category' => 'Transport Services',
            'county' => 'Timiș'
        ],
        [
            'title' => 'Best Catering',
            'email' => 'order@bestcatering.ro',
            'phone' => '+40786543210',
            'address' => 'Str. Gustului Nr. 7, Brașov',
            'postalCode' => '500678',
            'locationType' => Company::LOCATION_TYPE_PROVIDER,
            'price' => '1200 RON',
            'website' => 'https://www.bestcatering.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '45',
            'services' => ['Catering Services', 'Cleaning Services'],
            'body' => 'Delicious catering and impeccable cleaning services.',
            'shortDescription' => 'Delicious catering and impeccable cleaning services.',
            'category' => 'Catering Services',
            'county' => 'Brașov'
        ],
        [
            'title' => 'Office Maintenance',
            'email' => 'contact@officemaintenance.ro',
            'phone' => '+40798765432',
            'address' => 'Str. Biroului Nr. 10, Cluj-Napoca',
            'postalCode' => '400456',
            'locationType' => Company::LOCATION_TYPE_PROVIDER,
            'price' => '1300 RON',
            'website' => 'https://www.officemaintenance.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '65',
            'services' => ['Maintenance Services', 'Cleaning Services'],
            'body' => 'Maintaining office spaces with expert care.',
            'shortDescription' => 'Maintaining office spaces with expert care.',
            'category' => 'Maintenance Services',
            'county' => 'Cluj'
        ],
        [
            'title' => 'ChildCare Services',
            'email' => 'info@childcare.ro',
            'phone' => '+40761234569',
            'address' => 'Str. Copiilor Nr. 4, Iași',
            'postalCode' => '700123',
            'locationType' => Company::LOCATION_TYPE_PROVIDER,
            'price' => '1900 RON',
            'website' => 'https://www.childcare.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '55',
            'services' => ['Child Care', 'Special Needs Care'],
            'body' => 'Professional care services for children.',
            'shortDescription' => 'Professional care services for children.',
            'category' => 'Cleaning Services',
            'county' => 'Iași'
        ],
        [
            'title' => 'Secure Home',
            'email' => 'support@securehome.ro',
            'phone' => '+40734123457',
            'address' => 'Str. Siguranței Nr. 11, Timișoara',
            'postalCode' => '300789',
            'locationType' => Company::LOCATION_TYPE_PROVIDER,
            'price' => '1100 RON',
            'website' => 'https://www.securehome.ro',
            'videoUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?si=2pX2YWxzTpbGTeU1',
            'admissionCriteria' => '45',
            'services' => ['Security Services', 'Maintenance Services'],
            'body' => 'Ensuring your home is safe and well-maintained.',
            'shortDescription' => 'Ensuring your home is safe and well-maintained.',
            'category' => 'Security Services',
            'county' => 'Timiș'
        ]
    ];


    /**
     * @var string
     */
    protected static $defaultName = 'app:company-seed';

    /**
     * @var LanguageHelper
     */
    protected LanguageHelper $languageHelper;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @param LanguageHelper $languageHelper
     * @param EntityManagerInterface $em
     */
    public function __construct(LanguageHelper $languageHelper, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->languageHelper = $languageHelper;
        $this->em = $em;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Push category and courses');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $i = 0;
        $io = new SymfonyStyle($input, $output);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'seniorhelp@gmail.com']);
        $slugger = new AsciiSlugger();

        // All categories
        $careCategories = self::CARE_CATEGORIES;
        $serviceCategories = self::SERVICE_CATEGORIES;

        $companies = self::COMPANIES;

        $categoryEntities = [];

        // Parse care categories
        foreach ($careCategories as $val) {
            $slug = $slugger->slug($val)->lower();

            // Check exist category
            $getCategory = $this->em->getRepository(CategoryCare::class)->findOneBy(['slug' => $slug]);

            if ($getCategory) {
                $io->error('This category exist: ' . $val);
                $categoryEntities[$val] = $getCategory;
                continue;
            }

            // Create new category
            $category = new CategoryCare();
            $category->setUuid(Uuid::v4());
            $category->setSlug($slug);
            $category->setStatus(CategoryCare::STATUS_PUBLISHED);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create category translations
                $categoryTrans = new CategoryCareTranslation();
                $categoryTrans->setTitle($val);
                $categoryTrans->setCategoryCare($category);
                $categoryTrans->setLanguage($language);

                // Persist and save
                $this->em->persist($categoryTrans);
            }

            // Persist and save
            $this->em->persist($category);
            $this->em->flush();

            $categoryEntities[$val] = $category;

            $io->success('Category: ' . $val);
        }

        // Parse service categories
        foreach ($serviceCategories as $val) {
            $slug = $slugger->slug($val)->lower();

            // Check exist category
            $getCategory = $this->em->getRepository(CategoryService::class)->findOneBy(['slug' => $slug]);

            if ($getCategory) {
                $io->error('This category exist: ' . $val);
                $categoryEntities[$val] = $getCategory;
                continue;
            }

            // Create new category
            $category = new CategoryService();
            $category->setUuid(Uuid::v4());
            $category->setSlug($slug);
            $category->setStatus(CategoryService::STATUS_PUBLISHED);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create category translations
                $categoryTrans = new CategoryServiceTranslation();
                $categoryTrans->setTitle($val);
                $categoryTrans->setCategoryService($category);
                $categoryTrans->setLanguage($language);

                // Persist and save
                $this->em->persist($categoryTrans);
            }

            // Persist and save
            $this->em->persist($category);
            $this->em->flush();

            $categoryEntities[$val] = $category;

            $io->success('Category: ' . $val);
        }

        // Parse companies
        foreach ($companies as $item) {
            $i++;
            $slug = $slugger->slug($item['title'])->lower();

            // Check exist job
            $getCompany = $this->em->getRepository(Company::class)->findOneBy(['slug' => $slug]);
            $getCounty = $this->em->getRepository(County::class)->findOneBy(['name' => $item['county']]);

            if ($getCompany && $getCounty) {
                if ($getCompany->getLocationType() === Company::LOCATION_TYPE_CARE) {
                    // Parse and save
                    $getCompany->updateCompanyReviewsRating();
                    $this->em->persist($getCompany);
                    $this->em->flush();

                    $io->success('Average rating for: ' . $getCompany->getName());
                }

                $io->error('This job exist: ' . $item['title']);
                continue;
            }

            $company = new Company();
            $company->setUser($user);
            $company->setCounty($getCounty);
            $company->setCity($getCounty->getCities()->first());
            $company->setUuid(Uuid::v4());
            $company->setName($item['title']);
            $company->setLogo($item['locationType'] === Company::LOCATION_TYPE_CARE ? 'care-logo.png' : 'provider-logo.png');
            $company->setSlug($slug);
            $company->setEmail($item['email']);
            $company->setPhone($item['phone']);
            $company->setAddress($item['address']);
            $company->setPostalCode($item['postalCode']);
            $company->setLocationType($item['locationType']);
            $company->setPrice($item['price']);
            $company->setDescription($item['body']);
            $company->setShortDescription($item['shortDescription']);
            $company->setWebsite($item['website']);
            $company->setAdmissionCriteria($item['admissionCriteria']);
            $company->setAvailableServices($item['services']);
            $company->setStatus(Company::STATUS_PUBLISHED);
            $company->setVideoUrl($item['videoUrl']);

            // Associate job with the corresponding category
            $category = $categoryEntities[$item['category']];
            $item['locationType'] === Company::LOCATION_TYPE_CARE ? $company->addCategoryCare($category) : $company->addCategoryService($category);

            // Persist and save
            $this->em->persist($company);
            $this->em->flush();

            if ($item['locationType'] === Company::LOCATION_TYPE_CARE) {
                // Create object
                $review = new CompanyReview();
                $review->setUuid(Uuid::v4());
                $review->setCompany($company);
                $review->setName('Ion#' . $i);
                $review->setSurname('Ionescu#' . $i);
                $review->setEmail($item['email']);
                $review->setPhone($item['phone']);
                $review->setDisplayName('Ionica#' . $i);
                $review->setConnection('Connection 1');
                $review->setReview($item['shortDescription']);
                $review->setGeneralStar(rand(1, 5));
                $review->setFacilityStar(rand(1, 5));
                $review->setMaintenanceStar(rand(1, 5));
                $review->setCleanStar(rand(1, 5));
                $review->setDignityStar(rand(1, 5));
                $review->setBeverageStar(rand(1, 5));
                $review->setPersonalStar(rand(1, 5));
                $review->setActivityStar(rand(1, 5));
                $review->setSecurityStar(rand(1, 5));
                $review->setManagementStar(rand(1, 5));
                $review->setRoomStar(rand(1, 5));
                $review->setPriceQualityStar(rand(1, 5));
                $review->setNameAgree(1);
                $review->setRatingAgree(1);
                $review->setStatus(CompanyReview::STATUS_APPROVED);
                $review->setStartDate(new \DateTime());
                $review->setEndDate(new \DateTime());
                $review->setTotalValuesStar(number_format($review->calculateTotalValuesStar(), 2));

                // Parse and save
                $this->em->persist($review);
                $this->em->flush();
            }

            $io->success('Company: ' . $item['title']);
        }


        $careCompanies = $this->em->getRepository(Company::class)->findBy(['status' => Company::STATUS_PUBLISHED, 'locationType' => Company::LOCATION_TYPE_CARE]);

        return Command::SUCCESS;
    }
}

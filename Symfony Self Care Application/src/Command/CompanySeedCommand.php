<?php

namespace App\Command;

use App\Entity\CategoryCare;
use App\Entity\CategoryCareTranslation;
use App\Entity\City;
use App\Entity\CompanyGallery;
use App\Entity\County;
use App\Entity\Company;
use App\Entity\User;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class CompanySeedCommand extends Command
{
    const APP_EMAIL = 'ads.seniorhelp@gmail.com';

    const CARE_CATEGORIES = [
        'Îngrijire Bătrâni',
        'Îngrijire Copii',
        'Îngrijire Persoane cu Nevoi Speciale',
        'Îngrijire Medicală',
        'Îngrijire pentru Reabilitare'
    ];
    const DESCRIPTION = ' este un loc dedicat seniorilor, unde aceștia pot beneficia de îngrijire profesională într-un mediu cald și familial. Situat într-o locație liniștită și accesibilă, centrul nostru oferă servicii complete de îngrijire și suport, adaptate nevoilor individuale ale fiecărui rezident. De la asistență medicală continuă și activități recreative, până la mese sănătoase și echilibrate, ne asigurăm că toți cei care trec pragul nostru se simt confortabil și în siguranță. Casa Bunicilor este mai mult decât un centru de îngrijire – este un loc unde seniorii pot trăi cu demnitate, respect și bucurie.';
    const SHORT_DESCRIPTION = ' oferă un mediu cald și primitor.';

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
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var string
     */
    private string $targetDirectory;

    /**
     * @param LanguageHelper $languageHelper
     * @param EntityManagerInterface $em
     * @param Filesystem $filesystem
     * @param $targetDirectory
     */
    public function __construct(LanguageHelper $languageHelper, EntityManagerInterface $em, Filesystem $filesystem, $targetDirectory)
    {
        parent::__construct();
        $this->languageHelper = $languageHelper;
        $this->em = $em;
        $this->targetDirectory = $targetDirectory;
        $this->filesystem = $filesystem;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('file_name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $successImported = 0;
        $errorImported = 0;
        $error = 0;

        // get excel name
        $fileName = $input->getArgument('file_name');
        $path = sprintf('%s/%s', $this->targetDirectory, $fileName);

        // Check file exist
        if (!file_exists($path)) {
            $io->write('File ' . $fileName . ' does not exist in directory: ' . $this->targetDirectory);
            return 0;
        }

        // Load file
        $spreadsheet = IOFactory::load($path);

        // Remove row 1 (Header)
        $spreadsheet->getActiveSheet()->removeRow(1);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);


        foreach ($sheetData as $row) {
            if ($row['A'] != null) {
                // Create new recipe
                $this->createCompany($row, $input, $output);

                $successImported++;
            } else {
                $error++;
            }
//            break;
        }

        // Return message console
        $io->write('Success Imported: ' . $successImported . ' Failed Imported(Error validate or User exists): ' . $errorImported . ' Empty row ' . $error);

        return Command::SUCCESS;
    }


    /**
     * @param $row
     * @param $input
     * @param $output
     * @return void
     */
    protected function createCompany($row, $input, $output): void
    {
        // create slug by title
        $slugger = new AsciiSlugger();
        $io = new SymfonyStyle($input, $output);

        // Rows data
        $rowData = [
            'name' => trim(preg_replace('/\s+/', ' ', $row['B'])),
            'slug' => $slugger->slug(trim(preg_replace('/\s+/', ' ', $row['B'])) . ' ' . trim(preg_replace('/\s+/', ' ', $row['G'])) . ' ' . trim(preg_replace('/\s+/', ' ', $row['H'])))->lower()->toString(),
            'companyType' => trim($row['C']) === 'Public' ? 'public' : 'private',
            'companyCapacity' => intval(trim($row['D'])),
            'address' => trim($row['I']),
            'county' => trim(preg_replace('/\s+/', ' ', $row['G'])),
            'city' => trim(preg_replace('/\s+/', ' ', $row['H'])),
            'category' => $this->getRandomValueFromArray(self::CARE_CATEGORIES)
        ];

        $county = $this->em->getRepository(County::class)->findOneBy(['name' => $rowData['county']]);
        $city = $this->em->getRepository(City::class)->findOneBy(['name' => $rowData['city']]);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => self::APP_EMAIL]);

        // Get county and city and user
        if (isset($county) && isset($city) && isset($user)) {
            $categorySlug = $slugger->slug($rowData['category'])->lower()->toString();
            $category = $this->em->getRepository(CategoryCare::class)->findOneBy(['slug' => $categorySlug]);

            if (empty($category)) {
                $category = new CategoryCare();
                $category->setUuid(Uuid::v4());
                $category->setSlug($categorySlug);
                $category->setStatus(CategoryCare::STATUS_PUBLISHED);

                // Persist and save
                $this->em->persist($category);
                $this->em->flush();

                // Parse all language
                foreach ($this->languageHelper->getAllLanguage() as $language) {
                    // Create category translations
                    $categoryTrans = new CategoryCareTranslation();
                    $categoryTrans->setTitle($rowData['category']);
                    $categoryTrans->setCategoryCare($category);
                    $categoryTrans->setLanguage($language);

                    // Persist and save
                    $this->em->persist($categoryTrans);
                    $this->em->flush();
                }
            }

            /** @var Company $company */
            $company = $this->em->getRepository(Company::class)->findOneBy(['slug' => $rowData['slug']]);

            $this->insertData($rowData, $user, $county, $city, $category, isset($company));

            if (isset($company)) {
                $io->write('Item duplicate:' . $row['A'] . ' Slug: ' . $rowData['slug'] . '-' . $rowData['companyCapacity'] . "\n");
            }
        }
    }

    /**
     * @param $rowData
     * @param $user
     * @param $county
     * @param $city
     * @param $category
     * @param bool $changeSlug
     * @return void
     */
    protected function insertData($rowData, $user, $county, $city, $category, bool $changeSlug = false): void
    {
        $company = new Company();
        $company->setUuid(Uuid::v4());
        $company->setName($rowData['name']);
        $company->setSlug($changeSlug ? $rowData['slug'] . '-' . $rowData['companyCapacity'] : $rowData['slug']);
        $company->setEmail($user->getEmail());
        $company->setPhone($user->getPhone());
        $company->setCounty($county);
        $company->setCity($city);
        $company->setAddress($rowData['address']);
        $company->setPostalCode(rand(701011, 705100));
        $company->setCompanyType($rowData['companyType']);
        $company->setCompanyCapacity($rowData['companyCapacity']);
        $company->setAdmissionCriteria(55);
        $company->setPrice(rand(2000, 10000));
        $company->setWebsite('https://www.seniorhelp.ro');
        $company->setAvailableServices(Company::getServices());
        $company->addCategoryCare($category);
        $company->setShortDescription($rowData['name'] . self::SHORT_DESCRIPTION);
        $company->setDescription($rowData['name'] . self::DESCRIPTION);
        $company->setLogo($user->getProfilePicture());
        $company->setFileName('care-' . rand(1, 2) . '.jpg');
        $company->setStatus(Company::STATUS_PUBLISHED);
        $company->setLocationType(Company::LOCATION_TYPE_CARE);
        $company->setUser($user);

        $this->em->persist($company);
        $this->em->flush();

        // Parse and save gallery image
        for ($i = 1; $i <= rand(1, 5); $i++) {
            $gallery = new CompanyGallery();
            $gallery->setFileName('gallery-' . rand(1, 5) . '.jpg');
            $gallery->setCompany($company);

            $this->em->persist($gallery);
            $this->em->flush();
        }
    }

    /**
     * @param $array
     * @return mixed
     */
    protected function getRandomValueFromArray($array): mixed
    {
        // Gets the number of elements in the array
        $count = count($array);

        // Generates a random index between 0 and count - 1
        $randomIndex = rand(0, $count - 1);

        // Returns the value from the array corresponding to the random index
        return $array[$randomIndex];
    }
}

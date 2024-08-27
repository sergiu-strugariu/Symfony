<?php

namespace App\Command;

use App\Entity\CategoryJob;
use App\Entity\CategoryJobTranslation;
use App\Entity\Company;
use App\Entity\County;
use App\Entity\Job;
use App\Entity\JobTranslation;
use App\Entity\User;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class JobSeedCommand extends Command
{
    const CATEGORIES = [
        'IT',
        'Marketing',
        'Design',
        'HR',
        'Sales',
        'Content',
        'Support',
        'Management',
        'SEO'
    ];


    const JOBS = [
        [
            'title' => 'Software Developer',
            'jobType' => Job::TYPE_FULL_TYME,
            'address' => 'Str. IT Nr. 10',
            'body' => 'Develop and maintain software applications.',
            'shortDescription' => 'Develop and maintain apps.',
            'category' => 'IT',
            'county' => 'București'
        ],
        [
            'title' => 'Marketing Specialist',
            'jobType' => Job::TYPE_PART_TYME,
            'address' => 'Str. Marketing Nr. 5, Cluj-Napoca',
            'setting' => 'Office',
            'body' => 'Create and manage marketing campaigns.',
            'shortDescription' => 'Manage marketing campaigns.',
            'category' => 'Marketing',
            'county' => 'Cluj'
        ],
        [
            'title' => 'Graphic Designer',
            'jobType' => Job::TYPE_FULL_TYME,
            'address' => 'Str. Artelor Nr. 7',
            'setting' => 'Office',
            'body' => 'Design graphics and visual content.',
            'shortDescription' => 'Design visual content.',
            'category' => 'Design',
            'county' => 'Timiș'
        ],
        [
            'title' => 'HR Manager',
            'jobType' => Job::TYPE_FULL_TYME,
            'address' => 'Str. Resurse Umane Nr. 3',
            'setting' => 'Office',
            'body' => 'Manage HR processes and policies.',
            'shortDescription' => 'Manage HR processes.',
            'category' => 'HR',
            'county' => 'Iași'
        ],
        [
            'title' => 'Sales Executive',
            'jobType' => Job::TYPE_PART_TYME,
            'address' => 'Str. Vânzări Nr. 12',
            'setting' => 'Office',
            'body' => 'Handle sales and customer relationships.',
            'shortDescription' => 'Handle sales and customers.',
            'category' => 'Sales',
            'county' => 'Brașov'
        ],
        [
            'title' => 'Content Writer',
            'jobType' => Job::TYPE_FULL_TYME,
            'address' => 'Str. Conținut Nr. 8',
            'setting' => 'Remote',
            'body' => 'Write and edit content for websites.',
            'shortDescription' => 'Write website content.',
            'category' => 'Content',
            'county' => 'Constanța'
        ],
        [
            'title' => 'Customer Support',
            'jobType' => Job::TYPE_PART_TYME,
            'address' => 'Str. Suport Nr. 11, Sibiu',
            'setting' => 'Remote',
            'body' => 'Provide customer support services.',
            'shortDescription' => 'Provide support services.',
            'category' => 'Support',
            'county' => 'Sibiu'
        ],
        [
            'title' => 'Project Manager',
            'jobType' => Job::TYPE_FULL_TYME,
            'address' => 'Str. Proiecte Nr. 14',
            'setting' => 'Office',
            'body' => 'Manage projects and teams.',
            'shortDescription' => 'Manage projects and teams.',
            'category' => 'Management',
            'county' => 'Bihor'
        ],
        [
            'title' => 'SEO Specialist',
            'jobType' => Job::TYPE_PART_TYME,
            'address' => 'Str. SEO Nr. 6',
            'setting' => 'Remote',
            'body' => 'Optimize website content for search engines.',
            'shortDescription' => 'Optimize for search engines.',
            'category' => 'SEO',
            'county' => 'Galați'
        ],
        [
            'title' => 'Network Engineer',
            'jobType' => Job::TYPE_FULL_TYME,
            'address' => 'Str. Rețele Nr. 9',
            'setting' => 'Office',
            'body' => 'Maintain and troubleshoot network systems.',
            'shortDescription' => 'Maintain network systems.',
            'category' => 'IT',
            'county' => 'Dolj'
        ]
    ];

    /**
     * @var string
     */
    protected static $defaultName = 'app:job-seed';

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
        $this->setDescription('Push category and jobs');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'seniorhelp@gmail.com']);
        $company = $this->em->getRepository(Company::class)->findOneBy(['slug' => 'childcare-services']);

        $slugger = new AsciiSlugger();

        // All categories
        $categories = self::CATEGORIES;

        $jobs = self::JOBS;

        // Parse categories
        $categoryEntities = [];
        foreach ($categories as $val) {
            $slug = $slugger->slug($val)->lower();

            // Check exist category
            $getCategory = $this->em->getRepository(CategoryJob::class)->findOneBy(['slug' => $slug]);

            if ($getCategory) {
                $io->error('This category exist: ' . $val);
                $categoryEntities[$val] = $getCategory;
                continue;
            }

            // Create new category
            $category = new CategoryJob();
            $category->setUuid(Uuid::v4());
            $category->setSlug($slug);
            $category->setStatus(CategoryJob::STATUS_PUBLISHED);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create category translations
                $categoryTrans = new CategoryJobTranslation();
                $categoryTrans->setTitle($val);
                $categoryTrans->setCategoryJob($category);
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

        foreach ($jobs as $item) {
            $slug = $slugger->slug($item['title'])->lower();

            // Check exist job
            $getJob = $this->em->getRepository(Job::class)->findOneBy(['slug' => $slug]);
            $getCounty = $this->em->getRepository(County::class)->findOneBy(['name' => $item['county']]);

            if ($getJob && $getCounty) {
                $io->error('This job exist: ' . $item['title']);
                continue;
            }

            $job = new Job();
            $job->setUuid(Uuid::v4());
            $job->setStartGrossSalary(rand(500, 3500));
            $job->setEndGrossSalary(rand(3500, 8500));
            $job->setSlug($slug);
            $job->setAddress($item['address']);
            $job->setJobType($item['jobType']);
            $job->setStatus(Job::STATUS_PUBLISHED);
            $job->setUser($user);
            $job->setCompany($company);
            $job->setCounty($getCounty);
            $job->setCity($getCounty->getCities()->first());

            // Associate job with the corresponding category
            $category = $categoryEntities[$item['category']];
            $job->addCategoryJob($category);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create job translations
                $jobTrans = new JobTranslation();
                $jobTrans->setTitle($item['title']);
                $jobTrans->setBody($item['body']);
                $jobTrans->setBenefits(Job::getBenefits());
                $jobTrans->setShortDescription($item['shortDescription']);
                $jobTrans->setJob($job);
                $jobTrans->setLanguage($language);

                // Persist and save
                $this->em->persist($jobTrans);
            }

            // Persist and save
            $this->em->persist($job);
            $this->em->flush();

            $io->success('Job: ' . $item['title']);
        }

        return Command::SUCCESS;
    }
}

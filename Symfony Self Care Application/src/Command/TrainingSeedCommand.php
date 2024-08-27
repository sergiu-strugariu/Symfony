<?php

namespace App\Command;

use App\Entity\CategoryCourse;
use App\Entity\CategoryCourseTranslation;
use App\Entity\Company;
use App\Entity\County;
use App\Entity\TrainingCourse;
use App\Entity\TrainingCourseTranslation;
use App\Entity\User;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class TrainingSeedCommand extends Command
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

    const COURSES = [
        [
            'title' => 'Introduction to IT',
            'price' => '500 RON',
            'address' => 'Str. Tehnologiei Nr. 1, București',
            'duration' => '2 hours',
            'level' => 'Beginner',
            'format' => TrainingCourse::FORMAT_ONLINE,
            'certificate' => 'Yes',
            'body' => 'Learn the basics of IT in this introductory course.',
            'shortDescription' => 'Basics of IT.',
            'category' => 'IT',
            'county' => 'București'
        ],
        [
            'title' => 'Advanced Marketing Strategies',
            'price' => '800 RON',
            'address' => 'Str. Marketingului Nr. 3, Cluj-Napoca',
            'duration' => '4 hours',
            'level' => 'Advanced',
            'format' => TrainingCourse::FORMAT_ONLINE,
            'certificate' => 'Yes',
            'body' => 'Learn advanced strategies to enhance your marketing skills.',
            'shortDescription' => 'Advanced marketing strategies.',
            'category' => 'Marketing',
            'county' => 'Cluj'
        ],
        [
            'title' => 'Graphic Design for Beginners',
            'price' => '600 RON',
            'address' => 'Str. Artelor Nr. 7, Timișoara',
            'duration' => '3 hours',
            'level' => 'Beginner',
            'format' => TrainingCourse::FORMAT_ONLINE,
            'certificate' => 'No',
            'body' => 'An introductory course on graphic design principles.',
            'shortDescription' => 'Intro to graphic design.',
            'category' => 'Design',
            'county' => 'Timiș'
        ],
        [
            'title' => 'HR Management Essentials',
            'price' => '700 RON',
            'address' => 'Str. Resurse Umane Nr. 3, Iași',
            'duration' => '2.5 hours',
            'level' => 'Intermediate',
            'format' => TrainingCourse::FORMAT_ONLINE,
            'certificate' => 'Yes',
            'body' => 'Essential skills for managing human resources.',
            'shortDescription' => 'HR management skills.',
            'category' => 'HR',
            'county' => 'Iași'
        ],
        [
            'title' => 'Sales Techniques and Strategies',
            'price' => '400 RON',
            'address' => 'Str. Vânzări Nr. 12, Brașov',
            'duration' => '2 hours',
            'level' => 'Intermediate',
            'format' => TrainingCourse::FORMAT_ONLINE,
            'certificate' => 'No',
            'body' => 'Effective techniques and strategies for successful sales.',
            'shortDescription' => 'Sales techniques.',
            'category' => 'Sales',
            'county' => 'Brașov'
        ],
        [
            'title' => 'Content Writing Workshop',
            'price' => '300 RON',
            'address' => 'Str. Conținut Nr. 8, Constanța',
            'duration' => '1.5 hours',
            'level' => 'Beginner',
            'format' => TrainingCourse::FORMAT_PHYSICAL,
            'certificate' => 'Yes',
            'body' => 'Hands-on workshop on content writing.',
            'shortDescription' => 'Content writing workshop.',
            'category' => 'Content',
            'county' => 'Constanța'
        ],
        [
            'title' => 'Customer Support Excellence',
            'price' => '350 RON',
            'address' => 'Str. Suport Nr. 11, Sibiu',
            'duration' => '2 hours',
            'level' => 'Intermediate',
            'format' => TrainingCourse::FORMAT_PHYSICAL,
            'certificate' => 'No',
            'body' => 'Learn how to provide excellent customer support.',
            'shortDescription' => 'Customer support skills.',
            'category' => 'Support',
            'county' => 'Sibiu'
        ],
        [
            'title' => 'Project Management Professional',
            'price' => '900 RON',
            'address' => 'Str. Proiecte Nr. 14, Oradea',
            'duration' => '5 hours',
            'level' => 'Advanced',
            'format' => TrainingCourse::FORMAT_PHYSICAL,
            'certificate' => 'Yes',
            'body' => 'Professional training for project management.',
            'shortDescription' => 'Professional project management.',
            'category' => 'Management',
            'county' => 'Bihor'
        ],
        [
            'title' => 'SEO Optimization Techniques',
            'price' => '550 RON',
            'address' => 'Str. SEO Nr. 6, Galați',
            'duration' => '3 hours',
            'level' => 'Intermediate',
            'format' => TrainingCourse::FORMAT_PHYSICAL,
            'certificate' => 'Yes',
            'body' => 'Techniques for optimizing your website for search engines.',
            'shortDescription' => 'SEO optimization.',
            'category' => 'SEO',
            'county' => 'Galați'
        ],
        [
            'title' => 'Network Engineering Basics',
            'price' => '650 RON',
            'address' => 'Str. Rețele Nr. 9, Craiova',
            'duration' => '4 hours',
            'level' => 'Beginner',
            'format' => TrainingCourse::FORMAT_PHYSICAL,
            'certificate' => 'Yes',
            'body' => 'Introduction to network engineering principles.',
            'shortDescription' => 'Network engineering basics.',
            'category' => 'IT',
            'county' => 'Dolj'
        ]
    ];

    /**
     * @var string
     */
    protected static $defaultName = 'app:training-seed';

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
        $io = new SymfonyStyle($input, $output);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'seniorhelp@gmail.com']);
        $company = $this->em->getRepository(Company::class)->findOneBy(['slug' => 'childcare-services']);

        $slugger = new AsciiSlugger();

        // All categories
        $categories = self::CATEGORIES;

        $courses = self::COURSES;

        // Parse categories
        $categoryEntities = [];
        foreach ($categories as $val) {
            $slug = $slugger->slug($val)->lower();

            // Check exist category
            $getCategory = $this->em->getRepository(CategoryCourse::class)->findOneBy(['slug' => $slug]);

            if ($getCategory) {
                $io->error('This category exist: ' . $val);
                $categoryEntities[$val] = $getCategory;
                continue;
            }

            // Create new category
            $category = new CategoryCourse();
            $category->setUuid(Uuid::v4());
            $category->setSlug($slug);
            $category->setStatus(CategoryCourse::STATUS_PUBLISHED);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create category translations
                $categoryTrans = new CategoryCourseTranslation();
                $categoryTrans->setTitle($val);
                $categoryTrans->setCategoryCourse($category);
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

        foreach ($courses as $item) {
            $slug = $slugger->slug($item['title'])->lower();

            // Check exist job
            $getJob = $this->em->getRepository(TrainingCourse::class)->findOneBy(['slug' => $slug]);
            $getCounty = $this->em->getRepository(County::class)->findOneBy(['name' => $item['county']]);

            if ($getJob && $getCounty) {
                $io->error('This job exist: ' . $item['title']);
                continue;
            }

            $course = new TrainingCourse();
            $course->setUuid(Uuid::v4());
            $course->setSlug($slug);
            $course->setAddress($item['address']);
            $course->setFileName('default.png');
            $course->setStatus(TrainingCourse::STATUS_PUBLISHED);
            $course->setFormat($item['format']);
            $course->setUser($user);
            $course->setCounty($getCounty);
            $course->setCity($getCounty->getCities()->first());
            $course->setStartCourseDate(new \DateTime());
            $course->setMinParticipant(rand(1, 5));
            $course->setMaxParticipant(rand(10, 15));
            $course->setCompany($company);
            // Associate job with the corresponding category
            $category = $categoryEntities[$item['category']];
            $course->addCategoryCourse($category);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create job translations
                $courseTrans = new TrainingCourseTranslation();
                $courseTrans->setTitle($item['title']);
                $courseTrans->setPrice($item['price']);
                $courseTrans->setDuration($item['duration']);
                $courseTrans->setLevel($item['level']);
                $courseTrans->setCertificate($item['certificate']);
                $courseTrans->setBody($item['body']);
                $courseTrans->setShortDescription($item['shortDescription']);
                $courseTrans->setTrainingCourse($course);
                $courseTrans->setLanguage($language);

                // Persist and save
                $this->em->persist($courseTrans);
            }

            // Persist and save
            $this->em->persist($course);
            $this->em->flush();

            $io->success('Job: ' . $item['title']);
        }

        return Command::SUCCESS;
    }
}

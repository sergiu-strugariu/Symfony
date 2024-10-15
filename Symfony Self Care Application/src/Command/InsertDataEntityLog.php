<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\Company;
use App\Entity\Job;
use App\Entity\TrainingCourse;
use App\Helper\MembershipHelper;
use App\Repository\ArticleRepository;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use App\Repository\TrainingCourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InsertDataEntityLog extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:generate-entity-log';

    /**
     * @var ArticleRepository
     */
    private ArticleRepository $articleRepository;

    /**
     * @var JobRepository
     */
    private JobRepository $jobRepository;

    /**
     * @var TrainingCourseRepository
     */
    private TrainingCourseRepository $trainingCourseRepository;

    /**
     * @var CompanyRepository
     */
    private CompanyRepository $companyRepository;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var MembershipHelper
     */
    protected MembershipHelper $helper;

    /**
     * @param ArticleRepository $articleRepository
     * @param JobRepository $jobRepository
     * @param TrainingCourseRepository $trainingCourseRepository
     * @param CompanyRepository $companyRepository
     * @param EntityManagerInterface $em
     * @param MembershipHelper $helper
     */
    public function __construct(
        ArticleRepository        $articleRepository,
        JobRepository            $jobRepository,
        TrainingCourseRepository $trainingCourseRepository,
        CompanyRepository        $companyRepository,
        EntityManagerInterface   $em,
        MembershipHelper            $helper)
    {
        parent::__construct();
        $this->articleRepository = $articleRepository;
        $this->jobRepository = $jobRepository;
        $this->trainingCourseRepository = $trainingCourseRepository;
        $this->companyRepository = $companyRepository;
        $this->em = $em;
        $this->helper = $helper;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Generate the entityLog');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating entityLog...');

        // Get all items
        $articles = $this->articleRepository->getAllArticles();
        $jobs = $this->jobRepository->getAllJobs();
        $courses = $this->trainingCourseRepository->getAllCourses();
        $cares = $this->companyRepository->getAllCompanyByType();
        $providers = $this->companyRepository->getAllCompanyByType(Company::LOCATION_TYPE_PROVIDER);

        // Adding the article URLs
        $this->helper->parseEntityLog($articles, Article::ENTITY_NAME, false);

        // Adding the job URLs
        $this->helper->parseEntityLog($jobs, Job::ENTITY_NAME, false);

        // Adding the course URLs
        $this->helper->parseEntityLog($courses, TrainingCourse::ENTITY_NAME, false);

        // Adding the company care URLs
        $this->helper->parseEntityLog($cares, Company::ENTITY_NAME, false);

        // Adding the company provider URLs
        $this->helper->parseEntityLog($providers, Company::ENTITY_NAME, false);

        $io->success("EntityLog generated and saved");

        return Command::SUCCESS;
    }
}
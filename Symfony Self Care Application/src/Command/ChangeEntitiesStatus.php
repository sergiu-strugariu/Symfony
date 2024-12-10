<?php

namespace App\Command;

use App\Entity\Job;
use App\Entity\TrainingCourse;
use App\Helper\MembershipHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChangeEntitiesStatus extends Command
{
    /**
     * @var MembershipHelper
     */
    private MembershipHelper $helper;

    /**
     * @var string
     */
    protected static $defaultName = 'app:check-ended-date';

    /**
     * @param MembershipHelper $helper
     */
    public function __construct(MembershipHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Check and change entities status.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check expired items and change status in @draft
        $articles = $this->helper->changeEntitiesStatus();
        $jobs = $this->helper->changeEntitiesStatus(Job::ENTITY_NAME);
        $courses = $this->helper->changeEntitiesStatus(TrainingCourse::ENTITY_NAME);

        $io->success('Total articles changed: ' . $articles);
        $io->success('Total jobs changed: ' . $jobs);
        $io->success('Total courses changed: ' . $courses);

        return Command::SUCCESS;
    }
}

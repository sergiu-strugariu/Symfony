<?php

namespace App\Command;

use Elastica\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Elastica\Exception\ConnectionException;

class TestElasticsearchConnectionCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:test-elasticsearch-connection';

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Tests the connection to the Elasticsearch server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $response = $this->client->request('/');
            if ($response->isOk()) {
                $io->success('Successfully connected to Elasticsearch.');
                return Command::SUCCESS;
            } else {
                $io->error('Failed to connect to Elasticsearch. Response status: ' . $response->getStatus());
                return Command::FAILURE;
            }
        } catch (ConnectionException $e) {
            $io->error('Failed to connect to Elasticsearch: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

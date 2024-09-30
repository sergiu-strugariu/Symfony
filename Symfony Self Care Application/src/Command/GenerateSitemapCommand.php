<?php

namespace App\Command;

use App\Entity\Company;
use App\Repository\ArticleRepository;
use App\Repository\CompanyRepository;
use App\Repository\EventRepository;
use App\Repository\JobRepository;
use App\Repository\TrainingCourseRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSitemapCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:generate-sitemap';

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var string
     */
    private string $projectDir;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var ArticleRepository
     */
    private ArticleRepository $articleRepository;

    /**
     * @var JobRepository
     */
    private JobRepository $jobRepository;

    /**
     * @var EventRepository
     */
    private EventRepository $eventRepository;

    /**
     * @var TrainingCourseRepository
     */
    private TrainingCourseRepository $trainingCourseRepository;

    /**
     * @var CompanyRepository
     */
    private CompanyRepository $companyRepository;

    /**
     * @param RouterInterface $router
     * @param ArticleRepository $articleRepository
     * @param JobRepository $jobRepository
     * @param TrainingCourseRepository $trainingCourseRepository
     * @param EventRepository $eventRepository
     * @param CompanyRepository $companyRepository
     * @param string $projectDir
     * @param string $baseUrl
     */
    public function __construct(RouterInterface          $router,
                                ArticleRepository        $articleRepository,
                                JobRepository            $jobRepository,
                                TrainingCourseRepository $trainingCourseRepository,
                                EventRepository          $eventRepository,
                                CompanyRepository        $companyRepository,
                                string                   $projectDir,
                                string                   $baseUrl)
    {
        parent::__construct();
        $this->router = $router;
        $this->projectDir = $projectDir;
        $this->baseUrl = $baseUrl;
        $this->articleRepository = $articleRepository;
        $this->jobRepository = $jobRepository;
        $this->trainingCourseRepository = $trainingCourseRepository;
        $this->eventRepository = $eventRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Generate the sitemap XML file for the website');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating sitemap...');

        // Get the routes and generate the sitemap
        $sitemapXml = $this->generateSitemap();
        $filePath = $this->projectDir . '/public/sitemap.xml';

        // Save the generated XML to a file
        file_put_contents($filePath, $sitemapXml);

        $io->success("Sitemap generated and saved to $filePath");

        return Command::SUCCESS;
    }

    /**
     * @return string
     */
    private function generateSitemap(): string
    {
        $urls = [];
        $hardcodedUrls = ['/login', '/resetting/request', '/creare-cont/client', '/creare-cont/company'];

        // Specific controller for which we want to generate the sitemap
        $controllerClass = 'App\Controller\Frontend\DefaultController';

        $routeCollection = $this->router->getRouteCollection();

        /** @var Route $route */
        foreach ($routeCollection as $route) {
            // We check if the route belongs to the desired controller and does not contain parameters
            if ($this->isRouteForController($route, $controllerClass) && !$this->isRouteParameterized($route)) {
                $url = $this->baseUrl . $route->getPath();
                $urls[] = [
                    'loc' => $url,
                    'lastmod' => (new \DateTime())->format('Y-m-d'),
                    'changefreq' => 'daily',
                    'priority' => 0.9
                ];
            }
        }

        // Parse hardcoded routes
        foreach ($hardcodedUrls as $url) {
            $url = $this->baseUrl . $url;
            $urls[] = [
                'loc' => $url,
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => 0.8
            ];
        }

        // Get all items
        $articles = $this->articleRepository->getAllArticles();
        $jobs = $this->jobRepository->getAllJobs();
        $courses = $this->trainingCourseRepository->getAllCourses();
        $events = $this->eventRepository->getAllEvents();
        $cares = $this->companyRepository->getAllCompanyByType();
        $providers = $this->companyRepository->getAllCompanyByType(Company::LOCATION_TYPE_PROVIDER);

        // Adding the article URLs
        $this->addEntityUrls($urls, $this->baseUrl, $articles, 'blog');

        // Adding the job URLs
        $this->addEntityUrls($urls, $this->baseUrl, $jobs, 'job');

        // Adding the course URLs
        $this->addEntityUrls($urls, $this->baseUrl, $courses, 'curs');

        // Adding the course URLs
        $this->addEntityUrls($urls, $this->baseUrl, $events, 'eveniment');

        // Adding the company care URLs
        $this->addEntityUrls($urls, $this->baseUrl, $cares, 'camin');

        // Adding the company provider URLs
        $this->addEntityUrls($urls, $this->baseUrl, $providers, 'furnizor');

        // Adding the company care URLs
        $this->addEntityUrls($urls, $this->baseUrl, $cares, 'camin/trimite-recenzie');

        return $this->generateSitemapXml($urls);
    }

    /**
     * Checks if the route belongs to the specified controller.
     *
     * @param Route $route
     * @param string $controllerClass
     * @return bool
     */
    private function isRouteForController(Route $route, string $controllerClass): bool
    {
        $defaults = $route->getDefaults();
        return isset($defaults['_controller']) && str_starts_with($defaults['_controller'], $controllerClass);
    }

    /**
     * Checks if the route has parameters like {slug}, {id}, etc.
     *
     * @param Route $route
     * @return bool
     */
    private function isRouteParameterized(Route $route): bool
    {
        return str_contains($route->getPath(), '{');
    }

    /**
     * Add items URLs to sitemap.
     *
     * @param array $urls
     * @param string $baseUrl
     * @param array $items
     * @param string $pagePath
     */
    private function addEntityUrls(array &$urls, string $baseUrl, array $items, string $pagePath): void
    {
        foreach ($items as $item) {
            $url = $baseUrl . sprintf('/%s/', $pagePath) . $item->getSlug();

            $urls[] = [
                'loc' => $url,
                'lastmod' => null === $item->getUpdatedAt() ? $item->getCreatedAt()->format('Y-m-d') : $item->getUpdatedAt()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => 0.9
            ];
        }
    }

    /**
     * Generează XML-ul sitemap-ului din lista de URL-uri.
     *
     * @param array $urls
     * @return string
     */
    private function generateSitemapXml(array $urls): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', $url['loc']);
            $urlElement->addChild('lastmod', $url['lastmod']);
            $urlElement->addChild('changefreq', $url['changefreq']);
            $urlElement->addChild('priority', (string)$url['priority']);
        }

        return $xml->asXML();
    }
}
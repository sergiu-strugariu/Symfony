<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\CategoryArticle;
use App\Entity\CategoryArticleTranslation;
use App\Entity\User;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class ArticleSeedCommand extends Command
{
    const CATEGORIES = ['Category1', 'Category2', 'Category3', 'Category4', 'Category5'];

    const ARTICLES = [
        [
            'title' => 'First Article 01',
            'body' => 'This is the body of the first article with 50 chars.',
            'shortDescription' => 'First short desc with 30 chars.',
            'category' => 'Category1'
        ],
        [
            'title' => 'Second Article 02',
            'body' => 'This is the body of the second article with 50 chars.',
            'shortDescription' => 'Second short desc with 30 chars.',
            'category' => 'Category2'
        ],
        [
            'title' => 'Third Article 03',
            'body' => 'This is the body of the third article with 50 chars.',
            'shortDescription' => 'Third short desc with 30 chars.',
            'category' => 'Category3'
        ],
        [
            'title' => 'Fourth Article 04',
            'body' => 'This is the body of the fourth article with 50 chars.',
            'shortDescription' => 'Fourth short desc with 30 chars.',
            'category' => 'Category4'
        ],
        [
            'title' => 'Fifth Article 05',
            'body' => 'This is the body of the fifth article with 50 chars.',
            'shortDescription' => 'Fifth short desc with 30 chars.',
            'category' => 'Category5'
        ],
        [
            'title' => 'Sixth Article 06',
            'body' => 'This is the body of the sixth article with 50 chars.',
            'shortDescription' => 'Sixth short desc with 30 chars.',
            'category' => 'Category1'
        ],
        [
            'title' => 'Seventh Article 07',
            'body' => 'This is the body of the seventh article with 50 chars.',
            'shortDescription' => 'Seventh short desc with 30 chars.',
            'category' => 'Category2'
        ],
        [
            'title' => 'Eighth Article 08',
            'body' => 'This is the body of the eighth article with 50 chars.',
            'shortDescription' => 'Eighth short desc with 30 chars.',
            'category' => 'Category3'
        ],
        [
            'title' => 'Ninth Article 09',
            'body' => 'This is the body of the ninth article with 50 chars.',
            'shortDescription' => 'Ninth short desc with 30 chars.',
            'category' => 'Category4'
        ],
        [
            'title' => 'Tenth Article 10',
            'body' => 'This is the body of the tenth article with 50 chars.',
            'shortDescription' => 'Tenth short desc with 30 chars.',
            'category' => 'Category5'
        ]
    ];

    /**
     * @var string
     */
    protected static $defaultName = 'app:article-seed';

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
        $this->setDescription('Push category and articles');
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
        $slugger = new AsciiSlugger();

        // All categories
        $categories = self::CATEGORIES;

        $articles = self::ARTICLES;

        // Parse categories
        $categoryEntities = [];
        foreach ($categories as $val) {
            $slug = $slugger->slug($val)->lower();

            // Check exist category
            $getCategory = $this->em->getRepository(CategoryArticle::class)->findOneBy(['slug' => $slug]);

            if ($getCategory) {
                $io->error('This category exist: ' . $val);
                $categoryEntities[$val] = $getCategory;
                continue;
            }

            // Create new category
            $category = new CategoryArticle();
            $category->setUuid(Uuid::v4());
            $category->setSlug($slug);
            $category->setStatus(CategoryArticle::STATUS_PUBLISHED);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create category translations
                $categoryTrans = new CategoryArticleTranslation();
                $categoryTrans->setTitle($val);
                $categoryTrans->setCategoryArticle($category);
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

        foreach ($articles as $item) {
            $slug = $slugger->slug($item['title'])->lower();

            // Check exist article
            $getArticle = $this->em->getRepository(Article::class)->findOneBy(['slug' => $slug]);

            if ($getArticle) {
                $io->error('This article exist: ' . $item['title']);
                continue;
            }

            $article = new Article();
            $article->setUuid(Uuid::v4());
            $article->setSlug($slug);
            $article->setStatus(Article::STATUS_PUBLISHED);
            $article->setUser($user);
            $article->setImage('default.png');

            // Associate article with the corresponding category
            $category = $categoryEntities[$item['category']];
            $article->addCategoryArticle($category);

            // Parse all language
            foreach ($this->languageHelper->getAllLanguage() as $language) {
                // Create article translations
                $articleTrans = new ArticleTranslation();
                $articleTrans->setTitle($item['title']);
                $articleTrans->setBody($item['body']);
                $articleTrans->setShortDescription($item['shortDescription']);
                $articleTrans->setArticle($article);
                $articleTrans->setLanguage($language);

                // Persist and save
                $this->em->persist($articleTrans);
            }

            // Persist and save
            $this->em->persist($article);
            $this->em->flush();

            $io->success('Article: ' . $item['title']);
        }

        return Command::SUCCESS;
    }
}

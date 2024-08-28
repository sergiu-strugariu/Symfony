<?php

namespace App\Controller\Frontend;

use App\Controller\Dashboard\LanguageController;
use App\Entity\Article;
use App\Entity\CertificationCategory;
use App\Entity\Page;
use App\Entity\Gallery;
use App\Entity\TeamMember;
use App\Form\Type\ContactType;
use App\Helper\LanguageHelper;
use App\Helper\MailHelper;
use App\Repository\ArticleRepository;
use App\Repository\GalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "homepage"]);
        return $this->render('frontend/default/page.html.twig', ['page' => $page]);
    }

    #[Route('/language/{_locale}', name: 'app_language')]
    public function language(EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        return $this->redirectToRoute("app_index");
    }

    #[Route('/despre-noi', name: 'app_about_us')]
    public function aboutUs(EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "about-us"]);
        return $this->render('frontend/default/page.html.twig', ['page' => $page]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(): Response
    {
        return $this->render('frontend/default/search-results.html.twig');
    }

    #[Route('/parteneri', name: 'app_parteners')]
    public function parteners(EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "partners"]);
        return $this->render('frontend/default/page.html.twig', ['page' => $page]);
    }

    #[Route('/politica-de-confidentialitate', name: 'app_privacy_policy')]
    public function privacy(EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "privacy-statement"]);
        return $this->render('frontend/default/page.html.twig', ['page' => $page]);
    }

    #[Route('/termeni-si-conditii', name: 'app_terms')]
    public function terms(EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "terms-service"]);
        return $this->render('frontend/default/page.html.twig', ['page' => $page]);
    }

    #[Route('/sitemap', name: 'app_sitemap')]
    public function sitemap(): Response
    {
        return $this->render('frontend/default/sitemap.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/contact', name: 'app_contact')]
    public function contact(EntityManagerInterface $em, Request $request, MailHelper $mail, TranslatorInterface $translator): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "contact"]);
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $email = $mail->sendMail(
                $form->get('emailAddress')->getData(),
                'Contact',
                'frontend/emails/contact.html.twig', $formData
            );

            if (!$email) {
                $this->addFlash('error', $translator->trans('authentication.mail.fail'));
                return $this->redirectToRoute('app_contact');
            }

            $this->addFlash('success', $translator->trans('authentication.mail.success'));
            return $this->redirectToRoute('app_contact');
        }
        return $this->render('frontend/default/page.html.twig', [
            'page' => $page,
            'form' => $form->createView()
        ]);
    }

    #[Route('/certificari', name: 'app_certificates')]
    public function certificate(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(CertificationCategory::class)->findAll();
        $categorizedCertifications = [];

        foreach ($categories as $category) {
            $categorizedCertifications[] = [
                'category' => $category
            ];
        }

        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "certification"]);
        return $this->render('frontend/default/page.html.twig', ['page' => $page, 'categories' => $categorizedCertifications]);
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(Request $request, EntityManagerInterface $em, ArticleRepository $articleRepository): Response
    {
        $currentPage = $request->get('p', 1);
        $limit = 8;
        $offset = ($currentPage - 1) * $limit;

        $totalCount = $articleRepository->findTotalCount(Article::STATUS_PUBLISHED);
        $totalPages = ceil($totalCount / $limit);
        $articles = $articleRepository->findBy(['status' => Article::STATUS_PUBLISHED, 'deletedAt' => null], ['createdAt' => 'DESC'], $limit, $offset);
        $article = $articleRepository->findOneBy(['status' => Article::STATUS_PUBLISHED, 'deletedAt' => null], ['createdAt' => 'DESC']);

        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "blog"]);
        return $this->render('frontend/default/page.html.twig', [
            'page' => $page,
            'article' => $article,
            'articles' => $articles,
            'totalPages' => $totalPages,
            'totalResults' => $totalCount,
            'currentPage' => $currentPage
        ]);
    }

    #[Route('/multimedia', name: 'app_multimedia')]
    public function multimedia(Request $request, GalleryRepository $repository, LanguageHelper $languageHelper): Response
    {
        $locale = $request->getLocale();
        $language = $languageHelper->getLanguageByLocale($locale);

        // Pagination
        $page = $request->get('p', 1);
        $limit = 4;
        $offset = ($page - 1) * $limit;

        // Filtration
        $type = $request->get('type');
        $location = $request->get('location');
        $query = $request->get('q');

        $locations = $repository->getAllMultimediaLocations($type);
        if (!in_array($location, $locations))  {
            $location = "all";
        }

        $types = $repository->getAllMultimediaTypes();
        $galleries = $repository->findMultimediaByFilters($language, $type, $location, $query, $limit, $offset);

        $totalCount = $repository->findMultimediaByFilters($language, $type, $location, $query, $limit, $offset, true);
        $totalPages = ceil($totalCount / $limit);

        return $this->render('frontend/default/multimedia.html.twig', [
            'galleries' => $galleries,
            'totalPages' => $totalPages,
            'totalResults' => $totalCount,
            'currentPage' => $page,
            'types' => array_column($types, 'type'),
            'selectedType' => $type,
            'locations' => $locations,
            'selectedLocation' => $location,
            'query' => $query
        ]);
    }

    #[Route('/echipa', name: 'app_team')]
    public function team(EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "team"]);
        $teamMembers = $em->getRepository(TeamMember::class)->findAllMembers();

        return $this->render('frontend/default/page.html.twig', [
            'page' => $page,
            'teamMembers' => $teamMembers
        ]);
    }

    #[Route('/echipa/{slug}', name: 'app_team_member')]
    public function teamMember(EntityManagerInterface $em, $slug): Response
    {
        $teamMember = $em->getRepository(TeamMember::class)->findOneBy([
            'slug' => $slug
        ]);

        if (!$teamMember) {
            return $this->redirectToRoute('app_team');
        }

        if ($teamMember->getDeletedAt()) {
            return $this->redirectToRoute('app_team');
        }

        return $this->render('frontend/default/team-details.html.twig', [
            'member' => $teamMember
        ]);
    }

    #[Route('/articol/{slug}', name: 'app_article')]
    public function article(Request $request, EntityManagerInterface $em, ArticleRepository $articleRepository, $slug): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if (!isset($article)) {
            return $this->redirectToRoute('app_blog');
        }

        $locale = $request->getLocale();

        $nextArticle = $articleRepository->findNextPrevArticle($article);
        $prevArticle = $articleRepository->findNextPrevArticle($article, 'DESC');

        return $this->render('frontend/default/article.html.twig', [
            'article' => $article,
            'nextArticle' => $nextArticle,
            'prevArticle' => $prevArticle,
            'locale' => $locale
        ]);
    }
    
}

<?php

namespace App\Controller\Dashboard;

use App\Entity\Education;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CourseController extends AbstractController
{
    #[Route('/dashboard/courses', name: 'dashboard_course_index')]
    public function index(): Response
    {
        return $this->render('dashboard/education/index.html.twig', [
            'type' => Education::TYPE_COURSE,
            'page_title' => 'Courses'
        ]);
    }
}

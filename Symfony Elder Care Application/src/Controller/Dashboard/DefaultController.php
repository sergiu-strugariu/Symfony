<?php

namespace App\Controller\Dashboard;

use App\Entity\NursingHome;
use App\Entity\Pacient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $pacients = $em->getRepository(Pacient::class)->getAllPacients($user);
        $countPacients = $em->getRepository(Pacient::class)->countAllPacients($user);

        $countLocations = $em->getRepository(NursingHome::class)->countNursingHomes();
        $staff = count($em->getRepository(User::class)->getAdmins());

        if ($this->isGranted('ROLE_ADMIN')) {
            $staff = $em->getRepository(NursingHome::class)->getTotalUserCount($user);
            $countLocations = $em->getRepository(NursingHome::class)->countNursingHomes($user);
        }

        $template = $this->isGranted('ROLE_ADMIN') ? 'dashboard/dashboard/admin.html.twig' : 'dashboard/dashboard/superadmin.html.twig';

        return $this->render($template, [
            'countPacients' => $countPacients,
            'pacients' => $pacients,
            'countLocations' => $countLocations,
            'staff' => $staff,
        ]);
    }

    /**
     * @Route("/dashboard/cache/clear", name="dashboard_clear_cache")
     */
    public function clearCache(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
            '--env' => $this->getParameter('app_env'),
            '--no-warmup' => true
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);


        // Set flash message
        $this->addFlash('success', 'Cache-ul a fost șters cu succes.');
        return new RedirectResponse($this->generateUrl('dashboard_user_profile'));
    }
}

<?php

namespace App\Controller\Dashboard;

use App\Entity\ArticleTranslation;
use App\Entity\TeamMember;
use App\Entity\TeamMemberTranslation;
use App\Form\Type\TeamMemberType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class TeamMemberController extends AbstractController
{

    #[Route('/dashboard/team-members', name: 'dashboard_team_member_index')]
    public function index(Request $request): Response
    {
        $range = $request->get('range', '');
        return $this->render('dashboard/team_member/index.html.twig', [
            'range' => $range,
        ]);
    }

    #[Route('/dashboard/team-member/create', name: 'dashboard_team_member_create')]
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, LanguageHelper $languageHelper): Response
    {
        $teamMember = new TeamMember();

        $form = $this->createForm(TeamMemberType::class, $teamMember);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teamMember->setUuid(Uuid::v4());
            $file = $form->get('image')->getData();

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_team_member_path')
            );

            if ($uploadFile['success']) {
                $teamMember->setImageName($uploadFile['fileName']);
            }
            $language = $languageHelper->getDefaultLanguage();

            $teamMemberTranslation = new TeamMemberTranslation();
            $teamMemberTranslation->setLanguage($language);
            $teamMemberTranslation->setTeamMember($teamMember);
            $teamMemberTranslation->setSpecialization($form->get('specialization')->getData());
            $teamMemberTranslation->setDescription($form->get('description')->getData());

            $em->persist($teamMemberTranslation);
            $em->persist($teamMember);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new team member.');
            return $this->redirectToRoute('dashboard_team_member_index');
        }

        return $this->render('dashboard/team_member/management.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    #[Route('/dashboard/team-member/{uuid}/edit', name: 'dashboard_team_member_edit')]
    public function edit(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, LanguageHelper $languageHelper, $uuid): Response
    {
        $teamMember = $em->getRepository(TeamMember::class)->findOneBy(['uuid' => $uuid]);
        if (null === $teamMember) {
            return $this->redirectToRoute('dashboard_team_member_index');
        }

        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $teamMemberTranslation = $em->getRepository(TeamMemberTranslation::class)->findOneBy([
            'teamMember' => $teamMember,
            'language' => $language
        ]);

        if (null === $teamMemberTranslation) {
            $teamMemberTranslation = new TeamMemberTranslation();
            $teamMemberTranslation->setLanguage($language);
            $teamMemberTranslation->setTeamMember($teamMember);
        }

        $form = $this->createForm(TeamMemberType::class, $teamMember, [
            'translation' => $teamMemberTranslation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_team_member_path')
            );

            if ($uploadFile['success']) {
                $teamMember->setImageName($uploadFile['fileName']);
            }

            $teamMemberTranslation->setDescription($form->get('description')->getData());
            $teamMemberTranslation->setSpecialization($form->get('specialization')->getData());

            $em->persist($teamMemberTranslation);
            $em->persist($teamMember);
            $em->flush();

            $this->addFlash('success', 'You have successfully edited the team member');
            return $this->redirectToRoute('dashboard_team_member_index');
        }

        return $this->render('dashboard/team_member/management.html.twig', [
            'form' => $form->createView(),
            'entity' => $teamMember,
            'editMode' => true
        ]);
    }

    #[Route('/dashboard/team-member/{uuid}/delete', name: 'dashboard_team_member_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $teamMember = $em->getRepository(TeamMember::class)->findOneBy(['uuid' => $uuid]);
        if (null === $teamMember) {
            return $this->redirectToRoute('dashboard_team_member_index');
        }

        $teamMember->setDeletedAt(new \DateTime());
        $em->persist($teamMember);
        $em->flush();

        $this->addFlash('success', 'The team member has been successfully deleted');
        return $this->redirectToRoute('dashboard_team_member_index');
    }

}

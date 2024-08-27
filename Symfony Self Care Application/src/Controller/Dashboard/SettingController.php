<?php

namespace App\Controller\Dashboard;

use App\Entity\Setting;
use App\Form\Type\SettingFormType;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingController extends AbstractController
{
    #[Route('/dashboard/setting', name: 'dashboard_settings_index')]
    public function index(Request $request, FileUploader $fileUploader, EntityManagerInterface $em, DefaultHelper $helper, TranslatorInterface $translator): Response
    {
        $setting = $em->getRepository(Setting::class);
        $fields = $helper::SETTING_FIELDS;
        $files = $helper::SETTING_FILE_FIELDS;
        $data = [];

        // Parse values and set to formType
        foreach ($fields as $field) {
            $getSetting = $setting->findOneBy(['settingName' => $field]);
            if ($getSetting) {
                $data[$field] = $getSetting->getSettingValue();
            }
        }

        $form = $this->createForm(SettingFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();
            $formData = $data['setting_form'];

            // Parse and save files
            foreach ($files as $value) {
                $file = $form->get($value)->getData();

                if (isset($file)) {
                    // Upload logo file
                    $uploadFile = $fileUploader->uploadFile(
                        $file,
                        $form,
                        $this->getParameter('app_setting_path'),
                        $value
                    );

                    // Check and set @filename
                    if ($uploadFile['success']) {
                        $getSetting = $setting->findOneBy(['settingName' => $value]) ?? new Setting();
                        $getSetting->setSettingName($value);
                        $getSetting->setSettingValue($uploadFile['fileName']);
                        $em->persist($getSetting);
                    }
                }
            }

            // Parse and save fields
            foreach ($fields as $field) {
                if (isset($formData[$field])) {
                    $getSetting = $setting->findOneBy(['settingName' => $field]) ?? new Setting();
                    $getSetting->setSettingName($field);
                    $getSetting->setSettingValue($formData[$field]);
                    $em->persist($getSetting);
                }
            }

            $em->flush();

            // Add flash message
            $this->addFlash('success', $translator->trans('controller.success_updated', [], 'messages'));

            // Redirect to the same route to avoid form resubmission
            return $this->redirectToRoute('dashboard_settings_index');
        }

        return $this->render('dashboard/setting/actions.html.twig', [
            'pageTitle' => 'Settings',
            'form' => $form->createView()
        ]);
    }
}

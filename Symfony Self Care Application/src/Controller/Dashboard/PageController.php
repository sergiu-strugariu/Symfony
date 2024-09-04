<?php

namespace App\Controller\Dashboard;

use App\Entity\Page;
use App\Form\Type\PageFormType;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\FileUploaderOld;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageController extends AbstractController
{
    #[Route('/dashboard/page', name: 'dashboard_page_index')]
    public function index(): Response
    {
        return $this->render('dashboard/page/index.html.twig');
    }

    #[Route('/dashboard/page/create', name: 'dashboard_page_create')]
    public function create(Request $request, EntityManagerInterface $em, FileUploaderOld $fileUploader, LanguageHelper $languageHelper, DefaultHelper $helper, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(PageFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the form
            $file = $form->get('file')->getData();

            // Upload company file
            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                '/pages/',
                'file',
                'config',
                false
            );

            // Check and set @filename
            if ($uploadFile['success']) {
                // Get path to file
                $jsonParseTemplate = $helper->parsePageJsonFile(str_replace('.json', '', $uploadFile['fileName']));

                // Check if the fileJson exists
                if (!$jsonParseTemplate['success']) {
                    $this->addFlash('danger', $jsonParseTemplate['message']);
                    return $this->redirectToRoute('dashboard_page_index');
                }

                // Get template data
                $pageTemplate = $jsonParseTemplate['data'];

                $machineName = $pageTemplate['machineName'];
                $fullClassName = sprintf("App\Entity\%s", $pageTemplate['entity']);

                // Check exist class
                if (!class_exists($fullClassName)) {
                    $this->addFlash('danger', $translator->trans('form.default.required', [], 'messages'));
                    return $this->redirectToRoute('dashboard_page_index');
                }

                // Get page by @machineName
                $page = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

                // Check exist page or not
                $page = empty($page) ? new $fullClassName : $page;
                $page->setName($pageTemplate['name']);
                $page->setUrl($pageTemplate['url']);
                $page->setMachineName($machineName);
                $page->setClasses($pageTemplate['classes']);

                // Parse and save data
                $em->persist($page);
                $em->flush();

                // Check exist sections
                if (isset($pageTemplate['sections'])) {
                    // Parse sections and insert data
                    foreach ($pageTemplate['sections'] as $itemSection) {
                        $fullClassName = sprintf("App\Entity\%s", $itemSection['entity']);

                        // Check exist class
                        if (!class_exists($fullClassName)) continue;

                        // Get section by @machineName
                        $section = $em->getRepository($fullClassName)->findOneBy(['machineName' => $itemSection['machineName']]);

                        // Check exist section or not
                        $section = empty($section) ? new $fullClassName : $section;
                        $section->setName($itemSection['name']);
                        $section->setMachineName($itemSection['machineName']);
                        $section->setTemplate($itemSection['template']);
                        $section->setPage($page);

                        // Parse and save data
                        $em->persist($section);
                        $em->flush();

                        if (isset($itemSection['widgets'])) {
                            // Parse widgets and insert data
                            foreach ($itemSection['widgets'] as $itemWidget) {
                                $fullClassName = sprintf("App\Entity\%s", $itemWidget['entity']);
                                $fullClassNameTranslation = sprintf("App\Entity\%s", $itemWidget['entityTranslation']);

                                // Check exist class
                                if (!class_exists($fullClassName) || !class_exists($fullClassNameTranslation)) continue;

                                // Get widget by @machineName
                                $widget = $em->getRepository($fullClassName)->findOneBy(['machineName' => $itemWidget['machineName']]);
                                $hasWidget = empty($widget);

                                $widget = empty($widget) ? new $fullClassName : $widget;
                                $widget->setMachineName($itemWidget['machineName']);
                                $widget->setTemplate($itemWidget['template']);
                                $widget->setPageSection($section);

                                // Parse and save data
                                $em->persist($widget);
                                $em->flush();

                                // Check exist widget
                                if ($hasWidget) {
                                    // Parse all language and save widgets
                                    foreach ($languageHelper->getAllLanguage() as $language) {
                                        // Create new widget translation
                                        $widgetTranslation = new $fullClassNameTranslation;
                                        $widgetTranslation->setLanguage($language);
                                        $widgetTranslation->setPageWidget($widget);

                                        // Parse and save data
                                        $em->persist($widgetTranslation);
                                        $em->flush();
                                    }
                                }
                            }
                        }
                    }
                }

                $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
                return $this->redirectToRoute('dashboard_page_index');
            }
        }

        return $this->render('dashboard/page/create.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_page', [], 'messages')
        ]);
    }

    #[Route('/dashboard/page/{machineName}/edit-view', name: 'dashboard_page_edit_view')]
    public function editView(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, DefaultHelper $helper, $machineName, TranslatorInterface $translator): Response
    {
        // Init propertyAccess
        $accessor = PropertyAccess::createPropertyAccessor();

        // Check language
        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        // Get path to file
        $jsonParseTemplate = $helper->parsePageJsonFile($machineName);

        // Check if the fileJson exists
        if (!$jsonParseTemplate['success']) {
            $this->addFlash('danger', $jsonParseTemplate['message']);
            return $this->redirectToRoute('dashboard_page_index');
        }

        // Get template data
        $pageTemplate = $jsonParseTemplate['data'];

        // Create dynamic class
        $fullClassName = sprintf("App\Entity\%s", $pageTemplate['entity']);

        // Check exist class
        if (!class_exists($fullClassName)) {
            $this->addFlash('danger', $translator->trans('form.default.required', [], 'messages'));
            return $this->redirectToRoute('dashboard_page_index');
        }

        // Get page by @machineName
        $page = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

        if (null === $page) {
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_page_index');
        }

        // Check exist variables
        if (isset($pageTemplate['variables'])) {
            // Parse and set variables
            foreach ($pageTemplate['variables'] as $key => $variable) {
                // Create dynamic class
                $fullClassName = sprintf("App\Entity\%s", $pageTemplate['entity']);

                // Check exist class
                if (!class_exists($fullClassName)) continue;

                // Get page by @machineName
                $page = $em->getRepository($fullClassName)->findOneBy(['machineName' => $pageTemplate['machineName']]);

                // Check and set value
                if (isset($page)) {
                    $pageTemplate['variables'][$key]['value'] = $accessor->getValue($page, $variable['field']);
                }
            }
        }

        // Check exist sections
        if (isset($pageTemplate['sections'])) {
            // Parse and set sections
            foreach ($pageTemplate['sections'] as $secKey => $sectionData) {
                // Create dynamic class
                $fullClassName = sprintf("App\Entity\%s", $sectionData['entity']);

                // Check exist class
                if (!class_exists($fullClassName)) continue;

                // Get section by @machineName
                $section = $em->getRepository($fullClassName)->findOneBy(['machineName' => $sectionData['machineName']]);

                // Check exist section variables
                if (isset($sectionData['variables'])) {
                    foreach ($sectionData['variables'] as $varKey => $variable) {
                        $pageTemplate['sections'][$secKey]['variables'][$varKey]['value'] = $accessor->getValue($section, $variable['field']);
                    }
                }

                // Check exist section widgets
                if (isset($sectionData['widgets'])) {
                    // Parse section widgets and set data
                    foreach ($sectionData['widgets'] as $widKey => $widgetData) {
                        // Create dynamic class
                        $fullClassName = sprintf("App\Entity\%s", $widgetData['entity']);
                        $fullTransClassName = sprintf("App\Entity\%s", $widgetData['entityTranslation']);

                        // Check exist class
                        if (!class_exists($fullClassName) || !class_exists($fullTransClassName)) continue;

                        // Get widget by @machineName
                        $widget = $em->getRepository($fullClassName)->findOneBy(['machineName' => $widgetData['machineName']]);

                        if (isset($widget)) {
                            // Get widgetTranslation by @page and @lang
                            $widgetTranslation = $em->getRepository($fullTransClassName)->findOneBy([
                                'pageWidget' => $widget,
                                'language' => $language
                            ]);

                            // Check exist section widget translation
                            if (isset($widgetTranslation)) {
                                // Parse widget variables and set data
                                foreach ($widgetData['variables'] as $varWidKey => $variableWidget) {
                                    $source = $pageTemplate['sections'][$secKey]['widgets'][$widKey]['variables'][$varWidKey]['isTranslated'] ? $widgetTranslation : $widget;
                                    $pageTemplate['sections'][$secKey]['widgets'][$widKey]['variables'][$varWidKey]['value'] = $accessor->getValue($source, $variableWidget['field']);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->render('dashboard/page/edit.html.twig', [
            'pageTitle' => $translator->trans('controller.edit_page', [], 'messages'),
            'entity' => $page,
            'template' => $pageTemplate
        ]);
    }

    #[Route('/dashboard/page/{locale}/edit-save', name: 'dashboard_page_edit_save')]
    public function editSave(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $locale, TranslatorInterface $translator): RedirectResponse
    {
        // Init propertyAccess
        $accessor = PropertyAccess::createPropertyAccessor();

        // Check language
        $language = $languageHelper->getLanguageByLocale($locale);

        // Form data
        $formData = $request->get('fields');
        $files = $request->files->get('fields');

        if ($request->isMethod('POST') && isset($formData)) {
            if (isset($files)) {
                // Parse and save file
                foreach ($files as $className => $variables) {
                    // Create dynamic class
                    $fullClassName = sprintf("App\Entity\%s", $className);

                    // Check exist class
                    if (!class_exists($fullClassName)) continue;

                    // Check exist variables
                    if (isset($variables)) {
                        // Parse variables
                        foreach ($variables as $machineName => $fields) {
                            // Find item by @machineName
                            $entity = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

                            // Check exist data
                            if (isset($entity)) {
                                // Parse fields and upload files
                                foreach ($fields as $name => $value) {
                                    if (isset($value)) {
                                        // Upload file
                                        $uploadFile = $fileUploader->uploadFile(
                                            $value,
                                            null,
                                            $this->getParameter('app_page_path') . strtolower($className) . '/'
                                        );

                                        // Check success uploaded
                                        if ($uploadFile['success']) {
                                            $accessor->setValue($entity, $name, $uploadFile['fileName']);

                                            // Persist data and save
                                            $em->persist($entity);
                                            $em->flush();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Parse and save fields
            foreach ($formData as $className => $variables) {
                // Create dynamic class
                $fullClassName = sprintf("App\Entity\%s", $className);

                // Check exist class
                if (!class_exists($fullClassName)) continue;

                // Check exist variables
                if (isset($variables)) {
                    foreach ($variables as $machineName => $fields) {
                        // Find item by @machineName
                        $entity = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);
                        if (isset($entity) && isset($fields)) {
                            foreach ($fields as $name => $value) {
                                if (!is_array($value)) {
                                    $accessor->setValue($entity, $name, $value);
                                } else {
                                    // Create dynamic class
                                    $fullTransClassName = sprintf("%sTranslation", $fullClassName);

                                    // Check exist class
                                    if (!class_exists($fullClassName)) continue;

                                    $translation = $entity->getPageWidgetTranslations()->filter(function ($widgetTranslation) use ($language, $fullTransClassName) {
                                        return $widgetTranslation instanceof $fullTransClassName && $widgetTranslation->getLanguage() === $language;
                                    })->first();

                                    // Check exist translation
                                    if (isset($translation)) {
                                        foreach ($value as $key => $item) {
                                            $accessor->setValue($translation, $key, $item);
                                        }
                                    }
                                }
                            }

                            $em->persist($entity);
                            $em->flush();
                        }
                    }
                }
            }

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_updated', [], 'messages'));
            return $this->redirectToRoute('dashboard_page_index');
        }

        return $this->redirectToRoute('dashboard_page_index');
    }

    #[Route('/dashboard/page/{machineName}/delete', name: 'dashboard_page_delete')]
    public function delete(EntityManagerInterface $em, $machineName, TranslatorInterface $translator): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => $machineName]);

        if (null === $page) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            $this->redirectToRoute('dashboard_page_index');
        }

        $page->setDeletedAt(new \DateTime());
        $em->persist($page);
        //$em->remove($page);
        $em->flush();

        $this->addFlash('success', $translator->trans('controller.success_delete', [], 'messages'));
        return $this->redirectToRoute('dashboard_page_index');
    }
}

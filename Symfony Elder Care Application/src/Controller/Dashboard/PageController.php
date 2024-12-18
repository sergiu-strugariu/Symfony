<?php

namespace App\Controller\Dashboard;

use App\Entity\Page;
use App\Entity\PageWidget;
use App\Form\PageType;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\FileUploaderLocal;
use App\Helper\PageBuilderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/dashboard/secure/page", name="dashboard_pages_index")
     */
    public function index(): Response
    {
        return $this->render('dashboard/page/index.html.twig');
    }

    /**
     * @Route("/dashboard/secure/page/create", name="dashboard_page_create")
     */
    public function create(Request $request, FileUploaderLocal $fileUploader, DefaultHelper $defaultHelper, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            $uploadFile = $fileUploader->uploadFile($file, $form, '/pages/', 'file', 'config', false);

            if ($uploadFile['success']) {
                $jsonParseTemplate = $defaultHelper->parsePageJsonFile(str_replace('.json', '', $uploadFile['fileName']));

                if (!$jsonParseTemplate['success']) {
                    $this->addFlash('danger', $jsonParseTemplate['message']);
                    return $this->redirectToRoute('dashboard_pages_index');
                }

                $pageTemplate = $jsonParseTemplate['data'];

                $machineName = $pageTemplate['machineName'];
                $fullClassName = sprintf("App\Entity\%s", $pageTemplate['entity']);

                if (!class_exists($fullClassName)) {
                    $this->addFlash('danger', 'Oops! Ceva nu a mers bine');
                    return $this->redirectToRoute('dashboard_pages_index');
                }

                $page = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

                $page = empty($page) ? new $fullClassName : $page;
                $page->setName($pageTemplate['name']);
                $page->setUrl($pageTemplate['url']);
                $page->setMachineName($machineName);
                $page->setClasses($pageTemplate['classes']);

                $em->persist($page);
                $em->flush();

                if (isset($pageTemplate['sections'])) {
                    foreach ($pageTemplate['sections'] as $itemSection) {
                        $fullClassName = sprintf("App\Entity\%s", $itemSection['entity']);

                        if (!class_exists($fullClassName)) continue;

                        $section = $em->getRepository($fullClassName)->findOneBy(['machineName' => $itemSection['machineName']]);

                        $section = empty($section) ? new $fullClassName : $section;
                        $section->setName($itemSection['name']);
                        $section->setMachineName($itemSection['machineName']);
                        $section->setTemplate($itemSection['template']);
                        $section->setWeight($itemSection['weight']);
                        $section->setPage($page);

                        $em->persist($section);
                        $em->flush();

                        if (isset($itemSection['widgets'])) {
                            foreach ($itemSection['widgets'] as $itemWidget) {

                                $fullClassName = sprintf("App\Entity\%s", $itemWidget['entity']);

                                if (!class_exists($fullClassName)) continue;
                                $widget = $em->getRepository($fullClassName)->findOneBy(['machineName' => $itemWidget['machineName']]);

                                $widget = empty($widget) ? new $fullClassName : $widget;
                                $widget->setMachineName($itemWidget['machineName']);
                                $widget->setTemplate($itemWidget['template']);
                                $widget->setWeight($itemWidget['weight']);
                                $widget->setPageSection($section);

                                $em->persist($widget);
                                $em->flush();
                            }
                        }
                    }
                }

                $this->addFlash('success', 'Felicitări, ați adăugat cu succes un element nou');
                return $this->redirectToRoute('dashboard_pages_index');
            }
        }

        return $this->render('dashboard/page/management.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/dashboard/secure/page/{machineName}/edit", name="dashboard_page_edit")
     */
    public function edit(DefaultHelper $defaultHelper, EntityManagerInterface $em, $machineName): Response
    {
        $weightField = PageBuilderHelper::WEIGHT;
        $accessor = PropertyAccess::createPropertyAccessor();

        $jsonParseTemplate = $defaultHelper->parsePageJsonFile($machineName);

        if (!$jsonParseTemplate['success']) {
            $this->addFlash('danger', $jsonParseTemplate['message']);
            return $this->redirectToRoute('dashboard_pages_index');
        }

        $pageTemplate = $jsonParseTemplate['data'];

        $fullClassName = sprintf("App\Entity\%s", $pageTemplate['entity']);

        if (!class_exists($fullClassName)) {
            $this->addFlash('danger', 'Oops! Ceva nu a mers bine.');
            return $this->redirectToRoute('dashboard_pages_index');
        }

        $page = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

        if (null === $page) {
            $this->addFlash('danger', 'Acest conținut nu există');
            return $this->redirectToRoute('dashboard_pages_index');
        }

        if (isset($pageTemplate['variables'])) {
            foreach ($pageTemplate['variables'] as $key => $variable) {
                $fullClassName = sprintf("App\Entity\%s", $pageTemplate['entity']);

                if (!class_exists($fullClassName)) continue;

                $page = $em->getRepository($fullClassName)->findOneBy(['machineName' => $pageTemplate['machineName']]);

                if (isset($page)) {
                    $pageTemplate['variables'][$key]['value'] = $accessor->getValue($page, $variable['field']);
                }
            }
        }

        if (isset($pageTemplate['sections'])) {
            foreach ($pageTemplate['sections'] as $secKey => $sectionData) {
                $fullClassName = sprintf("App\Entity\%s", $sectionData['entity']);

                if (!class_exists($fullClassName)) continue;

                $section = $em->getRepository($fullClassName)->findOneBy([
                    'machineName' => $sectionData['machineName']
                ]);

                // Parse and set weight values
                if (isset($sectionData[$weightField])) {
                    $pageTemplate['sections'][$secKey][$weightField] = $accessor->getValue($section, $weightField);
                }

                // Parse and set variables values
                if (isset($sectionData['variables'])) {
                    foreach ($sectionData['variables'] as $varKey => $variable) {
                        $pageTemplate['sections'][$secKey]['variables'][$varKey]['value'] = $accessor->getValue($section, $variable['field']);
                    }
                }

                if (isset($sectionData['widgets'])) {
                    foreach ($sectionData['widgets'] as $widKey => $widgetData) {
                        $fullClassName = sprintf("App\Entity\%s", $widgetData['entity']);

                        if (!class_exists($fullClassName)) continue;

                        $widget = $em->getRepository($fullClassName)->findOneBy(['machineName' => $widgetData['machineName']]);

                        if (isset($widget)) {
                            // Parse and set weight values
                            if (isset($widgetData[$weightField])) {
                                $pageTemplate['sections'][$secKey]['widgets'][$widKey][$weightField] = $accessor->getValue($widget, $weightField);
                            }

                            foreach ($widgetData['variables'] as $varWidKey => $variableWidget) {
                                $pageTemplate['sections'][$secKey]['widgets'][$widKey]['variables'][$varWidKey]['value'] = $accessor->getValue($widget, $variableWidget['field']);
                            }
                        }
                    }
                }
            }
        }

        return $this->render('dashboard/page/management.html.twig', [
            'pageTitle' => 'Pagina de editare',
            'entity' => $page,
            'template' => $pageTemplate
        ]);
    }

    /**
     * @Route("/dashboard/secure/page/save", name="dashboard_page_save")
     */
    public function save(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $formData = $request->get('fields');
        $files = $request->files->get('fields');

        if ($request->isMethod('POST')) {

            if (!empty($files)) {
                foreach ($files as $className => $variables) {
                    // Generate the fully qualified class name
                    $fullClassName = sprintf("App\Entity\%s", $className);

                    // Skip processing if the class does not exist
                    if (!class_exists($fullClassName)) continue;

                    if (!empty($variables)) {
                        foreach ($variables as $machineName => $fields) {
                            // Fetch the entity using the machineName
                            $entity = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

                            // Process only if the entity exists
                            if ($entity) {
                                foreach ($fields as $field => $value) {
                                    // Skip if the field value is not set
                                    if (empty($value)) continue;

                                    // Handle gallery files
                                    if ($field === PageBuilderHelper::GALLERY['field']) {
                                        $galleryFiles = [];
                                        $formGalleryFiles = [];

                                        // Determine the upload path based on the entity type
                                        $uploadPath = $entity instanceof PageWidget
                                            ? $this->getParameter('app_page_widget_gallery_path')
                                            : $this->getParameter('app_page_section_gallery_path');

                                        foreach ($value as $key => $file) {
                                            if (!empty($file['fileName'])) {
                                                // Upload new gallery file
                                                $uploadFile = $fileUploader->uploadFile(
                                                    $file['fileName'],
                                                    null,
                                                    $uploadPath
                                                );

                                                if ($uploadFile['success']) {
                                                    $galleryFiles[] = [
                                                        'fileName' => $uploadFile['fileName'],
                                                        'title' => $formData[$className][$machineName][$field][$key]['title'] ?? '',
                                                        'weight' => $formData[$className][$machineName][$field][$key]['weight'] ?? ''
                                                    ];
                                                }
                                            } else {
                                                // Use existing gallery file
                                                $formGalleryFiles[] = [
                                                    'fileName' => $accessor->getValue($entity, $field)[$key]['fileName'] ?? '',
                                                    'title' => $formData[$className][$machineName][$field][$key]['title'] ?? '',
                                                    'weight' => $formData[$className][$machineName][$field][$key]['weight'] ?? ''
                                                ];
                                            }
                                        }

                                        $fileNameFiles = array_column($formGalleryFiles, 'fileName');

                                        // Compare galleries and oldGalleryFiles for fileName
                                        $missingFiles = array_filter($entity->getGalleries(), function ($item) use ($fileNameFiles) {
                                            return !in_array($item['fileName'], $fileNameFiles);
                                        });

                                        // Parse missing files and remove
                                        foreach ($missingFiles as $file) {
                                            $fileUploader->removeFile($uploadPath, $file['fileName']);
                                        }

                                        // Update the entity's gallery
                                        $entity->setGalleries(array_merge($formGalleryFiles, $galleryFiles));
                                    } else {
                                        // Check exist old file
                                        $oldFile = $accessor->getValue($entity, $field);

                                        // FilePath
                                        $uploadPath = $entity instanceof PageWidget
                                            ? $this->getParameter('app_page_widget_path')
                                            : $this->getParameter('app_page_section_path');

                                        // Upload and set non-gallery files
                                        $uploadedFile = $fileUploader->uploadFile($value, null, $uploadPath);

                                        if ($uploadedFile['success']) {
                                            if ($oldFile !== null) {
                                                $fileUploader->removeFile($uploadPath, $oldFile);
                                            }
                                            $accessor->setValue($entity, $field, $uploadedFile['fileName']);
                                        }
                                    }
                                }

                                // Persist and save the entity
                                $em->persist($entity);
                                $em->flush();
                            }
                        }
                    }
                }
            }

            if (!empty($formData)) {
                foreach ($formData as $className => $variables) {
                    $fullClassName = sprintf("App\Entity\%s", $className);

                    // Skip if class doesn't exist
                    if (!class_exists($fullClassName)) continue;

                    foreach ($variables ?? [] as $machineName => $fields) {
                        // Retrieve the entity
                        $entity = $em->getRepository($fullClassName)->findOneBy(['machineName' => $machineName]);

                        if ($entity && $fields) {
                            foreach ($fields as $name => $value) {
                                if ($name === PageBuilderHelper::GALLERY['field']) continue;
                                $accessor->setValue($entity, $name, $value);
                            }

                            $em->persist($entity);
                            $em->flush();
                        }
                    }
                }
            }

            $this->addFlash('success', 'Acest conținut a fost actualizat cu succes.');
            return $this->redirectToRoute('dashboard_pages_index');
        }

        return $this->redirectToRoute('dashboard_pages_index');
    }

    /**
     * @Route("/dashboard/secure/page/{machineName}/delete", name="dashboard_page_delete")
     */
    public function delete(EntityManagerInterface $em, $machineName): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => $machineName]);

        if (null === $page) {
            $this->addFlash('danger', 'Acest conținut nu există.');
            $this->redirectToRoute('dashboard_pages_index');
        }

        $page->setDeletedAt(new \DateTime());
        $em->persist($page);
        $em->flush();

        $this->addFlash('success', 'Acest conținut a fost șters cu succes.');
        return $this->redirectToRoute('dashboard_pages_index');
    }
}
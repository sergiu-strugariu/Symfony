<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Helper\FileUploader;
use App\Helper\DefaultHelper;
use App\Helper\LanguageHelper;
use App\Helper\MailHelper;
use App\Helper\MailchimpAPIHelper;
use App\Repository\EducationRepository;
use App\Validator\ValidationConstraints;
use GuzzleHttp\Exception\ClientException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxController extends AbstractController
{
    #[Route('/ajax/search', name: 'ajax_search', methods: ['GET'])]
    public function ajaxSearch(Request $request, EducationRepository $repository, LanguageHelper $languageHelper): JsonResponse
    {
        $query = $request->query->get('query', '');
        $locale = $request->getLocale();
        $limit = $request->query->get('limit', 4);
        $page = $request->query->get('page', 1);

        $language = $languageHelper->getLanguageByLocale($locale);
        $offset = ($page - 1) * $limit;

        $data = $repository->searchEducation($query, $language, $limit, $offset);
        $total = $repository->searchEducation($query, $language, $limit, $offset, true);
        $totalPages = ceil($total / $limit);

        // Prepare the response data
        $responseData = [
            'results' => $data,
            'total' => $total,
            'limit' => $limit,
            'selectedPage' => $page,
            'totalPages' => $totalPages,
        ];

        return new JsonResponse($responseData);
    }

    #[Route('/ajax/update-user-password', name: 'ajax_update_password', methods: ['POST'])]
    public function ajaxUpdatePassword(Request $request, EntityManagerInterface $em, TranslatorInterface $translator, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('authentication.user_not_logged')
            ]);
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $newPassword = $form->get('newPassword')->getData();

                $user->setPassword(
                    $hasher->hashPassword($user, $newPassword)
                );

                $em->persist($user);
                $em->flush();

                return new JsonResponse([
                    'status' => 'success',
                    'message' => $translator->trans('authentication.account.success')
                ]);
            } else {
                $errors = $form->getErrors(true, true);

                $messages = [];
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return new JsonResponse([
                    'status' => 'error',
                    'message' => implode(',', $messages)
                ]);
            }
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => $translator->trans("authentication.account.default_password_error")
        ]);
    }

    #[Route('/ajax/update-user-picture', name: 'ajax_update_user_picture', methods: ['POST'])]
    public function ajaxUpdateUserPicture(Request $request, FileUploader $fileUploader, TranslatorInterface $translator, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('authentication.user_not_logged')
            ]);
        }

        $file = $request->files->get('fileCv');

        if ($file) {
            $uploadFile = $fileUploader->uploadFile(
                $file,
                [],
                $this->getParameter('app_user_photo_path')
            );

            if ($uploadFile['success']) {
                $user->setImageName($uploadFile['fileName']);
                $em->persist($user);
                $em->flush();

                return new JsonResponse([
                    'status' => 'success',
                    'image' => $this->getParameter('app_user_photo_path') . $uploadFile['fileName'],
                ]);
            }

            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('authentication.account.default_password_error')
            ]);
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => $translator->trans('authentication.account.default_password_error')
        ]);
    }

    #[Route('/ajax/update-user-details', name: 'ajax_update_user_details', methods: ['POST'])]
    public function ajaxUpdateUserDetails(Request $request, EntityManagerInterface $em, TranslatorInterface $translator, MailHelper $mailHelper, DefaultHelper $defaultHelper, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('authentication.user_not_logged')
            ]);
        }

        $key = $request->get('name');
        $value = $request->get('value');

        if ($key == 'email' && $value != $user->getEmail()) {
            $existingUser = $em->getRepository(User::class)->findOneBy([
                'email' => $value
            ]);

            if ($existingUser) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $translator->trans('authentication.account.user_already_exists')
                ]);
            }

            $errors = $validator->validate($value, new Email());
            if ($errors->count()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $translator->trans('common.not_valid.email')
                ]);
            }

            // Generate hash by request data
            $hash = $defaultHelper->generateHash($value);

            $sent = $mailHelper->sendMail(
                    $value, 
                    $translator->trans('mails.email_update.title'),
                    'frontend/emails/update-email.html.twig', 
                    [
                        'title' => $translator->trans('mails.email_update.title'),
                        'user' => $user,
                        'confirmEmailUpdateUrl' => $this->generateUrl('app_confirm_email_update', [
                            'token' => $hash
                                ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ]
            );

            if (!$sent) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $translator->trans('authentication.account.default_password_error')
                ]);
            }
            
            $user->setTempEmail($value);
            $user->setConfirmationToken($hash);

            $em->persist($user);
            $em->flush();
            
            return new JsonResponse([
                'status' => 'success',
                'message' => $translator->trans('account.details.confirm_email_update')
            ]);
        }

        $constraintsClass = new ValidationConstraints($translator);
        $constraints = $constraintsClass->getConstraints();

        if (!array_key_exists($key, $constraints)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid key.'
            ]);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($value, $constraints[$key]);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse([
                'status' => 'error',
                'message' => $errors,
            ]);
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($user, $key, $value);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => $translator->trans('authentication.account.success')
        ]);
    }

    #[Route('/ajax/update-user-company', name: 'ajax_update_company', methods: ['POST'])]
    public function ajaxUpdateCompany(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $data = $request->request->all();

        $constraintsClass = new ValidationConstraints($translator);
        $constraints = $constraintsClass->getCompanyConstraints();

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $constraints)) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $translator->trans('common.not_valid.key')
                ]);
            }

            $validator = Validation::createValidator();
            $violations = $validator->validate($value, $constraints[$key]);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $errors,
                ]);
            }

            $accessor = PropertyAccess::createPropertyAccessor();
            $accessor->setValue($user, $key, $value);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => $translator->trans('authentication.account.success')
        ]);
    }
    
    #[Route('/ajax/subscribe/member', name: 'ajax_subscribe_member')]
    public function ajaxSubscribeMember(Request $request, MailchimpAPIHelper $mailChimp, TranslatorInterface $translator): JsonResponse
    {
        $email = $request->get('email');
        
        if (null === $email) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('authentification.not_valid.email')
            ]);
        }
        
        try {
            $response = $mailChimp->addListMember($email, 'pending');
        } catch (ClientException $ex) {
            $contents = $ex->getResponse()->getBody()->getContents();
            $errorResponse = json_decode($contents, true);
            
            if (JSON_ERROR_NONE !== json_last_error()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $translator->trans('newsletter.errors.default')
                ]);
            }
            
            if (isset($errorResponse['title'])){
               switch ($errorResponse['title']) {
                case 'Member Exists': 
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => $translator->trans('newsletter.errors.user_exists')
                    ]);
                    break;
                case 'Invalid Resource':
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => $translator->trans('newsletter.errors.email_not_valid')
                    ]);
                    break;
                default:
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => $translator->trans('newsletter.errors.default')
                    ]);
                    break;
                }
            }
            
            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('newsletter.errors.default')
            ]);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $translator->trans('newsletter.errors.default')
            ]);
        }
        
        return new JsonResponse([
            'status' => 'success',
            'message' => $translator->trans('newsletter.errors.success')
        ]);
    }
}

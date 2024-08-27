<?php

namespace App\Form\Type;

use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForgotPasswordType extends AbstractType
{
    private UserRepository $userRepository;
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Assert\Email([
                        'message' => 'common.not_valid_email'
                    ]),
                    new Assert\Callback([$this, 'validateEmailAndInterval']),
                ]
            ]);
    }


    /**
     * @param $value
     * @param ExecutionContextInterface $context
     * @return void
     * @throws NonUniqueResultException
     */
    public function validateEmailAndInterval($value, ExecutionContextInterface $context): void
    {
        $user = $this->userRepository->findOneBy(['email' => $value]);

        if (null !== $user) {
            if ($user->getPasswordRequestedAt() !== null && $user->getPasswordRequestedAt() > (new \DateTime())->modify("-2 hours")) {
                $context->buildViolation($this->translator->trans('mails.email_notifications.already_sent'))
                        ->atPath('email')
                        ->addViolation();
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}

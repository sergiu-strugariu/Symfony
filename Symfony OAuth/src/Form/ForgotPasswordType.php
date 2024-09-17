<?php

namespace App\Form;

use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ForgotPasswordType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'This field is required'
                    ]),
                    new Email([
                        'message' => 'The email is invalid.'
                    ]),
                    new Callback([$this, 'validateEmailAndInterval']),
                ]
            ])
        ;
    }

    public function validateEmailAndInterval($value, ExecutionContextInterface $context): void
    {
        $user = $this->userRepository->findOneBy(['email' => $value]);

        if (null !== $user) {
            if ($user->getPasswordRequestedAt() !== null && $user->getPasswordRequestedAt() > (new \DateTime())->modify("-2 hours")) {
                $context->buildViolation('The mail was already requested.')
                    ->atPath('email')
                    ->addViolation();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}

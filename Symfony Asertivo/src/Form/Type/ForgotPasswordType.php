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

class ForgotPasswordType extends AbstractType
{
    private $userRepository;

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
                    new Assert\NotBlank([
                        'message' => 'Please enter your email address'
                    ]),
                    new Assert\Email([
                        'message' => 'Please enter a valid email address'
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

        if (!$user) {
            $context->buildViolation('The email address "' . $value . '" doesn\'t exist')
                ->atPath('email')
                ->addViolation();
        }

        if (isset($user) && $user->getPasswordRequestedAt() !== null && $user->getPasswordRequestedAt() > (new \DateTime())->modify("-2 hours")) {
            $context->buildViolation("Ai trimis deja un email pentru resetarea parolei, te rog incearca mai tarziu")
                ->atPath('email')
                ->addViolation();
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
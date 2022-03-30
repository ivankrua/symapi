<?php

namespace App\Form;


use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SignUpType.
 */
class SignUpType extends ApiBaseType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Email(['message' => 'email.is_invalid']),
                    ],
                ]
            )
            ->add(
                'password',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            );
    }
}

<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class IcepayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('merchant_id', TextType::class, [
                'label' => 'icepay.merchant_id',
                'constraints' => [ new NotBlank([
                    'message' => 'icepay.merchant_id.not_blank',
                    'groups' => 'sylius',
                ])
            ]])
            ->add('secret', TextType::class, [
                'label' => 'icepay.secret',
                'constraints' => [ new NotBlank([
                    'message' => 'icepay.secret.not_blank',
                    'groups' => 'sylius',
                ])
            ]])
        ;
    }
}

<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\UserBundle\Form\Provider\PasswordTooltipProvider;
use Oro\Bundle\UserBundle\Form\Provider\PasswordFieldOptionsProvider;

class SetPasswordType extends AbstractType
{
    /** @var PasswordTooltipProvider */
    protected $passwordTooltip;

    /** @var PasswordFieldOptionsProvider */
    protected $optionsProvider;

    public function __construct(PasswordFieldOptionsProvider $optionsProvider)
    {
        $this->optionsProvider = $optionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'password', $this->optionsProvider->getOptions(
            [
                'required' => true,
                'label' => 'oro.user.new_password.label',
                'attr' => $this->optionsProvider->getSuggestPasswordOptions(),
            ]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_set_password';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound'        => true,
            'csrf_protection' => true,
        ]);
    }
}

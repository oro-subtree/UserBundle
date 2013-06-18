<?php
namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'width' => '400px',
                    'placeholder' => 'Choose a user...',
                    'result_template_twig' => 'OroUserBundle:Js:userResult.html.twig',
                    'selection_template_twig' => 'OroUserBundle:Js:userSelection.html.twig'
                ),
                'autocomplete_alias' => 'users',
                'route_name' => 'oro_user_autocomplete'
            )
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_select';
    }
}

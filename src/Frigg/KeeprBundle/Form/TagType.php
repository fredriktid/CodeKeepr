<?php

namespace Frigg\KeeprBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TagType.
 */
class TagType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'autocomplete ui-widget',
                    'data-type' => 'tag',
                ],
            ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Frigg\KeeprBundle\Entity\Tag',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'frigg_keeprbundle_tag';
    }
}

<?php

namespace Frigg\KeeprBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PostType
 * @package Frigg\KeeprBundle\Form
 */
class PostType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('topic')
            ->add('content')
            ->add('private')
            ->add('Tags', 'collection', [
                'type' => new TagType(),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => false,
            ])
            ->add('Language')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Frigg\KeeprBundle\Entity\Post'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'frigg_keeprbundle_post';
    }
}

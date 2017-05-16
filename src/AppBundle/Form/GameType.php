<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use AppBundle\Entity\Game;

class GameType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('startDate', 'datetime', array(
                'date_format' => 'yyyy/MM/dd HH:mm',
                'widget' => 'single_text',
            ))
            ->add('endDate', 'datetime', array(
                'date_format' => 'yyyy/MM/dd HH:mm',
                'widget' => 'single_text',
            ))
            ->add('visibility', null, array(
                'required' => false,
                'empty_data' => Game::VISIBILITY_PUBLIC
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Game',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
    }
}

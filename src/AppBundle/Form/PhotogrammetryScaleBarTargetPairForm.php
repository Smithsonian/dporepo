<?php
// src/AppBundle/Form/PhotogrammetryScaleBarTargetPairForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PhotogrammetryScaleBarTargetPairForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('parent_photogrammetry_scale_bar_repository_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('target_type', null, array(
                'label' => 'Target Type',
                'required' => true,
              ))
            ->add('target_pair_1_of_2', null, array(
                'label' => 'Target Pair 1 of 2',
                'required' => false,
              ))
            ->add('target_pair_2_of_2', null, array(
                'label' => 'Target Pair 2 of 2',
                'required' => false,
              ))
            ->add('distance', null, array(
                'label' => 'Distance',
                'required' => false,
              ))
            ->add('units', null, array(
                'label' => 'Units',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
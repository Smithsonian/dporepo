<?php
// src/AppBundle/Form/PhotogrammetryScaleBarTargetPairForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PhotogrammetryScaleBarTargetPairForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('photogrammetry_scale_bar_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('target_type', null, array(
                'label' => 'Target Type (convert to controlled vocabulary)',
                'required' => true,
              ))
            ->add('target_type', ChoiceType::class, array(
                'label' => 'Target Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['target_type_options'],
                // Selected option
                'data' => $data['target_type'],
                'attr' => array('class' => 'default-chosen-select'),
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
            ->add('units', ChoiceType::class, array(
                'label' => 'Units',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['unit_options'],
                // Selected option
                'data' => $data['units'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
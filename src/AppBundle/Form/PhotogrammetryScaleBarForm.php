<?php
// src/AppBundle/Form/PhotogrammetryScaleBarForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PhotogrammetryScaleBarForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('capture_dataset_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('scale_bar_id', null, array(
                'label' => 'Scale Bar ID',
                'required' => true,
              ))
            ->add('scale_bar_manufacturer', null, array(
                'label' => 'Scale Bar Manufacturer',
                'required' => false,
              ))
            ->add('scale_bar_barcode_type', ChoiceType::class, array(
                'label' => 'Scale Bar Barcode Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['scale_bar_barcode_type_options'],
                // Selected option
                'data' => $data['scale_bar_barcode_type'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('scale_bar_target_pairs', null, array(
                'label' => 'Scale Bar Target Pairs',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
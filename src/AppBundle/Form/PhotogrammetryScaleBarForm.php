<?php
// src/AppBundle/Form/PhotogrammetryScaleBarForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PhotogrammetryScaleBarForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('parent_capture_dataset_repository_id', HiddenType::class, array(
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
            ->add('scale_bar_barcode_type', null, array(
                'label' => 'Scale Bar Barcode Type',
                'required' => false,
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
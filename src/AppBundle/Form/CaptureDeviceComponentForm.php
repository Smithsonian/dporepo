<?php
// src/AppBundle/Form/CaptureDeviceComponentForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDeviceComponentForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('capture_device_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('serial_number', null, array(
                'label' => 'Serial Number',
                'required' => true,
              ))
            ->add('capture_device_component_type', null, array(
                'label' => 'Capture Device Component Type',
                'required' => false,
              ))
            ->add('manufacturer', null, array(
                'label' => 'Manufacturer',
                'required' => false,
              ))
            ->add('model_name', null, array(
                'label' => 'Model Name',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
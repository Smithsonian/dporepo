<?php
// src/AppBundle/Form/CaptureDeviceForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDeviceForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('capture_data_element_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('calibration_file', null, array(
                'label' => 'Calibration File',
                'required' => true,
              ))
            ->add('capture_device_component_ids', null, array(
                'label' => 'Capture Device Component IDs',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
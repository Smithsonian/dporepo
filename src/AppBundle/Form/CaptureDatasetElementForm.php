<?php
// src/AppBundle/Form/CaptureDatasetElementForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDatasetElementForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('capture_device_configuration_id', null, array(
                'label' => 'Capture Device Configuration ID',
              ))
            ->add('capture_device_field_id', null, array(
                'label' => 'Capture Device Field ID',
              ))
            ->add('capture_sequence_number', null, array(
                'label' => 'Capture Sequence Number',
                'required' => true,
              ))
            ->add('cluster_position_field_id', null, array(
                'label' => 'Cluster Position Field ID',
              ))
            ->add('position_in_cluster_field_id', null, array(
                'label' => 'Position In Cluster Field ID',
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
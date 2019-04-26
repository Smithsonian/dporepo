<?php
// src/AppBundle/Form/CaptureDatasetElementForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDatasetElementForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];
        $disabled = $data['user_can_edit'] ? false : true;

        $builder
            ->add('capture_dataset_id', HiddenType::class, array(
              'required' => true,
            ))
            ->add('capture_device_configuration_id', null, array(
                'label' => 'Capture Device Configuration ID',
                'disabled' => $disabled,
              ))
            ->add('capture_device_field_id', null, array(
                'label' => 'Capture Device Field ID',
                'disabled' => $disabled,
              ))
            ->add('capture_sequence_number', null, array(
                'label' => 'Capture Sequence Number',
                'required' => true,
                'disabled' => $disabled,
              ))
            ->add('cluster_position_field_id', null, array(
                'label' => 'Cluster Position Field ID',
                'disabled' => $disabled,
              ))
            ->add('position_in_cluster_field_id', null, array(
                'label' => 'Position In Cluster Field ID',
                'disabled' => $disabled,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
                'disabled' => $disabled,
              ))
        ;
    }

}
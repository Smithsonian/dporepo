<?php
// src/AppBundle/Form/BatchProcessingForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BatchProcessingForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];
        $builder
            ->add('batch_processing_workflow', ChoiceType::class, array(
                'label' => 'Choose Workflow',
                'placeholder'=>'Choose Workflow',
                'required' => false,
                'choices' => $data['batch_processing_assests_guid_options'],
                // All options
                'choices' => $data['batch_processing_workflow_guid_options'],
                'data' => $data['batch_processing_workflow_guid_picker'],
                'attr' => array('class' => 'stakeholder-chosen-select'),
              ))
            ->add('batch_processing_assets', ChoiceType::class, array(
                'label' => 'Choose Assets for Batch Processing',
                'placeholder'=>'Choose Assets for Batch Processing',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                // All options
                'choices' => $data['batch_processing_assests_guid_options'],
                // Selected option
                'data' => $data['batch_processing_assests_guid_picker'],
                'attr' => array('class' => 'stakeholder-chosen-select'),
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Process Selected Assets',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
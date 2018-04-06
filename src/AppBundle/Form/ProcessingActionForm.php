<?php
// src/AppBundle/Form/ProcessingActionForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProcessingActionForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('parent_model_repository_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('preceding_processing_action_repository_id', null, array(
                'label' => 'Preceding Processing Action Repository ID',
                'required' => false,
              ))
            ->add('date_of_action', null, array(
                'label' => 'Date of Action',
                'required' => false,
              ))
            // TODO: hook-up to JSON schema
            ->add('action_method', ChoiceType::class, array(
                'label' => 'Action Method',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => array('align' => 1, 'mesh' => 2, 'cleanup' => 3, 'texture' => 4),
                // Selected option
                'data' => $data['action_method'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('software_used', null, array(
                'label' => 'Software Used',
                'required' => false,
              ))
            ->add('action_description', null, array(
                'label' => 'Action Description',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
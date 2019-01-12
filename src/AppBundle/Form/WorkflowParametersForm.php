<?php
// src/AppBundle/Form/WorkflowParamatersForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class WorkflowParametersForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
          ->add('point_of_contact', ChoiceType::class, array(
              'label' => 'Point of Contact',
              'required' => false,
              // All options
              'choices' => $data['point_of_contact_guid_options'],
              // Selected option
              'data' => $data['point_of_contact_guid_picker'],
              'attr' => array('class' => 'stakeholder-chosen-select'),
            ))
          ->add('uuid', HiddenType::class, array(
            'required' => true,
          ))
          ->add('recipe_id', HiddenType::class, array(
            'required' => true,
          ))
          ->add('save', SubmitType::class, array(
              'label' => 'Save and Review',
              'attr' => array('class' => 'btn btn-primary'),
            ))
        ;
    }

}
<?php
// src/AppBundle/Form/Project.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class Project extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('project_name', null, array(
                'label' => 'Project Name',
                'required' => true,
              ))
            ->add('stakeholder_label', Hiddentype::class, array(
            ))
            ->add('stakeholder_guid', Hiddentype::class, array(
            ))
            ->add('project_description', TextareaType::class, array(
                'label' => 'Project Description',
                'required' => false,
                'attr' => array('rows' => '10'),
              ))
            ->add('stakeholder_guid_picker', ChoiceType::class, array(
                'label' => 'Stakeholder',
                'required' => false,
                'placeholder' => 'Select SI Unit',
                // All options
                'choices' => $data['stakeholder_guid_options'],
                // Selected option
                'data' => $data['stakeholder_guid_picker'],
                'attr' => array('class' => 'stakeholder-chosen-select'),
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
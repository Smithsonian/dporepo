<?php
// src/AppBundle/Form/Subject.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Subject extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('subject_name', null, array(
                'label' => 'Subject Name',
                'required' => true,
              ))
            ->add('subject_guid', null, array(
                'label' => 'Subject GUID',
              ))
            ->add('location_information', null, array(
                'label' => 'Location Information',
              ))
            ->add('holding_entity_guid', null, array(
                'label' => 'Holding Entity GUID',
              ))
            ->add('subject_type_lookup_id', ChoiceType::class, array(
                'label' => 'Subject Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['subject_type_lookup_options'],
                // Selected option
                'data' => $data['subject_type_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('subject_holder_subject_id', null, array(
                'label' => 'Subject Holder Subject ID',
              ))
            ->add('subject_description', TextareaType::class, array(
                'label' => 'Subject Description',
                'attr' => array('rows' => '10'),
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
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

        $builder
            ->add('subject_name', null, array(
                'label' => 'Subject Name',
                'required' => true,
              ))
            ->add('subject_display_name', null, array(
                'label' => 'Subject Display Name',
                'required' => false,
              ))
            ->add('subject_guid', null, array(
                'label' => 'Subject GUID',
                'required' => true,
              ))
            ->add('holding_entity_guid', null, array(
                'label' => 'Holding Entity GUID',
                'required' => false,
              ))
            ->add('local_subject_id', null, array(
                'label' => 'Local Subject ID',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
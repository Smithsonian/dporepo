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
            ->add('subject_guid', null, array(
                'label' => 'Subject GUID',
              ))
            ->add('holding_entity_guid', null, array(
                'label' => 'Holding Entity GUID',
              ))
            ->add('local_subject_id', null, array(
                'label' => 'Local Subject ID',
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
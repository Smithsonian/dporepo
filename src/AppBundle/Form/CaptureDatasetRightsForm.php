<?php
// src/AppBundle/Form/CaptureDatasetRightsForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDatasetRightsForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('parent_capture_dataset_repository_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('data_rights_restriction', null, array(
                'label' => 'Data Rights Restriction',
                'required' => true,
              ))
            ->add('start_date', null, array(
                'label' => 'Start Date',
                'required' => false,
              ))
            ->add('end_date', null, array(
                'label' => 'End Date',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
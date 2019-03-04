<?php
// src/AppBundle/Form/CaptureDataFileForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDataFileForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('capture_data_element_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('capture_data_file_name', null, array(
                'label' => 'Capture Data File Name',
                'required' => true,
              ))
            ->add('capture_data_file_type', null, array(
                'label' => 'Capture Data File Type',
                'required' => false,
              ))
            ->add('is_compressed_multiple_files', null, array(
                'label' => 'Is Compressed Multiple Files',
                'required' => false,
              ))
            ->add('is_compressed_multiple_files', CheckboxType::class, array(
                'label' => 'Is Compressed Multiple Files',
                'required' => false,
                'data' => (bool)$data['is_compressed_multiple_files'],
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
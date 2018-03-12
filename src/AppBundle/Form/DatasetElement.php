<?php
// src/AppBundle/Form/DatasetElement.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DatasetElement extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('camera_id', null, array(
                'label' => 'Camera ID',
                'required' => true,
              ))
            ->add('camera_capture_position_id', null, array(
                'label' => 'Camera Capture Position',
              ))
            ->add('cluster_position_id', null, array(
                'label' => 'Cluster Position',
              ))
            ->add('calibration_object_type_id', ChoiceType::class, array(
                'label' => 'Calibration Object Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['calibration_object_type_options'],
                // Selected option
                'data' => $data['calibration_object_type_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('exif_data_placeholder', null, array(
                'label' => 'Exif Data Placeholder',
              ))
            ->add('camera_body', null, array(
                'label' => 'Camera Body',
              ))
            ->add('lens', null, array(
                'label' => 'Lens',
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
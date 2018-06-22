<?php
// src/AppBundle/Form/Dataset.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Dataset extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('parent_project_repository_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('parent_item_repository_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('capture_method', ChoiceType::class, array(
                'label' => 'Capture Method',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['capture_methods_lookup_options'],
                // Selected option
                'data' => $data['capture_method'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('capture_dataset_type', ChoiceType::class, array(
                'label' => 'Capture Dataset Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['dataset_types_lookup_options'],
                // Selected option
                'data' => $data['capture_dataset_type'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('capture_dataset_name', null, array(
                'label' => 'Capture Dataset Name',
                'required' => true,
              ))
            ->add('collected_by', null, array(
                'label' => 'Collected By',
                'required' => true,
              ))
            ->add('date_of_capture', null, array(
                'label' => 'Date of Capture',
                'required' => true,
              ))
            ->add('capture_dataset_description', TextareaType::class, array(
                'label' => 'Capture Dataset Description',
                'attr' => array('rows' => '10'),
                'required' => true,
              ))
            ->add('collection_notes', TextareaType::class, array(
                'label' => 'Collection Notes',
                'required' => false,
                'attr' => array('rows' => '10'),
              ))
            ->add('item_position_type', ChoiceType::class, array(
                'label' => 'Item Position Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['item_position_types_lookup_options'],
                // Selected option
                'data' => $data['item_position_type'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('positionally_matched_capture_datasets', null, array(
                'label' => 'Positionally Matched Capture Datasets',
                'required' => true,
              ))
            ->add('focus_type', ChoiceType::class, array(
                'label' => 'Focus Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['focus_types_lookup_options'],
                // Selected option
                'data' => $data['focus_type'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('light_source_type', ChoiceType::class, array(
                'label' => 'Light Source Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['light_source_types_lookup_options'],
                // Selected option
                'data' => $data['light_source_type'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('background_removal_method', ChoiceType::class, array(
                'label' => 'Background Removal Method',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['background_removal_methods_lookup_options'],
                // Selected option
                'data' => $data['background_removal_method'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('cluster_type', ChoiceType::class, array(
                'label' => 'Camera Cluster Type',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['camera_cluster_types_lookup_options'],
                // Selected option
                'data' => $data['cluster_type'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('cluster_geometry_field_id', null, array(
                'label' => 'Cluster Geometry Field ID',
                'required' => false,
              ))
            ->add('capture_dataset_guid', null, array(
                'label' => 'Capture Dataset GUID',
                'required' => true,
              ))
            ->add('capture_dataset_field_id', null, array(
                'label' => 'Capture Dataset Field ID',
                'required' => true,
              ))
            ->add('support_equipment', null, array(
                'label' => 'Support Equipment',
                'required' => false,
              ))
            ->add('item_position_field_id', null, array(
                'label' => 'Item Position Field ID',
                'required' => true,
              ))
            ->add('item_arrangement_field_id', null, array(
                'label' => 'Item Arrangement Field ID',
                'required' => true,
              ))
            ->add('resource_capture_datasets', null, array(
                'label' => 'Resource Capture Datasets',
                'required' => false,
              ))
            ->add('calibration_object_used', null, array(
                'label' => 'Calibration Object Used',
                'required' => false,
              ))
            ->add('calibration_object_used', ChoiceType::class, array(
                'label' => 'Calibration Object Used',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['calibration_object_type_options'],
                // Selected option
                'data' => $data['calibration_object_used'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('directory_path', null, array(
                'label' => 'Directory Path',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
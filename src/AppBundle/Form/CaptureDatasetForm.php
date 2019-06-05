<?php
// src/AppBundle/Form/Dataset.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CaptureDatasetForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('item_id', HiddenType::class, array(
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
                'required' => false,
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
                'required' => false,
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
                'attr' => array('placeholder' => 'Example: capture_dataset_directory_01'),
              ))
            ->add('api_publication_picker', ChoiceType::class, array(
              'label' => 'API Publication Status',
              'required' => false,
              'placeholder' => '--Inherit from item (' . $data['inherit_publication_default'] . ') --',
              // All options
              'choices' => $data['api_publication_options'],
              // Selected option
              'data' => $data['api_publication_picker'],
              'attr' => array('class' => 'publication-chosen-select'),
            ))

            ->add('api_access_model_face_count_id', ChoiceType::class, array(
              'label' => 'Face Count',
              'required' => false,
              'placeholder' => '--Inherit from subject--',
              // All options
              'choices' => $data['model_face_count_options'],
              // Selected option
              'data' => $data['api_access_model_face_count_id'],
              'attr' => array('class' => 'publication-chosen-select'),
            ))
            ->add('api_access_uv_map_size_id', ChoiceType::class, array(
              'label' => 'UV Map Size',
              'required' => false,
              'placeholder' => '--Inherit from subject--',
              // All options
              'choices' => $data['uv_map_size_options'],
              // Selected option
              'data' => $data['api_access_uv_map_size_id'],
              'attr' => array('class' => 'publication-chosen-select'),
            ))
            ->add('model_purpose_picker', ChoiceType::class, array(
              'label' => 'Published Content Types',
              'required' => false,
              'placeholder' => '--Inherit from subject--',
              // All options
              'choices' => $data['model_purpose_options'],
              // Selected option
              'data' => $data['model_purpose_picker'],
              'attr' => array('class' => 'publication-chosen-select'),
              'expanded' => true,
              'multiple' => true,
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
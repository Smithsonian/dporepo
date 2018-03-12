<?php
// src/AppBundle/Form/Dataset.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Dataset extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('capture_method_lookup_id', ChoiceType::class, array(
                'label' => 'Capture Method',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['capture_methods_lookup_options'],
                // Selected option
                'data' => $data['capture_method_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('dataset_type_lookup_id', ChoiceType::class, array(
                'label' => 'Dataset Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['dataset_types_lookup_options'],
                // Selected option
                'data' => $data['dataset_type_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('dataset_name', null, array(
                'label' => 'Dataset Name',
                'required' => true,
              ))
            ->add('collected_by', null, array(
                'label' => 'Collected By',
              ))
            ->add('collected_by_guid', null, array(
                'label' => 'Collected by Guid',
              ))
            ->add('date_of_capture', null, array(
                'label' => 'Date of Capture',
              ))
            ->add('dataset_description', TextareaType::class, array(
                'label' => 'Dataset Description',
                'attr' => array('rows' => '10'),
              ))
            ->add('dataset_collection_notes', TextareaType::class, array(
                'label' => 'Dataset Collection Notes',
                'attr' => array('rows' => '10'),
              ))
            ->add('item_position_type_lookup_id', ChoiceType::class, array(
                'label' => 'Item Position Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['item_position_types_lookup_options'],
                // Selected option
                'data' => $data['item_position_type_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('positionally_matched_sets_id', null, array(
                'label' => 'Positionally Matched Sets',
              ))
            ->add('motion_control', null, array(
                'label' => 'Motion Control',
              ))
            ->add('focus_lookup_id', ChoiceType::class, array(
                'label' => 'Focus Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['focus_types_lookup_options'],
                // Selected option
                'data' => $data['focus_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('light_source', null, array(
                'label' => 'Light Source',
              ))
            ->add('light_source_type_lookup_id', ChoiceType::class, array(
                'label' => 'Light Source Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['light_source_types_lookup_options'],
                // Selected option
                'data' => $data['light_source_type_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('scale_bars_used', null, array(
                'label' => 'Scale Bars Used',
              ))
            ->add('background_removal_method_lookup_id', ChoiceType::class, array(
                'label' => 'Background Removal Method',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['background_removal_methods_lookup_options'],
                // Selected option
                'data' => $data['background_removal_method_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('camera_cluster_type_lookup_id', ChoiceType::class, array(
                'label' => 'Camera Cluster Type',
                'placeholder' => 'Select',
                // All options
                'choices' => $data['camera_cluster_types_lookup_options'],
                // Selected option
                'data' => $data['camera_cluster_type_lookup_id'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('array_geometry_id', null, array(
                'label' => 'Array Geometry ID',
              ))
            
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
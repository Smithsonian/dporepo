<?php
// src/AppBundle/Form/ModelForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ModelForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('parent_capture_dataset_repository_id', HiddenType::class, array(
                'required' => true,
              ))
            ->add('model_guid', null, array(
                'label' => 'Model GUID',
                'required' => true,
              ))
            ->add('date_of_creation', null, array(
                'label' => 'Date Of Creation',
                'required' => false,
              ))
            ->add('model_file_type', null, array(
                'label' => 'Model File Type',
                'required' => false,
              ))
            ->add('derived_from', null, array(
                'label' => 'Derived From',
                'required' => false,
              ))
            // TODO: hook-up to JSON schema
            ->add('creation_method', ChoiceType::class, array(
                'label' => 'Creation Method',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => array('scan-to-mesh' => 1, 'CAD' => 2),
                // Selected option
                'data' => $data['creation_method'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            // TODO: hook-up to JSON schema
            ->add('model_modality', ChoiceType::class, array(
                'label' => 'Model Modality',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => array('point cloud' => 1, 'mesh' => 2),
                // Selected option
                'data' => $data['model_modality'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('units', ChoiceType::class, array(
                'label' => 'Units',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['unit_options'],
                // Selected option
                'data' => $data['units'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('is_watertight', CheckboxType::class, array(
                'label' => 'Is Watertight',
                'required' => false,
                'data' => (bool)$data['is_watertight'],
              ))
            // TODO: hook-up to JSON schema
            ->add('model_purpose', ChoiceType::class, array(
                'label' => 'Model Purpose',
                'required' => true,
                'placeholder' => 'Select',
                // All options
                'choices' => array('master' => 1, 'delivery web' => 2, 'delivery print' => 3, 'intermediate processing step' => 4),
                // Selected option
                'data' => $data['model_purpose'],
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('point_count', null, array(
                'label' => 'Point Count',
                'required' => false,
              ))
            ->add('has_normals', CheckboxType::class, array(
                'label' => 'Has Normals',
                'required' => false,
                'data' => (bool)$data['has_normals'],
              ))
            ->add('face_count', null, array(
                'label' => 'Face Count',
                'required' => false,
              ))
            ->add('vertices_count', null, array(
                'label' => 'Vertices Count',
                'required' => false,
              ))
            ->add('has_vertex_color', CheckboxType::class, array(
                'label' => 'Has Vertex Color',
                'required' => false,
                'data' => (bool)$data['has_vertex_color'],
              ))
            ->add('has_uv_space', CheckboxType::class, array(
                'label' => 'Has UV Space',
                'required' => false,
                'data' => (bool)$data['has_uv_space'],
              ))
            ->add('model_maps', null, array(
                'label' => 'Model Maps',
                'required' => false,
              ))
            ->add('file_path', null, array(
                'label' => 'File Path',
                'required' => false,
              ))
            ->add('file_checksum', null, array(
                'label' => 'File Checksum',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
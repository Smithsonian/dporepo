<?php
// src/AppBundle/Form/ModelForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('creation_method', null, array(
                'label' => 'Creation Method',
                'required' => false,
              ))
            ->add('model_modality', null, array(
                'label' => 'Model Modality',
                'required' => false,
              ))
            ->add('units', null, array(
                'label' => 'Units',
                'required' => false,
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
            ->add('is_watertight', null, array(
                'label' => 'Is Watertight',
                'required' => false,
              ))
            ->add('model_purpose', null, array(
                'label' => 'Model Purpose',
                'required' => false,
              ))
            ->add('point_count', null, array(
                'label' => 'Point Count',
                'required' => false,
              ))
            ->add('has_normals', null, array(
                'label' => 'Has Normals',
                'required' => false,
              ))
            ->add('face_count', null, array(
                'label' => 'Face Count',
                'required' => false,
              ))
            ->add('vertices_count', null, array(
                'label' => 'Vertices Count',
                'required' => false,
              ))
            ->add('has_vertex_color', null, array(
                'label' => 'Has Vertex Color',
                'required' => false,
              ))
            ->add('has_uv_space', null, array(
                'label' => 'Has UV Space',
                'required' => false,
              ))
            ->add('model_maps', null, array(
                'label' => 'Model Maps',
                'required' => false,
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
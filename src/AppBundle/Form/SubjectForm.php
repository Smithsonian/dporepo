<?php
// src/AppBundle/Form/Subject.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SubjectForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

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
                'label' => 'Holding Entity GUID (ISNI ID)',
                'required' => true,
                'attr' => array(
                  'placeholder' => 'Example: 0000000123642127',
                )
              ))
            ->add('local_subject_id', null, array(
                'label' => 'Local Subject ID',
                'required' => false,
              ))

            ->add('api_access_model_face_count_id', ChoiceType::class, array(
              'label' => 'Face Count',
              'required' => false,
              'placeholder' => '--not set--',
              // All options
              'choices' => $data['model_face_count_options'],
              // Selected option
              'data' => $data['api_access_model_face_count_id'],
              'attr' => array('class' => 'publication-chosen-select'),
            ))
            ->add('api_access_uv_map_size_id', ChoiceType::class, array(
              'label' => 'UV Map Size',
              'required' => false,
              'placeholder' => '--not set--',
              // All options
              'choices' => $data['uv_map_size_options'],
              // Selected option
              'data' => $data['api_access_uv_map_size_id'],
              'attr' => array('class' => 'publication-chosen-select'),
            ))
            ->add('model_purpose_picker', ChoiceType::class, array(
              'label' => 'Published Content Types',
              'required' => false,
              'placeholder' => '--not set--',
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
<?php
// src/AppBundle/Form/Item.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ItemForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
            ->add('item_guid', null, array(
                'label' => 'Item GUID',
                'required' => false,
              ))
            ->add('local_item_id', null, array(
                'label' => 'Local Item ID',
                'required' => false,
              ))
            ->add('item_description', null, array(
                'label' => 'Item Description',
                'required' => true
              ))
            ->add('item_type', ChoiceType::class, array(
                'label' => 'Item Type',
                'required' => false,
                'placeholder' => 'Select',
                // All options
                'choices' => $data['item_type_lookup_options'],
                // Selected option
                'data' => isset($data['item_type']) ? $data['item_type'] : null,
                'attr' => array('class' => 'default-chosen-select'),
              ))
            ->add('api_publication_picker', ChoiceType::class, array(
              'label' => 'API Publication Status',
              'required' => false,
              'placeholder' => '--Inherit from project (' . $data['inherit_publication_default'] . ') --',
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
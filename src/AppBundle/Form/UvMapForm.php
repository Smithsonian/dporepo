<?php
// src/AppBundle/Form/UvMapForm.php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class UvMapForm extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {

    $data = (array)$options['data'];

    $builder
      ->add('parent_capture_dataset_repository_id', HiddenType::class, array(
        'required' => true,
      ))
      // TODO: hook-up to JSON schema
      ->add('map_type', ChoiceType::class, array(
          'label' => 'Map Type',
          'required' => true,
          'placeholder' => 'Select',
          // All options
          'choices' => array('normal' => 1, 'ambient occlusion' => 2, 'photo texture' => 3, 'hole fill' => 4),
          // Selected option
          'data' => $data['map_type'],
          'attr' => array('class' => 'default-chosen-select'),
        ))
      ->add('map_file_type', null, array(
        'label' => 'Map File Type',
        'required' => false,
      ))
      ->add('map_size', null, array(
        'label' => 'Map Size',
        'required' => false,
      ))
      ->add('save', SubmitType::class, array(
        'label' => 'Save Edits',
        'attr' => array('class' => 'btn btn-primary'),
      ))
    ;
  }
}
<?php
// src/AppBundle/Form/UvMapForm.php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class UvMapForm extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('capture_dataset_repository_id', HiddenType::class, array(
        'required' => true,
      ))
      ->add('map_type', null, array(
        'label' => 'Map Type',
        'required' => true,
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
<?php
// src/AppBundle/Form/BackupForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BackupForm extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('save', SubmitType::class, array(
        'label' => 'Backup Database',
        'attr' => array('class' => 'btn btn-primary'),
      ))
    ;
  }

}
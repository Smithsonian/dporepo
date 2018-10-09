<?php
// src/AppBundle/Form/UserRole.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class UserRole extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

      $data = (array)$options['data'];

      $builder
        ->add('username_canonical', Hiddentype::class, array(
        ));

      // Stakeholders
      if(array_key_exists('stakeholders_array', $data) && is_array($data['stakeholders_array'])) {
        foreach($data['stakeholders_array'] as $p) {
          $choices_array[$p['name']] = $p['id'];
        }
      }
      $select_details = array(
        'choices'  => $choices_array,
        'expanded' => false,
        'multiple' => false,
        'required' => false,
      );
      if(array_key_exists('stakeholder_id', $data) && NULL !== $data['stakeholder_id']) {
        $select_details['data'] = $data['stakeholder_id'];
      }
      $builder->add('stakeholder_id', ChoiceType::class, $select_details);


      // Projects
      $choices_array = array();
      if(array_key_exists('projects_array', $data) && is_array($data['projects_array'])) {
        foreach($data['projects_array'] as $p) {
          $choices_array[$p['name']] = $p['id'];
        }
      }
      $select_details = array(
        'choices'  => $choices_array,
        'expanded' => false,
        'multiple' => false,
        'required' => false,
      );
      if(array_key_exists('project_id', $data) && NULL !== $data['project_id']) {
        $select_details['data'] = $data['project_id'];
      }
      $builder->add('project_id', ChoiceType::class, $select_details);

      // Roles
      $choices_array = array();
      if(array_key_exists('roles_array', $data) && is_array($data['roles_array'])) {
        foreach($data['roles_array'] as $p) {
          $choices_array[$p['name']] = $p['id'];
        }
      }
      $select_details = array(
        'choices'  => $choices_array,
        'expanded' => true,
        'multiple' => false,
      );
      if(array_key_exists('role_id', $data) && NULL !== $data['role_id']) {
        $select_details['data'] = $data['role_id'];
      }
      $builder->add('role_id', ChoiceType::class, $select_details);

      $builder->add('save', SubmitType::class, array(
        'label' => 'Save Edits',
        'attr' => array('class' => 'btn btn-primary'),
      ));

    }

}
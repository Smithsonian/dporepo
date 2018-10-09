<?php
// src/AppBundle/Form/Role.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class Role extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = (array)$options['data'];

        $builder
          ->add('rolename', null, array(
              'label' => 'Role Name',
              'required' => true,
            ))
          ->add('rolename_canonical', Hiddentype::class, array(
          ))
          ->add('role_description', TextareaType::class, array(
              'label' => 'Role Description',
              'required' => false,
              'attr' => array('rows' => '4'),
            )
          );

      $choices_array = array();
      $selected_array = array();
      if(array_key_exists('role_permissions', $data) && is_array($data['role_permissions'])) {
        foreach($data['role_permissions'] as $p) {
          $choices_array[$p['permission_name']] = $p['permission_id'];
          if(true == $p['selected']) {
            $selected_array[] = $p['permission_id'];
          }
        }
      }

      //unset($data['role_permissions']);
      if(count($choices_array) > 0) {
        $builder->add('role_permissions', ChoiceType::class, array(
          'choices'  => $choices_array,
          'data' => $selected_array,
          'expanded' => true,
          'multiple' => true,
        ));
      }

      $builder->add('save', SubmitType::class, array(
        'label' => 'Save Edits',
        'attr' => array('class' => 'btn btn-primary'),
      ));

    }

}
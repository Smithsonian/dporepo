<?php
// src/AppBundle/Form/UploadsParentPickerForm.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

// typeahead-bundle - https://github.com/lifo101/typeahead-bundle
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;

class UploadsParentPickerForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent_picker', TypeaheadType::class, array(
                'label' => false,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Begin typing a project or subject name',
                ),
                'class'  => null,
                'render' => 'project_name',
                'minLength' => 2,
                'route' => 'get_parent_records',
              ))
        ;
    }

}
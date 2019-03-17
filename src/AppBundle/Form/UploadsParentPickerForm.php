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
                'property' => 'uploads_parent_picker_form_parent_picker',
                'attr' => array(
                    'placeholder' => 'Begin typing a project name',
                ),
                'class'  => null,
                'render' => 'parent_record_name',
                'minLength' => 2,
                'items' => 50,
                'delay' => 50,
                // Hack - shouldn't need to pass the 'route' since a Custom Source Callback is being passed via 'source'.
                // https://github.com/lifo101/typeahead-bundle#custom-source-callback
                // Report this as a GitHub issue?
                // Not passing a route causes an error in the Twig template:
                // vendor/lifo/typeahead-bundle/Lifo/TypeaheadBundle/Resources/views/Form/typeahead.html.twig
                // The fix for the template would be:
                // 'data-url': url is defined ? url : '',
                'route' => 'get_parent_records',
                // 'source' => 'get_parent_records',
              ))
        ;
    }

}
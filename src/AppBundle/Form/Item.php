<?php
// src/AppBundle/Form/Item.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Item extends AbstractType
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
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
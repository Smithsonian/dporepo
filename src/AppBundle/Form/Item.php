<?php
// src/AppBundle/Form/Item.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Item extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('item_name', null, array(
                'label' => 'Item Name',
                'required' => true,
              ))
            ->add('subject_holder_item_id', null, array(
                'label' => 'Subject Holder Item ID',
              ))
            ->add('item_description', TextareaType::class, array(
                'label' => 'Item Description',
                'attr' => array('rows' => '10'),
              ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Edits',
                'attr' => array('class' => 'btn btn-primary'),
              ))
        ;
    }

}
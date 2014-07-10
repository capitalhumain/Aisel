<?php

/*
 * This file is part of the Aisel package.
 *
 * (c) Ivan Proskuryakov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aisel\OrderBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Sonata\AdminBundle\Validator\ErrorElement;

/**
 * Order CRUD configuration for Backend
 *
 * @author Ivan Proskoryakov <volgodark@gmail.com>
 */
class OrderAdmin extends Admin
{
    protected $orderManager;
    protected $baseRoutePattern = 'order';

    public function setManager($orderManager)
    {
        $this->orderManager = $orderManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
                ->assertNotBlank()
            ->end()
                ->with('content')
            ->assertNotBlank()
                ->end()
            ->with('metaUrl')
                ->assertNotBlank()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
            ->add('name', 'text', array('label' => 'Name', 'attr' => array()))
            ->add('sku', 'text', array('label' => 'Sku', 'attr' => array()))
            ->add('price', 'text', array('label' => 'Price', 'attr' => array()))
            ->add('priceSpecial', 'text', array('label' => 'Special Price', 'attr' => array()))
            ->add('priceSpecialFrom', 'datetime', array('label' => 'Special Price From', 'attr' => array()))
            ->add('priceSpecialTo', 'datetime', array('label' => 'Special Price To', 'attr' => array()))
            ->add('new', 'choice', array('choices' => array(
                '0' => 'Disabled',
                '1' => 'Enabled'),
                'label' => 'New', 'attr' => array()))
            ->add('newFrom', 'datetime', array('label' => 'New From', 'attr' => array()))
            ->add('newTo', 'datetime', array('label' => 'New To', 'attr' => array()))
            ->add('descriptionShort', 'ckeditor',
                array(
                    'label' => 'Short Description',
                    'required' => true,
                    'attr' => array('class' => 'field-content')
                ))
            ->add('description', 'ckeditor',
                array(
                    'label' => 'Description',
                    'required' => true,
                    'attr' => array('class' => 'field-content')
                ))
            ->add('status', 'choice', array('choices' => array(
                '0' => 'Disabled',
                '1' => 'Enabled'),
                'label' => 'Status', 'attr' => array()
            ))
            ->add('commentStatus', 'choice', array('choices' => array(
                '0' => 'Disabled',
                '1' => 'Enabled'),
                'label' => 'Comments', 'attr' => array()
            ))
            ->add('hidden', null, array('required' => false, 'label' => 'Hidden order'))

            ->with('Categories', array('description' => 'Select related categories'))
            ->add('categories', 'gedmotree', array('expanded' => true, 'multiple' => true,
                'class' => 'Aisel\CategoryBundle\Entity\Category',
            ))

            ->with('Meta', array('description' => 'Meta description for search engines'))
            ->add('metaUrl', 'text', array('label' => 'Url', 'help' => 'note: URL value must be unique'))
            ->add('metaTitle', 'text', array('label' => 'Title'))
            ->add('metaDescription', 'textarea', array('label' => 'Description'))
            ->add('metaKeywords', 'textarea', array('label' => 'Keywords'))
            ->end();

    }

    public function getFormTheme()
    {
        return array('AiselAdminBundle:Form:form_admin_fields.html.twig');
    }

//    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
//    {
//        $datagridMapper
//            ->add('title')
//            ->add('content')
//        ;
//    }

    public function prePersist($order)
    {
        $url = $order->getMetaUrl();
        $normalUrl = $this->orderManager->normalizeOrderUrl($url);

        $order->setMetaUrl($normalUrl);
        $order->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $order->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
    }

    public function preUpdate($order)
    {
        $url = $order->getMetaUrl();
        $orderId = $order->getId();
        $normalUrl = $this->orderManager->normalizeOrderUrl($url, $orderId);

        $order->setMetaUrl($normalUrl);
        $order->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('price')
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    ))
            );;
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Information')
            ->add('content')
            ->add('updatedAt')
            ->add('status', 'boolean')
            ->with('Categories')
            ->add('categories', 'tree')
            ->with('Meta')
            ->add('metaUrl')
            ->add('metaTitle')
            ->add('metaDescription')
            ->add('metaKeywords')
            ->with('General')
            ->add('id');
    }

    /**
     * {@inheritdoc}
     */
    public function toString($object)
    {
        return $object->getId() ? $object->getName() : $this->trans('link_add', array(), 'SonataAdminBundle');
    }
}
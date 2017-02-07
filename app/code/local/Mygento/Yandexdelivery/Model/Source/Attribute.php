<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_Attribute
{

    public function getAllOptions()
    {
        $attributes = Mage::getModel('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
        $attributes->setOrder('frontend_label', 'ASC');

        $_options = [];
        $_options[] = [
            'label' => Mage::helper('yandexdelivery')->__('No usage'),
            'value' => 0
        ];

        foreach ($attributes as $attr) {
            $label = $attr->getStoreLabel() ? $attr->getStoreLabel() : $attr->getFrontendLabel();
            if ('' != $label) {
                $_options[] = ['label' => $label, 'value' => $attr->getAttributeCode()];
            }
        }
        return $_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}

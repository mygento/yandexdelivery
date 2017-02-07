<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_Weight
{

    public function toOptionArray()
    {
        return [
                ['value' => '1000', 'label' => Mage::helper('yandexdelivery')->__('Gram')],
                ['value' => '1', 'label' => Mage::helper('yandexdelivery')->__('Kilogram')],
        ];
    }
}

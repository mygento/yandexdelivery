<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_Unit
{

    public function toOptionArray()
    {
        return [
                ['value' => '100', 'label' => Mage::helper('yandexdelivery')->__('Centimeter')],
                ['value' => '1', 'label' => Mage::helper('yandexdelivery')->__('Meter')],
        ];
    }
}

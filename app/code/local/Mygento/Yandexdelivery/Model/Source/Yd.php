<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_Yd
{

    public function toOptionArray()
    {
        return [
                ['value' => 0, 'label' => Mage::helper('yandexdelivery')->__('Directly to delivery service')],
                ['value' => 1, 'label' => Mage::helper('yandexdelivery')->__('Through root warehouse')],
        ];
    }
}

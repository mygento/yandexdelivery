<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_Payment
{

    public function toOptionArray()
    {
        return [
                ['value' => 1, 'label' => Mage::helper('yandexdelivery')->__('Cash')],
                ['value' => 3, 'label' => Mage::helper('yandexdelivery')->__('Pre-paid')],
        ];
    }
}

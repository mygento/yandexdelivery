<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_City
{

    public function toOptionArray()
    {
        return [
                ['value' => 'Москва', 'label' => Mage::helper('yandexdelivery')->__('Moscow')],
                ['value' => 'Санкт-Петербург', 'label' => Mage::helper('yandexdelivery')->__('Saint-Petersburg')],
        ];
    }
}

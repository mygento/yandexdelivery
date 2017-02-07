<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Source_Type
{

    public function toOptionArray()
    {
        return [
            ['value' => 'import', 'label' => Mage::helper('yandexdelivery')->__('Import')],
            ['value' => 'withdraw', 'label' => Mage::helper('yandexdelivery')->__('Withdraw')],
        ];
    }

    public function toOptionsArray()
    {
        $data = [];
        foreach ($this->toOptionArray() as $variant) {
            $data[$variant['value']] = $variant['label'];
        }
        return $data;
    }
}

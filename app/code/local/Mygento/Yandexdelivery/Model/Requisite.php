<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Requisite extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('yandexdelivery/requisite');
    }

    public function toOptionArray()
    {
        return Mage::helper('yandexdelivery')->toOptionArray('requisite');
    }
}

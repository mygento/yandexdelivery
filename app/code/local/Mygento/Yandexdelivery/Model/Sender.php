<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Sender extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('yandexdelivery/sender');
    }

    public function toOptionArray()
    {
        return Mage::helper('yandexdelivery')->toOptionArray('sender');
    }
}

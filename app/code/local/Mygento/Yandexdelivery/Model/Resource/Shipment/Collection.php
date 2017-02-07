<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Resource_Shipment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('yandexdelivery/shipment');
    }
}

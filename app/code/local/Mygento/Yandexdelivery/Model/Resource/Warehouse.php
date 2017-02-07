<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Resource_Warehouse extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('yandexdelivery/warehouse', 'id');
    }
}

<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Carrier_Pickup extends Mygento_Yandexdelivery_Model_Shipping
{

    protected $_subcode = 'pickup';

    public function getType()
    {
        return $this->_subcode;
    }
}

<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Block_Shipping extends Mage_Core_Block_Template
{

    public function _construct()
    {
        //защита от двойного вызова в одностраничном чекауте
        if (Mage::registry('yd_block_called')) {
            return '';
        }
        Mage::register('yd_block_called', true);
        $this->setTemplate('mygento/yandexdelivery/shipping.phtml');
    }
}

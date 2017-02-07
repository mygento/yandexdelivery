<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Block_Adminhtml_Shipment extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function _construct()
    {
        $this->_controller = 'adminhtml_shipment';
        $this->_blockGroup = 'yandexdelivery';
        $this->_headerText = Mage::helper('yandexdelivery')->__('Shipment Manager');
        $this->_addButtonLabel = Mage::helper('yandexdelivery')->__('Add shipment');
        parent::_construct();
    }

    public function _beforeToHtml()
    {
        $this->removeButton('add');
    }
}

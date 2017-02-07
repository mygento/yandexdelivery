<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Block_Adminhtml_Shipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function _construct()
    {
        parent::_construct();
        $this->setId('shipmentGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('yandexdelivery/shipment')->getCollection();
        $collection->getSelect()->join(
            array('table_alias' => Mage::getConfig()->getTablePrefix() . 'sales_flat_order'),
            'main_table.order_id = table_alias.entity_id',
            array('increment_id' => 'table_alias.increment_id')
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('yandexdelivery')->__('Order ID'),
            'align' => 'left',
            'index' => 'increment_id',
        ));

        $this->addColumn('yd_id', array(
            'header' => Mage::helper('yandexdelivery')->__('Yandex Delivery Id'),
            'align' => 'right',
            'width' => '30px',
            'index' => 'yd_id',
        ));

        return parent::_prepareColumns();
    }
}

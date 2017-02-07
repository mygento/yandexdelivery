<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
$installer = $this;

$installer->startSetup();


$installer->getConnection()->dropTable('yandexdelivery/sender');
$installer->getConnection()->dropTable('yandexdelivery/warehouse');
$installer->getConnection()->dropTable('yandexdelivery/requisite');
$installer->getConnection()->dropTable('yandexdelivery/shipment');

$sender_table = $installer->getConnection()
        ->newTable($installer->getTable('yandexdelivery/sender'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
                ], 'Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Name')
        ->addIndex($installer->getIdxName('yandexdelivery/sender', [
            'id'
        ]), [
    'id'
        ], [
    'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ]);

$warehouse_table = $installer->getConnection()
        ->newTable($installer->getTable('yandexdelivery/warehouse'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
                ], 'Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Name')
        ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 128, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Address')
        ->addIndex($installer->getIdxName('yandexdelivery/warehouse', [
            'id'
        ]), [
    'id'
        ], [
    'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ]);

$requisite_table = $installer->getConnection()
        ->newTable($installer->getTable('yandexdelivery/requisite'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
                ], 'Id')
        ->addColumn('legal_form', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Legal form')
        ->addColumn('legal_name', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Legal name')
        ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 128, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Address')
        ->addIndex($installer->getIdxName('yandexdelivery/requisite', [
            'id'
        ]), [
    'id'
        ], [
    'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ]);

$shipment_table = $installer->getConnection()
        ->newTable($installer->getTable('yandexdelivery/shipment'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            'auto_increment' => true,
                ], 'ID')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
                ], 'Id')
        ->addColumn('yd_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
                ], 'Yandex Delivery Id')
        ->addColumn('parcel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Parcel_ID')
        ->addColumn('requisite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Requisite ID')
        ->addColumn('warehouse_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Warehouse ID')
        ->addColumn('sender_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
            'unsigned' => true,
            'nullable' => true,
                ], 'Sender ID')
        ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
            'nullable' => true,
                ], 'Sending type')
        ->addIndex($installer->getIdxName('yandexdelivery/shipment', [
            'id'
        ]), [
    'id'
        ], [
    'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ]);

$installer->getConnection()->createTable($sender_table);
$installer->getConnection()->createTable($warehouse_table);
$installer->getConnection()->createTable($requisite_table);
$installer->getConnection()->createTable($shipment_table);

$installer->endSetup();

<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Observer extends Varien_Object
{

    public function handleSave()
    {

        if (Mage::getStoreConfig('carriers/yandexdelivery/active')) {
            //process keys
            $keys_data = Mage::getStoreConfig('carriers/yandexdelivery/keys');
            Mage::getModel('yandexdelivery/carrier')->processApiKeys($keys_data);

            //process ids
            $ids_data = Mage::getStoreConfig('carriers/yandexdelivery/ids');
            Mage::getModel('yandexdelivery/carrier')->processApiIds($ids_data);

            //очистка кешей
            if (Mage::getStoreConfig('carriers/yandexdelivery/flush')) {
                Mage::helper('yandexdelivery')->refreshAllCaches();
            }

            //currently unused
            //Mage::getModel('yandexdelivery/carrier')->getPaymentMethods();
        }
    }
}

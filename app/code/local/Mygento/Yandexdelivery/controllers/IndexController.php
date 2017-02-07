<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->getResponse()->setBody('Nope. Visit <a href="http://www.mygento.ru/">Magento development</a>');
    }

    public function jsonAction()
    {
        $callback = Mage::app()->getRequest()->getParam('callback');

        $results = Mage::getModel('yandexdelivery/carrier')->getAutocomplete(
            Mage::app()->getRequest()->getParam('search'),
            Mage::app()->getRequest()->getParam('type'),
            (Mage::app()->getRequest()->getParam('city') ? Mage::app()->getRequest()->getParam('city') : false)
        );

        $result = ['jsonresult' => $results];
        $this->getResponse()->setBody($callback . '(' . json_encode($result) . ')');
    }
}

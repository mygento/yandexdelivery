<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Adminhtml_Yandexdelivery_ApiController extends Mage_Adminhtml_Controller_Action
{

    public function sendAction()
    {

        if (!$this->getRequest()->isPost()) {
            Mage::getSingleton('adminhtml/session')->addError('Sending error');
            return $this->_redirectReferer();
        }

        $keys = ['orderid', 'type', 'warehouse', 'sender', 'requisite', 'date', 'street', 'house'];

        foreach ($keys as $key) {
            $tmp = Mage::app()->getRequest()->getParam($key);
            if (!$tmp) {
                $return = 'No ' . $key . ' in POST';
                return $this->getResponse()->setBody($return);
            }
        }

        $result = Mage::getModel('yandexdelivery/carrier')->createOrder(
            Mage::app()->getRequest()->getParam('orderid'),
            Mage::app()->getRequest()->getParam('warehouse'),
            Mage::app()->getRequest()->getParam('sender'),
            Mage::app()->getRequest()->getParam('requisite'),
            Mage::app()->getRequest()->getParam('date'),
            Mage::app()->getRequest()->getParam('street'),
            Mage::app()->getRequest()->getParam('house')
        );

        if ($result->status == "ok") {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('yandexdelivery')->__('Order template has been successfully created')
            );

            $shipment = Mage::getModel('yandexdelivery/shipment');

            $data = [
                'yd_id' => $result->data->order->id,
                'order_id' => Mage::app()->getRequest()->getParam('orderid'),
                'sender_id' => Mage::app()->getRequest()->getParam('sender'),
                'requisite_id' => Mage::app()->getRequest()->getParam('requisite'),
                'warehouse_id' => Mage::app()->getRequest()->getParam('warehouse'),
                'type' => Mage::app()->getRequest()->getParam('type')
            ];
            $shipment->setData($data);
            $shipment->save();

            //will auto-approve sended earlier order template
            $this->confirmOrder($result->data->order->id, Mage::app()->getRequest()->getParam('sender'), Mage::app()->getRequest()->getParam('type'));
            return $this->_redirectReferer();
        }
        $msg = '';
        foreach ($result->data->errors as $error) {
            $msg .= $error . '. ';
        }
        Mage::getSingleton('adminhtml/session')->addError($msg);
        $this->_redirectReferer();
    }

    public function deleteAction()
    {
        $order_id = Mage::app()->getRequest()->getParam('orderid');

        if (!$order_id) {
            $return = 'no order id';
            return $this->getResponse()->setBody($return);
        }

        $ydId = Mage::getModel('yandexdelivery/carrier')->getYdId($order_id);
        $result = Mage::getModel('yandexdelivery/carrier')->deleteOrder($ydId);

        if ($result->status == "ok") {
            $message = ['status' => "ok"];
        } else {
            $errors = (array) $result->data->errors;
            $error_text = '';
            foreach ($errors as $key => $value) {
                $error_text .= $key . " - " . $value . "\n";
            }
            $message = ['error' => true, 'message' => $error_text];
        }
        $this->getResponse()->setBody(json_encode($message));
    }

    public function confirmAction()
    {
        $orderId = Mage::app()->getRequest()->getParam('orderid');

        if (!$orderId) {
            $return = 'no order id';
            $this->getResponse()->setBody($return);
            return;
        }

        $yandexShipment = Mage::getModel('yandexdelivery/carrier')->getShipment($orderId);

        $message = $this->confirmOrder($yandexShipment->getYdId(), $yandexShipment->getSenderId(), $yandexShipment->getType(), false);

        $this->getResponse()->setBody(json_encode($message));
    }

    private function confirmOrder($ydId, $senderId, $type, $showAdmin = true)
    {

        $result = Mage::getModel('yandexdelivery/carrier')->confirmOrders($ydId, $senderId, $type);

        if (!isset($result->data->result) || count($result->data->result->error) != 0 || count($result->data->errors)) {
            $msg = Mage::helper('yandexdelivery')->__('Order was not confirmed');

            if (isset($result->data->errors)) {
                $msg .= ':';
                foreach ((array) $result->data->errors as $error) {
                    $msg .= ' ' . $error;
                }
            }
            if ($showAdmin) {
                Mage::getSingleton('adminhtml/session')->addError($msg);
                return;
            }
            return ['error' => true, 'message' => $msg];
        }
        $msg = Mage::helper('yandexdelivery')->__('Order template has been successfully confirmed');
        if ($showAdmin) {
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            return;
        }
        return ['status' => $msg];
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/yandexdelivery/api');
    }

    public function labelAction()
    {
        $order_id = Mage::app()->getRequest()->getParam('orderid');

        if (!$order_id) {
            $return = 'no order id';
            return $this->getResponse()->setBody($return);
        }

        $ydId = Mage::getModel('yandexdelivery/carrier')->getYdId($order_id);

        $result = Mage::getModel('yandexdelivery/carrier')->getSenderOrderLabel($ydId);

        if ($result->status == "ok") {
            $data = base64_decode($result->data);
            return $this->_prepareDownloadResponse('yandexdelivery_' . $ydId . '.pdf', $data, 'application/pdf');
        }
        $this->getResponse()->setBody(Mage::helper('yandexdelivery')->__('Error getting order PDF'));
    }

    public function updatetion()
    {
        $orderId = Mage::app()->getRequest()->getParam('orderid');

        if (!$orderId) {
            $return = 'no order id';
            $this->getResponse()->setBody($return);
            return;
        }

        $yandexShipment = Mage::getModel('yandexdelivery/carrier')->getShipment($orderId);

        Mage::getModel('yandexdelivery/carrier')->updateOrder($orderId, $warehouseId, $senderId, $requisiteId);
    }
}

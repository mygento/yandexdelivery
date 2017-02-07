<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Shipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_name = 'yandexdelivery';
    protected $_isFixed = false;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        Varien_Profiler::start($this->_name . '_collect_rate');

        if (!$this->getType()) {
            Mage::helper($this->_name)->addLog('Calc -> no code');
            Varien_Profiler::stop($this->_name . '_collect_rate');
            return false;
        }
        $type = $this->getType();

        $valid = $this->validateRequest($request);
        if ($valid !== true) {
            return $valid;
        }

        $city = mb_convert_case(trim($request->getDestCity()), MB_CASE_TITLE, "UTF-8");

        if ($this->getConfig('onlyone')) {
            Mage::helper($this->_name)->addLog('Calc -> only one standart item');
            $weight = round($this->getConfig('oneweight') * 1000 / $this->getConfig('weightunit'), 3);
            $dimensions = Mage::helper($this->_name)->getStandardSizes();
        } else {
            $quote = $this->getQuote();
            $cartItems = $quote->getAllVisibleItems();

            Mage::helper($this->_name)->addLog('Found ' . count($cartItems) . ' items in quote');
            Mage::helper($this->_name)->addLog('Calc -> every item in cart');

            $processed_dimensions = Mage::helper($this->_name)->getSizes($cartItems, true);
            $weight = $processed_dimensions['weight'];

            $dimensions = Mage::helper($this->_name)->dimenAlgo($processed_dimensions['dimensions']);
        }

        $data = [
            'client_id' => $this->getConfig('client_id'),
            'sender_id' => $this->getConfig('sender'),
            'city_from' => $this->getConfig('homecity'),
            'city_to' => $city,
            'weight' => $weight,
            'total_cost' => $request->getPackageValue(),
            'order_cost' => $request->getPackageValue(),
            'payment_method' => $this->getConfig('payment_type'),
            'height' => $dimensions['C'],
            'width' => $dimensions['B'],
            'length' => $dimensions['A'],
            'to_yd_warehouse' => $this->getConfig('yd_warehouse'),
            'assessed_value' => $request->getPackageValue(),
            'index_city' => $request->getDestPostcode(),
            'delivery_type' => $type
        ];

        $result = Mage::getModel('shipping/rate_result');

        Mage::helper('yandexdelivery')->__('Will calc type -> ' . $type);

        Varien_Profiler::start($this->_name . '_request');
        $response = Mage::getModel($this->_name . '/carrier')->searchDeliveryList($data);
        Varien_Profiler::stop($this->_name . '_request');

        if ($response->status != "ok") {
            $errors = (array) $response->data->errors;
            $error_text = Mage::helper('yandexdelivery')->__('Error:') . '&nbsp;';
            foreach ($errors as $key => $value) {
                $error_text .= $key . "&nbsp;&mdash;&nbsp;" . $value . "\n";
            }
            return $this->returnError($error_text);
        }

        $this->processType($response, $type, $request, $result);

        Mage::helper($this->_name)->addLog('Calculating ended');

        Varien_Profiler::stop($this->_name . '_collect_rate');
        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_name => $this->getConfigData('name'));
    }

    /**
     * Validate shipping request before processing
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    private function validateRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfig('active')) {
            return false;
        }

        Mage::helper($this->_name)->addLog('Started calculating');

        if (strlen($request->getDestCity()) <= 2) {
            Mage::helper($this->_name)->addLog('City strlen <= 2, aborting ...');
            return false;
        }

        if (0 >= $request->getPackageWeight()) {
            return $this->returnError('Zero weight');
        }

        return true;
    }

    private function returnError($message)
    {
        Mage::helper($this->_name)->addLog($message);
        if ($this->getConfig('debug')) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_name);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage(Mage::helper($this->_name)->__($message));
            return $error;
        }
        return false;
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function isCityRequired()
    {
        return true;
    }

    public function getFormBlock()
    {
        return 'Mygento_Yandexdelivery_Block_Shipping';
    }

    private function processType($response, $type, $request, $result)
    {

        foreach ($response->data as $carrier_offer) {
            $price = $request->getFreeShipping() ? 0 : $carrier_offer->cost;
            switch ($carrier_offer->type) {
                case "TODOOR":
                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_name . '_' . $type);
                    $method->setCarrierTitle($this->getTypeTitle($type));
                    $method->setMethod($carrier_offer->tariffId . '_' . $carrier_offer->direction . '_' . $carrier_offer->delivery->id);
                    $method->setMethodTitle($carrier_offer->delivery->name);
                    $method->setPrice($price);
                    $method->setCost($price);
                    Mage::dispatchEvent('yandexdelivery_rate_result', ['method' => $method, 'request' => $request]);
                    $result->append($method);
                    break;
                case 'PICKUP':
                    foreach ($carrier_offer->pickupPoints as $point) {
                        $method = Mage::getModel('shipping/rate_result_method');
                        $method->setCarrier($this->_name . '_' . $type);
                        $method->setCarrierTitle($this->getTypeTitle($type));
                        $method->setMethod($carrier_offer->tariffId . '_' . $carrier_offer->direction . '_' . $carrier_offer->delivery->id . '_' . $point->id);
                        $method->setMethodTitle($carrier_offer->delivery->name . ', ' . $point->name);
                        $method->setPrice($price);
                        $method->setCost($price);
                        Mage::dispatchEvent('yandexdelivery_rate_result', ['method' => $method, 'request' => $request]);
                        $result->append($method);
                    }
                    break;
                case 'POST':
                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_name . '_' . $type);
                    $method->setCarrierTitle($this->getTypeTitle($type));
                    $method->setMethod($carrier_offer->tariffId . '_' . $carrier_offer->direction . '_' . $carrier_offer->delivery->id);
                    $method->setMethodTitle($carrier_offer->delivery->name . ', ' . $carrier_offer->tariffName);
                    $method->setPrice($price);
                    $method->setCost($price);
                    Mage::dispatchEvent('yandexdelivery_rate_result', ['method' => $method, 'request' => $request]);
                    $result->append($method);
                    break;
                default:
                    break;
            }
        }
    }

    protected function getTypeTitle($type)
    {
        return Mage::getStoreConfig('carriers/' . $this->_name . '_' . $type . '/name');
    }

    public function getConfig($path)
    {
        return Mage::helper($this->_name)->getConfigData($path);
    }

    protected function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}

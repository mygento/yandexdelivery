<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_name = 'yandexdelivery';

    public function addLog($text)
    {
        if ($this->getConfigData('debug')) {
            Mage::log($text, null, $this->_name . '.log', true);
        }
    }

    public function requestApiPost($url, $data, $sign = false, $method = false)
    {
        $this->addLog('Request to: ' . $url);

        if ($sign) {
            $data = $this->sign($data, $method);
        }

        // @codingStandardsIgnoreStart
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 1);

        $result = curl_exec($curl_handle);

        if ($result === false) {
            $this->addLog('Request error:');
            $this->addLog(curl_error($ch));
        }

        curl_close($curl_handle);
        // @codingStandardsIgnoreEnd

        $this->addLog('Request result:');
        $this->addLog($result);

        return $result;
    }

    public function getConfigData($path)
    {
        return Mage::getStoreConfig('carriers/' . $this->_name . '/' . $path);
    }

    /**
     * формирование подписи запроса
     * @param array $data
     * @param string $method
     * @return array
     */
    private function sign($data, $method)
    {
        $data['client_id'] = $this->getConfigData('client_id');

        $secretKey = '';
        $keys = array_keys($data);
        sort($keys);

        foreach ($keys as $key) {
            if (!is_array($data[$key])) {
                $secretKey .= $data[$key];
            } else {
                $subkeys = array_keys($data[$key]);
                sort($subkeys);
                foreach ($subkeys as $subkey) {
                    if (!is_array($data[$key][$subkey])) {
                        $secretKey .= $data[$key][$subkey];
                    } else {
                        $subsubkeys = array_keys($data[$key][$subkey]);
                        sort($subsubkeys);
                        foreach ($subsubkeys as $subsubkey) {
                            if (!is_array($data[$key][$subkey][$subsubkey])) {
                                $secretKey .= $data[$key][$subkey][$subsubkey];
                            }
                        }
                    }
                }
            }
        }

        $preparedData = $secretKey . $this->getConfigData('' . $method);
        $data['secret_key'] = md5($preparedData);

        $this->addLog($data);
        return $data;
    }

    public function setConfigData($key, $value)
    {
        Mage::getModel('core/config')->saveConfig('carriers/' . $this->_name . '/' . $key, $value);
    }

    public function refreshAllCaches()
    {
        try {
            $allTypes = Mage::app()->useCache();
            foreach ($allTypes as $type) {
                Mage::app()->getCacheInstance()->cleanType($type);
            }
        } catch (Exception $e) {
            $this->addLog($e->getMessage());
        }
    }

    /**
     * алгоритм расчета суммарных габаритов всех товаров
     * @param array $dimensions
     * @return array
     */
    public function dimenAlgo(array $dimensions)
    {
        $this->addLog('Array before dimension sorting');
        $this->addLog($dimensions);

        $dim = [];
        $result = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
        ];
        foreach ($dimensions as $d) {
            if ($this->isValidDimensionArr($d)) {
                rsort($d);
                $dim[] = $d;
            }
        }

        foreach ($dim as $d) {
            ($d[0] > $result['A']) ? $result['A'] = $d[0] : '';
            ($d[1] > $result['B']) ? $result['B'] = $d[1] : '';
            $result['C'] += $d[2];
        }

        $this->addLog('Array after dimension sorting');
        $this->addLog($result);

        return $result;
    }

    private function isValidDimensionArr($arr)
    {
        if (is_array($arr) and 3 == sizeof($arr)) {
            foreach ($arr as $a) {
                if ((!is_int($a) and ! is_float($a)) or $a < 0.1) {
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    public function isShippedBy($order)
    {
        if (strpos($order->getShippingMethod(), 'yandexdelivery') !== false) {
            return true;
        }
        return false;
    }

    /**
     * получение габаритов и веса товаров
     * @param type $items
     * @param type $is_quote
     * @return array
     */
    public function getSizes($items, $is_quote)
    {
        $temporary_dimensions = [];
        $weight = 0;

        foreach ($items as $_item) {
            if ($_item->getProduct() instanceof Mage_Catalog_Model_Product) {
                $product = Mage::getModel('catalog/product')->load($_item->getProductId());

                if ($is_quote) {
                    $qty = $_item->getQty();
                } else {
                    $qty = $_item->getQtyOrdered();
                }

                $itemweight = round($this->getAttributeValue('weight', $product) * $qty * 1000 / $this->getConfigData('weightunit'), 3);

                if ($itemweight == 0) {
                    $itemweight = $_item->getWeight() * $qty / $this->getConfigData('weightunit');
                }

                $weight += $itemweight;

                $itemlength = round($this->getAttributeValue('length', $product) * 100 / $this->getConfigData('sizeunit'), 3);
                $itemwidth = round($this->getAttributeValue('width', $product) * 100 / $this->getConfigData('sizeunit'), 3);
                $itemheight = round($this->getAttributeValue('height', $product) * 100 / $this->getConfigData('sizeunit'), 3);

                for ($i = 1; $i <= $qty; $i++) {
                    array_push($temporary_dimensions, ['L' => $itemlength, 'W' => $itemwidth, 'H' => $itemheight]);
                }
            }
        }

        return [
            'dimensions' => $temporary_dimensions,
            'weight' => $weight
        ];
    }

    private function getAttributeValue($param, $product)
    {
        $attribute = $this->getConfigData('item' . $param);
        if ('0' != $attribute) {
            $attribute_mode = Mage::getModel('catalog/product')->getResource()->getAttribute($attribute)->getFrontendInput();
            if ('select' == $attribute_mode) {
                $value = $product->getAttributeText($attribute);
                Mage::helper($this->_name)->addLog('attribute ' . $attribute . ' is select with value -> ' . $value);
            } else {
                $value = $product->getData($attribute);
            }
        } else {
            $value = $this->getConfigData('fone' . $param);
        }
        return round($value, 0);
    }

    /**
     * получение стандартных габаритов посылки
     * @return array
     */
    public function getStandardSizes()
    {

        return [
            'A' => round($this->getConfigData('onelength') * 100 / $this->getConfigData('sizeunit'), 3),
            'B' => round($this->getConfigData('onewidth') * 100 / $this->getConfigData('sizeunit'), 3),
            'C' => round($this->getConfigData('oneheight') * 100 / $this->getConfigData('sizeunit'), 3)
        ];
    }

    public function toOptionArray($model)
    {
        $data_array = [];
        $model = Mage::getModel('yandexdelivery/' . $model);
        $data = $model->getCollection()->getData();
        foreach ($data as $track) {
            array_push($data_array, [
                'value' => $track['id'],
                'label' => (isset($track['name']) ? $track['name'] : $track['legal_name'])
            ]);
        }
        return $data_array;
    }

    public function toOptionsArray($model)
    {
        $data_array = [];
        $model = Mage::getModel('yandexdelivery/' . $model);
        $data = $model->getCollection()->getData();
        foreach ($data as $track) {
            $data_array[$track['id']] = (isset($track['name']) ? $track['name'] : $track['legal_name']);
        }
        return $data_array;
    }

    public function normalizePhone($phone)
    {
        $phone = trim($phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = preg_replace('/\s+/', '', $phone);
        static $filter = null;
        if (is_null($filter)) {
            $filter = new Zend_Filter_Digits();
        }
        $num = $filter->filter($phone);
        if (strlen($num) == 11) {
            $phone = '7' . substr($num, 1);
        } elseif (strlen($num) == 10) {
            $phone = '7' . $num;
        } else {
            $phone = $num;
        }
        return $phone;
    }
}

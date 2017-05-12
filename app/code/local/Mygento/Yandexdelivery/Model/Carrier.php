<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Model_Carrier {

    private $_name = 'yandexdelivery';
    private $_url = 'https://delivery.yandex.ru/api/last/';

    public function processApiKeys($data_string) {
        $json = json_decode(trim($data_string));

        if (!$json) {
            return;
        }

        Mage::helper($this->_name)->addLog('Processed API keys');
        Mage::helper($this->_name)->addLog($json);

        foreach ($json as $key => $element) {
            Mage::helper($this->_name)->setConfigData(trim($key), trim($element));
        }
    }

    public function processApiIds($data_string) {

        $json = json_decode(trim($data_string));
        $res = Mage::getSingleton('core/resource');

        Mage::helper($this->_name)->addLog('Processed API Ids');
        Mage::helper($this->_name)->addLog($json);

        if (empty($json)) {
            return;
        }

        Mage::helper($this->_name)->setConfigData('client_id', $json->client->id);

        $adapter = $res->getConnection('yandexdelivery_write');
        //process senders
        foreach ($json->senders as $value) {
            $insertData = [
                'id' => $value->id,
                'name' => $value->name
            ];
            $adapter->insertOnDuplicate($res->getTableName('yandexdelivery/sender'), $insertData, array_keys($insertData));
        }
        //process warehouses
        foreach ($json->warehouses as $value) {
            $insertData = [
                'id' => $value->id,
                'name' => $value->name
            ];
            $adapter->insertOnDuplicate($res->getTableName('yandexdelivery/warehouse'), $insertData, array_keys($insertData));
        }
        //process requisites
        foreach ($json->requisites as $value) {
            $insertData = [
                'id' => $value->id
            ];
            $adapter->insertOnDuplicate($res->getTableName('yandexdelivery/requisite'), $insertData, array_keys($insertData));
        }

        $this->getSenderInfo();
        $this->getRequisiteInfo();
        $this->getWarehouseInfo();
    }

    /**
     * автоподсказки адресов
     * @param string $search
     * @param string $type
     * @param string $city
     * @return json
     */
    public function getAutocomplete($search, $type, $city = false) {
        $data = [
            'term' => $search,
            'type' => $type,
        ];

        if ($city) {
            $data['locality_name'] = $city;
        }

        $result = $this->sendRequest('autocomplete', $data, true);

        if ($result->status == 'ok') {
            return $result->data->suggestions;
        }
        return json_encode();
    }

    private function getSenderInfo() {
        $result = $this->sendRequest('getSenderInfo', [], true);

        if ($result->status == 'ok') {
            $sender = Mage::getModel('yandexdelivery/sender')->load($result->data->id);
            $sender->setName($result->data->field_name);
            $sender->save();
        }
    }

    private function getWarehouseInfo() {
        $data = [
            'warehouse_id' => Mage::helper($this->_name)->getConfigData('warehouse'),
        ];
        $result = $this->sendRequest('getWarehouseInfo', $data, true);

        if ($result->status == 'ok') {
            $sender = Mage::getModel('yandexdelivery/warehouse')->load($result->data->id);

            $sender->setName($result->data->field_name);
            $sender->setAddress($result->data->address);
            $sender->save();
        }
    }

    private function getRequisiteInfo() {
        $data = [
            'requisite_id' => Mage::getStoreConfig('carriers/' . $this->_name . '/requisite')
        ];

        $result = $this->sendRequest('getRequisiteInfo', $data, true);

        if ($result->status != "ok") {
            return;
        }
        foreach ($result->data->requisites as $requsite) {
            $requsiteId = $requsite->id;
            Mage::helper($this->_name)->addLog('FFF ' . $requsiteId);
            if (!$requsiteId) {
                continue;
            }
            $requisite = Mage::getModel('yandexdelivery/requisite')->load($requsiteId);
            $requisite->setLegalForm($requsite->legal_form);
            $requisite->setLegalName($requsite->legal_name);
            $address = implode(', ', (array) $requsite->address_legal);
            $requisite->setAddress($address);
            $requisite->save();
        }
    }

    public function createOrder($orderId, $warehouseId, $senderId, $requisiteId, $date, $street, $house) {
        //создание заявки на отправку заказа в ЯД
        $order = Mage::getModel('sales/order')->load($orderId);

        if (!$order || !$order->getId()) {
            return false;
        }

        $data = $this->getOrderData($order);

        $data ['order_requisite'] = $requisiteId;
        $data ['order_warehouse'] = $warehouseId;
        $data['order_shipment_date'] = date('Y-m-d', strtotime($date));
        $data['deliverypoint']['street'] = $street;
        $data['deliverypoint']['house'] = $house;

        return $this->sendRequest('createOrder', $data, true, $senderId);
    }

    public function getTypes($addHypes = false) {
        $types = ['todoor', 'post', 'pickup'];
        $enabled = [];
        foreach ($types as $type) {
            if (Mage::getStoreConfig('carriers/yandexdelivery_' . $type . '/active')) {
                if ($addHypes) {
                    $enabled[] = "'" . $type . "'";
                } else {
                    $enabled[] = $type;
                }
            }
        }
        return $enabled;
    }

    public function confirmOrders($id, $type) {
        $data = [
            'order_ids' => $id,
            'type' => $type,
            'shipment_date' => date('Y-m-d')
        ];

        $result = $this->sendRequest('confirmSenderOrders', $data, true);

        if (!isset($result->data->result) || count($result->data->result->error) != 0 || count($result->data->errors)) {
            return $result;
        }

        //set parcel_id to DB
        foreach ($result->data->result->success as $success) {
            $yd_id_collection = Mage::getModel('yandexdelivery/shipment')->getCollection();
            $yd_id_collection->addFieldToFilter('yd_id', ['in' => $success->orders]);
            foreach ($yd_id_collection as $ydShipment) {
                $ydShipment->setParcelId($success->parcel_id);
                $ydShipment->save();
            }
        }

        return $result;
    }

    /**
     * получаем id заказа в ЯД по ордеру
     * @param int $orderId
     * @return boolean
     */
    public function getYdId($orderId) {
        if ($this->getShipment($orderId)) {
            return $this->getShipment($orderId)->getYdId();
        }
        return false;
    }

    /**
     * получаем объект шипмента
     * @param int $orderId
     * @return boolean
     */
    public function getShipment($orderId) {
        $yd_id_collection = Mage::getModel('yandexdelivery/shipment')->getCollection();
        $yd_id_collection->addFieldToFilter('order_id', $orderId);
        $yd_id_collection->load();
        if ($yd_id_collection->getSize() > 0) {
            return $yd_id_collection->getFirstItem();
        }
        return false;
    }

    /**
     * получаем id заказа в ЯД по id shipment
     * @param array $ids
     * @return boolean
     */
    public function getYdIdsByIds($ids) {
        $yd_id_collection = Mage::getModel('yandexdelivery/shipment')->getCollection();
        $yd_id_collection->addFieldToFilter('id', ['in' => $ids]);
        $yd_id_collection->load();
        if ($yd_id_collection->getSize() > 0) {
            return $yd_id_collection->getColumnValues('yd_id');
        }
        return false;
    }

    public function deleteOrder($order_id) {
        $result = $this->sendRequest('deleteOrder', ['order_id' => $order_id], true);

        //удаляем запись в БД в случае успеха
        if ($result->status == "ok") {
            $yd_id_collection = Mage::getModel('yandexdelivery/shipment')->getCollection();
            $yd_id_collection->addFieldToFilter('yd_id', $order_id);
            $yd_id_collection->load();
            if ($yd_id_collection->getSize() > 0) {
                $item = $yd_id_collection->getFirstItem();
                $item->delete();
            }
        }
        return $result;
    }

    public function senderOrderStatuses($order_id) {
        return $this->sendRequest('getSenderOrderStatuses', ['order_id' => $order_id], true);
    }

    public function getSenderOrderLabel($order_id) {
        return $this->sendRequest('getSenderOrderLabel', ['order_id' => $order_id], true);
    }

    public function getSenderParcelDocs($parcel_id) {
        return $this->sendRequest('getSenderParcelDocs', ['parcel_id' => $parcel_id], true);
    }

    public function searchDeliveryList($data) {
        return $this->sendRequest('searchDeliveryList', $data, true);
    }

    public function statusOrder($order_id) {
        return $this->sendRequest('getOrderInfo', ['order_id' => $order_id], true);
    }

    protected function sendRequest($method, $data, $sign, $sender = null) {
        if ($sender) {
            $data['sender_id'] = $sender;
        } else {
            $data['sender_id'] = $this->getSender();
        }
        return json_decode(Mage::helper($this->_name)->requestApiPost($this->_url . $method, $data, $sign, $method));
    }

    protected function getOrderData($order) {

        if (Mage::getStoreConfig('carriers/' . $this->_name . '/onlyone')) {
            $weight = round(Mage::getStoreConfig('carriers/' . $this->_name . '/oneweight') * 1000 / Mage::getStoreConfig('carriers/' . $this->_name . '/weightunit'), 3);
            $dimensions = Mage::helper($this->_name)->getStandardSizes();
        } else {
            $processed_dimensions = Mage::helper($this->_name)->getSizes($order->getAllVisibleItems(), false);
            $weight = $processed_dimensions['weight'];
            $dimensions = Mage::helper($this->_name)->dimenAlgo($processed_dimensions['dimensions']);
        }


        $shipping_code = $order->getShippingMethod();
        $detect_code = str_replace('yandexdelivery_', '', $shipping_code);

        $info_array = explode('_', $detect_code);

        $data = [
            'order_num' => $order->getIncrementId(),
            'order_length' => $dimensions['A'],
            'order_width' => $dimensions['B'],
            'order_height' => $dimensions['C'],
            'order_weight' => $weight,
            'order_assessed_value' => round($order->getGrandTotal() - $order->getShippingAmount(), 0),
            'order_delivery_cost' => round($order->getShippingAmount(), 0),
            'order_shipment_type' => $info_array[0],
            'is_manual_delivery_cost' => 1,
            'recipient' => [
                'phone' => Mage::helper('yandexdelivery')->normalizePhone($order->getShippingAddress()->getTelephone()),
                'email' => $order->getShippingAddress()->getEmail(),
                'first_name' => $order->getShippingAddress()->getFirstname(),
                'last_name' => $order->getShippingAddress()->getLastname(),
            ],
            'delivery' => [
                'to_yd_warehouse' => Mage::getStoreConfig('carriers/' . $this->_name . '/yd_warehouse'),
                'delivery' => $info_array[3],
                'direction' => $info_array[2],
                'tariff' => $info_array[1],
            ],
            'deliverypoint' => [
                'city' => $order->getShippingAddress()->getCity(),
                'index' => $order->getShippingAddress()->getPostcode(),
            ],
        ];

        $invoicesSum = 0;

        if ($order->hasInvoices()) {
            //check all invoices sum

            $invoicesCollection = $order->getInvoiceCollection();

            foreach ($invoicesCollection as $invoice) {
                $invoicesSum += $invoice->getGrandTotal();
            }

            //check all credit memo sum ??
        }
        $data['order_amount_prepaid'] = $invoicesSum;

        if (isset($info_array[4])) {
            //если ПВЗ
            $data['delivery']['pickuppoint'] = $info_array[4];
        }

        $order_items = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $data = [
                'orderitem_name' => $item->getName(),
                'orderitem_quantity' => round($item->getQtyOrdered(), 2),
                'orderitem_cost' => $item->getRowTotalInclTax() / ceil($item->getQtyOrdered()),
                'orderitem_article' => $item->getSku(),
            ];

            //get tax info
            if (Mage::getStoreConfig('carriers/' . $this->_name . '/tax_all')) {
                $data['orderitem_vat_value'] = Mage::getStoreConfig('carriers/' . $this->_name . '/tax_options');
            } else {
                $attributeCode = Mage::getStoreConfig('carriers/' . $this->_name . '/tax_all');
                $data['orderitem_vat_value'] = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item->getProductId(), $attributeCode, $order->getStore());
            }

            $order_items[] = $data;
        }

        $data['order_items'] = $order_items;

        return $data;
    }

    protected function getSender() {
        return Mage::helper($this->_name)->getConfigData('sender');
    }

    //currently unused

    public function getPaymentMethods() {
        return $this->sendRequest('getPaymentMethods', [], true);
    }

}

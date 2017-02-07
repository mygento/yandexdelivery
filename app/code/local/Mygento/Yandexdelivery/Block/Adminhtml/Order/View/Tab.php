<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Block_Adminhtml_Order_View_Tab extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('mygento/yandexdelivery/order/view/tab.phtml');
    }

    public function getTabLabel()
    {
        return $this->__('Yandexdelivery shipping');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return $this->isShippedBy();
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    protected function isShippedBy()
    {
        return Mage::helper('yandexdelivery')->isShippedBy($this->getOrder());
    }

    public function hasTrack($order)
    {
        foreach ($order->getShipmentsCollection() as $_shipment) {
            foreach ($_shipment->getAllTracks() as $tracknum) {
                return $tracknum->getNumber();
            }
        }
        return false;
    }

    public function hasYdId()
    {
        if (Mage::getModel('yandexdelivery/carrier')->getYdId($this->getOrder()->getId())) {
            return true;
        }
        return false;
    }

    public function getAjaxButton($name, $title, $url)
    {
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData([
                            'label' => Mage::helper('yandexdelivery')->__($title),
                            'onclick' => '' . $name . '.click(\'' . $url . '\');',
                            'class' => 'task'
                        ])->toHtml();
        $html .= '<script type = "text/javascript">
        //<![CDATA[
            var ' . $name . ';
            ' . $name . ' = {
                click: function (url) {
                    var request = new Ajax.Request(
                            url,
                            {
                                method: "get",
                                onComplete: this.onComplete,
                                onSuccess: this.onSave
                            }
                    );
                },
                onComplete: function (transport) {
                    if (transport && transport.responseText) {
                        try {
                            response = eval("(" + transport.responseText + ")");
                        } catch (e) {
                            response = {};
                        }
                    }
                    if (response.error) {
                        if ((typeof response.message) == "string") {
                            alert(response.message);
                        } else {
                            alert(response.message.join("\n"));
                        }
                        return false;
                    }
                    else {
                        alert(response.status);
                    }
                },
                onSave: function () {
                }
            }
        //]]>
        </script>' . "\n";
        return $html;
    }

    protected function getSendOrderUrl()
    {
        return $this->getFullUrl('send');
    }

    protected function getDeleteOrderUrl()
    {
        return $this->getFullUrl('delete');
    }

    protected function getConfirmOrderUrl()
    {
        return $this->getFullUrl('confirm');
    }

    protected function getUpdateOrderUrl()
    {
        return $this->getFullUrl('update');
    }

    protected function getLabelUrl()
    {
        return $this->getFullUrl('label');
    }

    protected function getFullUrl($path)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/yandexdelivery_api/' . $path, ['_secure' => true, 'orderid' => $this->getOrder()->getId()]);
    }

    public function getTable()
    {
        //таблица с статусами заказа

        if (!$this->hasYdId()) {
            return;
        }

        $result = Mage::getModel('yandexdelivery/carrier')->senderOrderStatuses(Mage::getModel('yandexdelivery/carrier')->getYdId($this->getOrder()->getId()));

        if ($result->status != "ok") {
            return;
        }

        $html = '<table class="data order-tables" cellspacing="0">';
        $html .= '<colgroup>
        <col width="1">
        <col width="1">
        <col width="1">
        <col width="1">
        </colgroup>';
        $html .= '<thead>
            <tr class="headings">
                <th>' . $this->__('Status Date') . '</th>
                <th>' . $this->__('Description') . '</th>
                <th>' . $this->__('Uniform status') . '</th>
                <th>' . $this->__('Status') . '</th>
            </tr>
        </thead>';
        foreach ($result->data->data as $item) {
            $html .= '<tbody class="even">
                    <tr class="border">
                        <td>' . $item->time . '</td>
                        <td>' . $item->description . '</td>
                        <td>' . $item->uniform_status . '</td>
                        <td>' . $item->status . '</td>
                    </tr>
                    </tbody>';
        }
        $html .= '</table>';
        return $html;
    }
}

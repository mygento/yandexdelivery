<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexdelivery
 * @copyright 2017 NKS LLC. (http://www.mygento.ru)
 * @license GPLv2
 */
class Mygento_Yandexdelivery_Block_Version extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     *
     * @SuppressWarnings("unused")
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $info = '<fieldset class="config success-msg" style="padding-left:30px;"><a target="_blank" href="https://www.mygento.ru/"><img style="margin-right:5px;" src="//www.mygento.ru/media/favicon/default/favicon.png" width="16" height="16" />' . $this->__('Magento Development') . '</a>';
        $info .= '<a style="float:right;margin-left: 20px;" target="_blank" href="https://github.com/mygento/yandexdelivery">' . $this->__('Module on Github') . '</a>';
        $info .= '<a style="float:right;" target="_blank" href="https://mygento.atlassian.net/wiki/pages/viewpage.action?pageId=3708567">' . $this->__('Extension instructions') . '</a></fieldset>';
        return $info;
    }
}

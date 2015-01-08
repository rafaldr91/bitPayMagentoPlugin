<?php
/**
 * @license Copyright 2011-2014 BitPay Inc., MIT License
 * @see https://github.com/bitpay/magento-plugin/blob/master/LICENSE
 */

class Bitpay_Core_Block_Form_Bitpay extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $payment_template = 'bitpay/form/bitpay.phtml';

        parent::_construct();

        if (true === file_exists($payment_template) && true === is_readable($payment_template)) {
            $this->setTemplate($payment_template);
        } else {
            \Mage::helper('bitpay')->debugData('[ERROR] In Bitpay_Core_Block_Form_Bitpay::_construct(): HTML payment template missing or unreadable.');
            throw new \Exception('In Bitpay_Core_Block_Iframe::getIframeUrl(): HTML payment template missing or unreadable.');
        }
    }
}

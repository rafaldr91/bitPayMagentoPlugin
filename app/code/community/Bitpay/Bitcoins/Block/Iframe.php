<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2011-2014 BitPay LLC
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Bitpay_Bitcoins_Block_Iframe extends Mage_Checkout_Block_Onepage_Payment
{

    /**
     */
    protected function _construct()
    {
        $this->setTemplate('bitcoins/iframe.phtml');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function GetQuoteId()
    {
        $quote   = $this->getQuote();
        $quoteId = $quote->getId();

        return $quoteId;
    }

    /**
     * create an invoice and return the url so that iframe.phtml can display it
     *
     * @return string
     */
    public function GetIframeUrl()
    {
        // are they using bitpay?
        // @todo refactor this
        if (!($quote = Mage::getSingleton('checkout/session')->getQuote()) 
            or !($payment = $quote->getPayment())
            or !($instance = $payment->getMethodInstance())
            or ($instance->getCode() != 'Bitcoins'))
        {
            return 'notbitpay';
        }

        // fullscreen disabled?
        if (Mage::getStoreConfig('payment/Bitcoins/fullscreen'))
        {
            return 'disabled';
        }

        include Mage::getBaseDir('lib').'/bitpay/bp_lib.php';		

        $apiKey  = Mage::getStoreConfig('payment/Bitcoins/api_key');
        $speed   = Mage::getStoreConfig('payment/Bitcoins/speed');
        $quote   = $this->getQuote();
        $quoteId = $quote->getId();

        if (Mage::getModel('Bitcoins/ipn')->GetQuotePaid($quoteId))
        {
            return 'paid'; // quote's already paid, so don't show the iframe
        }


        $options = array(
            'currency'          => $quote->getQuoteCurrencyCode(),
            'fullNotifications' => 'true',
            'notificationURL'   => Mage::getUrl('bitpay_callback'),
            'redirectURL'       => Mage::getUrl('checkout/onepage/success'),
            'transactionSpeed'  => $speed,
            'apiKey'            => $apiKey,
        );

        // customer data
        $method   = Mage::getModel('Bitcoins/paymentMethod');
        $options += $method->ExtractAddress($quote->getShippingAddress());

        // Mage doesn't round the total until saving and it can have more precision
        // at this point which would be bad for later comparing records w/ bitpay.
        // So round here to match what the price will be saved as:
        $price = round($quote->getGrandTotal(),4);

        //serialize info about the quote to detect changes
        $hash = $method->getQuoteHash($quoteId);

        $invoice = bpCreateInvoice($quoteId, $price, array('quoteId' => $quoteId, 'quoteHash' => $hash), $options);

        if (array_key_exists('error', $invoice))
        {
            Mage::log('Error creating bitpay invoice', null, 'bitpay.log');
            Mage::log($invoice['error'], null, 'bitpay.log');
            Mage::throwException("Error creating bit-pay invoice.  Please try again or use another payment option.");

            return false; 
        }

        return $invoice['url'].'&view=iframe';
    }
}

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
 
// callback controller
class Bitpay_Bitcoins_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     */
    public function checkForPaymentAction()
    {
        $params  = $this->getRequest()->getParams();
        $quoteId = $params['quote'];
        $paid    = Mage::getModel('Bitcoins/ipn')->GetQuotePaid($quoteId);
        print json_encode(array('paid' => $paid));
        exit(); 
    }

    /**
     * bitpay's IPN lands here
     */
    public function indexAction() {
        require Mage::getBaseDir('lib').'/bitpay/bp_lib.php';
        $apiKey  = Mage::getStoreConfig('payment/Bitcoins/api_key');
        $invoice = bpVerifyNotification($apiKey);

        if (is_string($invoice))
        {
            Mage::log("bitpay callback error: $invoice", Zend_Log::ERR, 'bitpay.log');
            throw new Exception('Bitpay callback error:' . $invoice);
        }

        // get the order
        if (isset($invoice['posData']['quoteId']))
        {
            $quoteId = $invoice['posData']['quoteId'];
            $order   = Mage::getModel('sales/order')->load($quoteId, 'quote_id');
        }
        elseif (isset($invoice['posData']['orderId']))
        {
            $orderId = $invoice['posData']['orderId'];
            $order   = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        }
        else
        {
            Mage::log('Invalid posData, does not contain quoteId or orderId.', Zend_Log::ERR, 'bitpay.log');
            throw new Exception('Invalid Bitpay IPN received.');
        }

        // save the ipn so that we can find it when the user clicks "Place Order"
        Mage::getModel('Bitcoins/ipn')->Record($invoice);

        if (!$order->getId())
        {
            Mage::log('Order object does not contain an ID', Zend_Log::ERR, 'bitpay.log');
            throw new Exception('Order object does not contain an ID');
        }

        // update the order if it exists already
        // BitPay Statuses
        // new, paid, confirmed, complete, expired, invalid
        Mage::log('Received IPN with "' . $invoice['status'] . '" status', Zend_Log::DEBUG, 'bitpay.log');
        switch($invoice['status'])
        {

        // Map to Magento state Processing
        case 'paid':
            // Mark paid if there is an outstanding total
            $method = Mage::getModel('Bitcoins/paymentMethod');
            $method->MarkOrderPaid($order);
            break;

        // Map to Magento status Complete
        case 'confirmed':
        case 'complete':
            // Mark confirmed/complete if the order has been paid
            $method = Mage::getModel('Bitcoins/paymentMethod');
            $method->MarkOrderComplete($order);
            //Mage::log('Received a ' . $invoice['status'] . ' notification from BitPay but this order is not paid yet. Possible internal error with Magento. Check order status to confirm.', Zend_Log::ERR, 'bitpay.log');
            break;

        // Map to Magento State Closed
        case 'invalid':
            $method = Mage::getModel('Bitcoins/paymentMethod');
            $method->MarkOrderCancelled($order);
            break;
        }
    }
}

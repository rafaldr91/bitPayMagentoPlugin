<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2011-2014 BitPay, Inc.
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

class Bitpay_Bitcoins_Model_Observer
{

    /**
     * Queries BitPay to update the order states in magento to make sure that
     * open orders are closed/canceled if the BitPay invoice expires or becomes
     * invalid.
     */
    public function updateOrderStates()
    {
        Mage::log(
            'cronjob: started',
            Zend_Log::DEBUG,
            Mage::helper('bitpay')->getLogFile()
        );

        $apiKey = Mage::getStoreConfig('payment/Bitcoins/api_key');

        if (empty($apiKey)) {
            Mage::log(
                'cronjob: Api Key not set.',
                Zend_Log::ERR,
                Mage::helper('bitpay')->getLogFile()
            );
            return; // Api Key needs to be set
        }


        /**
         * Get all of the orders that are open and have not received an IPN for
         * complete, expired, or invalid.
         *
         * If anyone knows of a better way to do this, please let me know
         */
        $orders = Mage::getModel('Bitcoins/ipn')->getOpenOrders();

        /**
         * Get all orders that have been paid using bitpay and
         * are not complete/closed/etc
         */
        foreach ($orders as $order) {
            /**
             * Query BitPay with the invoice ID to get the status. We must take
             * care not to anger the API limiting gods and disable our access
             * to the API.
             */
            $status = null;

            // Does the order need to be updated?
            // Yes? Update Order Status
            // No? continue
        }

        Mage::log(
            'cronjob: end',
            Zend_Log::DEBUG,
            Mage::helper('bitpay')->getLogFile()
        );
    }

    /**
     * Method that is called via the magento cron to update orders if the
     * invoice has expired
     */
    public function cleanExpired()
    {
        Mage::helper('bitpay')->cleanExpired();
    }
}

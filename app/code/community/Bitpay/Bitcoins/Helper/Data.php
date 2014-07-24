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

class Bitpay_Bitcoins_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * File that is used to put all logging information in.
     *
     * @var string
     */
    const LOG_FILE = 'bitpay.log';

    /**
     * Returns the file used for logging
     *
     * @return string
     */
    public function getLogFile()
    {
        return self::LOG_FILE;
    }

    /**
     * Returns true if the merchant has set their api key
     *
     * @return boolean
     */
    public function hasApiKey()
    {
        $key = Mage::getStoreConfig('payment/Bitcoins/api_key');

        return !empty($key);
    }

    /**
     * Returns true if Transaction Speed has been configured
     *
     * @return boolean
     */
    public function hasTransactionSpeed()
    {
        $speed = Mage::getStoreConfig('payment/Bitcoins/speed');

        return !empty($speed);
    }

    /**
     * This method is used to removed IPN records in the database that
     * are expired and update the magento orders to canceled if they have
     * expired.
     */
    public function cleanExpired()
    {
        $expiredRecords = Mage::getModel('Bitcoins/ipn')->getExpired();

        foreach ($expiredRecords as $ipn) {
            $incrementId = $ipn->getOrderId();
            if (empty($incrementId)) {
                $this->logIpnParseError($ipn);
                continue;
            }

            // Cancel the order
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            $this->cancelOrder($order);

            // Delete all IPN records for order id
            Mage::getModel('Bitcoins/ipn')
                ->deleteByOrderId($ipn->getOrderId());
            Mage::log(
                sprintf('Deleted Record: %s', $ipn->toJson()),
                Zend_Log::DEBUG,
                self::LOG_FILE
            );
        }
    }

    /**
     * Log error if there is an issue parsing an IPN record
     *
     * @param Bitpay_Bitcoins_Model_Ipn $ipn
     * @param boolean                   $andDelete
     */
    private function logIpnParseError(Bitpay_Bitcoins_Model_Ipn $ipn, $andDelete = true)
    {
        Mage::log(
            'Error processing IPN record',
            Zend_Log::DEBUG,
            self::LOG_FILE
        );
        Mage::log(
            $ipn->toJson(),
            Zend_Log::DEBUG,
            self::LOG_FILE
        );

        if ($andDelete) {
            $ipn->delete();
            Mage::log(
                'IPN record deleted from database',
                Zend_Log::DEBUG,
                self::LOG_FILE
            );
        }
    }

    /**
     * This will cancel the order in the magento database, this will return
     * true if the order was canceled or it will return false if the order
     * was not updated. For example, if the order is complete, we don't want
     * to cancel that order so this method would return false.
     *
     * @param Mage_Sales_Model_Order
     *
     * @return boolean
     */
    private function cancelOrder(Mage_Sales_Model_Order $order)
    {
        $orderState = $order->getState();

        /**
         * These order states are useless and can just be skipped over. No
         * need to cancel an order that is alread canceled.
         */
        $statesWeDontCareAbout = array(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_COMPLETE,
        );

        if (in_array($orderState, $statesWeDontCareAbout)) {
            return false;
        }

        $order->setState(
            Mage_Sales_Model_Order::STATE_CANCELED,
            true,
            'BitPay Invoice has expired', // Comment
            false // notifiy customer?
        )->save();
        Mage::log(
            sprintf('Order "%s" has been canceled', $order->getIncrementId()),
            Zend_Log::DEBUG,
            self::LOG_FILE
        );

        return true;
    }
}

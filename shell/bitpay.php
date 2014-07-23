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

require_once 'abstract.php';

/**
 * This class is used to work with the bitpay api via the command line and to
 * debug issues
 */
class Bitpay_Shell_Bitpay extends Mage_Shell_Abstract
{

    public function run()
    {
        if ($clean = $this->getArg('clean')) {
            switch ($clean) {
                case 'expired':
                    $this->cleanExpired();
                    break;
                default:
                    echo $this->usageHelp();
                    return 1;
                    break;
            }

            return 0;
        }

        if ($this->getArg('expired')) {
            $expiredIpns = Mage::getModel('Bitcoins/ipn')->getExpired();
            echo "\n";
            foreach ($expiredIpns as $ipn) {
                var_dump($ipn->toArray());
                printf(
                    'Order ID: %s',
                    $ipn->getOrderId()
                );

                echo "\n";
            }

            return 0;
        }

        $orders = Mage::getModel('Bitcoins/ipn')->getOpenOrders();
        foreach ($orders as $order) {
            var_dump(get_class($order));
        }
        echo $this->usageHelp();
    }

    /**
     * Removes expired IPNs from database and updates order if they are
     * open
     */
    public function cleanExpired()
    {
        $expiredRecords = Mage::getModel('Bitcoins/ipn')->getExpired();

        // Parse each record
        foreach ($expiredRecords as $ipn) {
            $incrementId = $ipn->getOrderId();
            if (empty($incrementId)) {
                printf("Error processing IPN record\n");
                Mage::log($ipn->toJson(), Zend_Log::DEBUG, 'bitpay.log');
                /**
                 * We have no way to tie this to any magento order so it needs
                 * to be deleted
                 */
                $ipn->delete();
                printf("IPN Record Deleted\n");
                continue;
            }
            // Parsing IPN for increment id x
            printf("Prasing '%s'\n", $ipn->getOrderId());
            // Cancel the order in the system
            $order      = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            $orderState = $order->getState();

            /**
             * If the order is complete, we do not want to cancel it
             */
            $statesWeDontCareAbout = array(
                Mage_Sales_Model_Order::STATE_CANCELED,
                Mage_Sales_Model_Order::STATE_CLOSED,
                Mage_Sales_Model_Order::STATE_COMPLETE,
            );
            if (!in_array($orderState, $statesWeDontCareAbout)) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_CANCELED,
                    true,
                    'BitPay Invoice has expired', // comment
                    false // notifiy customer?
                )->save();
                printf("Order has been canceled\n");
            }

            // Delete all IPN records for order id
            Mage::getModel('Bitcoins/ipn')
                ->deleteByOrderId($ipn->getOrderId());

            printf("IPN Record Deleted\n");
        }

        printf("Complete\n");
    }

    /**
     * Display help on how to use this bad boy
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f bitpay.php

  --clean <status>    Delete all IPN records based on <status>

List of Statuses:

  new
  paid
  confirmed
  complete
  expired
  invalid

USAGE;
    }
}

$shell = new Bitpay_Shell_Bitpay();
$shell->run();

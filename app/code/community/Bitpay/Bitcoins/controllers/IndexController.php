<?php

/**
 * ©2011,2012,2013,2014 BITPAY, INC.
 * 
 * Permission is hereby granted to any person obtaining a copy of this software
 * and associated documentation for use and/or modification in association with
 * the bitpay.com service.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * Bitcoin payment plugin using the bitpay.com service.
 * 
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
        }
        else
        {
            // get the order
            if (isset($invoice['posData']['quoteId']))
            {
                $quoteId = $invoice['posData']['quoteId'];
                $order   = Mage::getModel('sales/order')->load($quoteId, 'quote_id');
            }
            else
            {
                $orderId = $invoice['posData']['orderId'];
                $order   = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            }

            // save the ipn so that we can find it when the user clicks "Place Order"
            Mage::getModel('Bitcoins/ipn')->Record($invoice); 

            // update the order if it exists already
            if ($order->getId())
            {
                switch($invoice['status'])
                {

                case 'paid':
                    // Mark paid if there is an outstanding total
                    if ($order->getTotalDue() > 0)
                    {
                        $method = Mage::getModel('Bitcoins/paymentMethod');
                        $method->MarkOrderPaid($order);
                    }
                    else
                    {
                        Mage::log('Received a PAID notification from BitPay but there is nothing due on this invoice. Ignoring this IPN.', null, 'bitpay.log');
                    }
                    break;

                case 'confirmed':
                case 'complete':
                    // Mark confirmed/complete if the order has been paid
                    if ($order->getTotalDue() <= 0)
                    {
                        $method = Mage::getModel('Bitcoins/paymentMethod');
                        $method->MarkOrderComplete($order);
                    }
                    else
                    {
                        Mage::log('Received a ' . $invoice['status'] . ' notification from BitPay but this order is not paid yet. Possible internal error with Magento. Check order status to confirm.', Zend_Log::ERR, 'bitpay.log');
                    }
                    break;

                case 'invalid':
                    $method = Mage::getModel('Bitcoins/paymentMethod');
                    $method->MarkOrderCancelled($order);
                    break;
                }
            }
        }
    }
}

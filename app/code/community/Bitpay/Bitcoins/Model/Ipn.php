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

class Bitpay_Bitcoins_Model_Ipn extends Mage_Core_Model_Abstract
{

    /**
     */
    function _construct()
    {
        $this->_init('Bitcoins/ipn');

        return parent::_construct();
    }

    /**
     * @param $invoice
     *
     * @return
     */
    function Record($invoice)
    {
        return $this
            ->setQuoteId(isset($invoice['posData']['quoteId']) ? $invoice['posData']['quoteId'] : NULL)
            ->setOrderId(isset($invoice['posData']['orderId']) ? $invoice['posData']['orderId'] : NULL)
            ->setPosData(json_encode($invoice['posData']))
            ->setInvoiceId($invoice['id'])
            ->setUrl($invoice['url'])
            ->setStatus($invoice['status'])
            ->setBtcPrice($invoice['btcPrice'])
            ->setPrice($invoice['price'])
            ->setCurrency($invoice['currency'])
            ->setInvoiceTime(intval($invoice['invoiceTime']/1000.0))
            ->setExpirationTime(intval($invoice['expirationTime']/1000.0))
            ->setCurrentTime(intval($invoice['currentTime']/1000.0))
            ->save();
    }

    /**
     * @param string $quoteId
     * @param array  $statuses
     *
     * @return boolean
     */
    function GetStatusReceived($quoteId, $statuses) {
        if (!$quoteId)
        {
            return false;
        }

        $quote = Mage::getModel('sales/quote')->load($quoteId, 'entity_id');

        if (!$quote)
        {
            Mage::log('quote not found', Zend_Log::WARN, 'bitpay.log');

            return false;
        }

        $quoteHash = Mage::getModel('Bitcoins/paymentMethod')->getQuoteHash($quoteId);

        if (!$quoteHash)
        {
            Mage::log('Could not find quote hash for quote '.$quoteId, Zend_Log::WARN, 'bitpay.log');

            return false;		
        }

        $collection = $this->getCollection()->AddFilter('quote_id', $quoteId);

        foreach ($collection as $i)
        {
            if (in_array($i->getStatus(), $statuses))
            {
                // check that quote data was not updated after IPN sent
                $posData = json_decode($i->getPosData());

                if (!$posData)
                {
                    continue;
                }

                if ($quoteHash == $posData->quoteHash)
                {
                    return true;
                }
            }
        }

        return false;		
    }

    /**
     * @param string $quoteId
     *
     * @return boolean
     */
    function GetQuotePaid($quoteId)
    {
        return $this->GetStatusReceived($quoteId, array('paid', 'confirmed', 'complete'));
    }

    /**
     * @param string $quoteId
     *
     * @return boolean
     */
    function GetQuoteComplete($quoteId)
    {
        return $this->GetStatusReceived($quoteId, array('confirmed', 'complete'));
    }
}

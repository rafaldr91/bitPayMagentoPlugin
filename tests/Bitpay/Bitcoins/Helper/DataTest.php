<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2011-2014 BitPay
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

class Bitpay_Bitcoins_Helper_DataTest extends PHPUnit_Framework_TestCase
{

    protected static $faker;

    public static function setUpBeforeClass()
    {
        self::$faker = Faker\Factory::create();
    }

    public function testHasApiKeyFalse()
    {
        Mage::app()->getStore()->setConfig('payment/Bitcoins/api_key', null);

        $this->assertFalse(Mage::helper('bitpay')->hasApiKey());
    }

    public function testHasApiKeyTrue()
    {
        Mage::app()->getStore()->setConfig('payment/Bitcoins/api_key', 'ThisIsMyApiKey');

        $this->assertTrue(Mage::helper('bitpay')->hasApiKey());
    }

    public function testHasTransactionSpeedFalse()
    {
        Mage::app()->getStore()->setConfig('payment/Bitcoins/speed', null);

        $this->assertFalse(Mage::helper('bitpay')->hasTransactionSpeed());
    }

    public function testHasTransactionSpeedTrue()
    {
        Mage::app()->getStore()->setConfig('payment/Bitcoins/speed', 'low');

        $this->assertTrue(Mage::helper('bitpay')->hasTransactionSpeed());
    }

    public function testCleanExpired()
    {
        // Create a few expired/invalid ipns
        $invalidIpn = $this->createInvalidIpn();
        $expiredIpn = $this->createExpiredIpn();

        // Are the IPNs in the database?
        $ipn          = Mage::getModel('Bitcoins/ipn');
        $dbInvalidIpn = $ipn->load($invalidIpn->getId())->toArray();
        $dbExpiredIpn = $ipn->load($expiredIpn->getId())->toArray();
        $this->assertArrayHasKey('id', $dbInvalidIpn);
        $this->assertArrayHasKey('id', $dbExpiredIpn);
        unset($dbInvalidIpn, $dbExpiredIpn);

        // clean them
        Mage::helper('bitpay')->cleanExpired();

        // check the database and see if they are still there
        $ipn          = Mage::getModel('Bitcoins/ipn');
        $dbInvalidIpn = $ipn->load($invalidIpn->getId())->toArray();
        $dbExpiredIpn = $ipn->load($expiredIpn->getId())->toArray();
        $this->assertEmpty($dbInvalidIpn);
        $this->assertEmpty($dbExpiredIpn);
    }

    private function createInvalidIpn()
    {
        $ipn = new Bitpay_Bitcoins_Model_Ipn();
        $ipn->setData(
            array(
                'quote_id'        => '',
                'order_id'        => '',
                'invoice_id'      => '',
                'url'             => '',
                'pos_data'        => '',
                'status'          => '',
                'btc_price'       => '',
                'price'           => '',
                'currency'        => '',
                'invoice_time'    => '',
                'expiration_time' => '',
                'current_time'    => '',
            )
        );
        $ipn->save();
        $ipn->load($ipn->getId());

        return $ipn;
    }

    private function createExpiredIpn()
    {
        $order = $this->createOrder();
        $ipn   = new Bitpay_Bitcoins_Model_Ipn();
        $ipn->setData(
            array(
                'quote_id'        => '',
                'order_id'        => $order->getIncrementId(),
                'invoice_id'      => '',
                'url'             => '',
                'pos_data'        => '',
                'status'          => '',
                'btc_price'       => '',
                'price'           => '',
                'currency'        => '',
                'invoice_time'    => '',
                'expiration_time' => '',
                'current_time'    => '',
            )
        );
        $ipn->save();
        $ipn->load($ipn->getId());

        return $ipn;
    }

    private function createOrder()
    {
        $product = $this->createProduct();
        $quote   = $this->createQuote();
        $quote->addProduct(
            $product,
            new Varien_Object(
                array(
                    'qty' => 1,
                )
            )
        );
        $address = array(
            'firstname'            => self::$faker->firstName,
            'lastname'             => self::$faker->lastName,
            'company'              => self::$faker->company,
            'email'                => self::$faker->email,
            'city'                 => self::$faker->city,
            'region_id'            => '',
            'region'               => 'State/Province',
            'postcode'             => self::$faker->postcode,
            'telephone'            => self::$faker->phoneNumber,
            'country_id'           => self::$faker->state,
            'customer_password'    => '',
            'confirm_password'     => '',
            'save_in_address_book' => 0,
            'use_for_shipping'     => 1,
            'street'               => array(
                self::$faker->streetAddress
            ),
        );

        $quote->getBillingAddress()
            ->addData($address);

        $quote->getShippingAddress()
            ->addData($address)
            ->setShippingMethod('flatrate_flatrate')
            ->setPaymentMethod('checkmo')
            ->setCollectShippingRates(true)
            ->collectTotals();

        $quote
            ->setCheckoutMethod('guest')
            ->setCustomerId(null)
            ->setCustomerEmail($address['email'])
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        $quote->getPayment()
            ->importData(array('method' => 'checkmo'));

        $quote->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();

        $order->save();
        $order->load($order->getId());

        return $order;
    }

    private function createProduct()
    {
        $product = Mage::getModel('catalog/product');

        $product->addData(
            array(
                'attribute_set_id'  => 1,
                'website_ids'       => array(1),
                'categories'        => array(),
                'type_id'           => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'sku'               => self::$faker->randomNumber,
                'name'              => self::$faker->name,
                'weight'            => self::$faker->randomDigit,
                'status'            => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                'tax_class_id'      => 2,
                'visibility'        => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                'price'             => self::$faker->randomFloat(2),
                'description'       => self::$faker->paragraphs,
                'short_description' => self::$faker->sentence,
                'stock_data'        => array(
                    'is_in_stock' => 1,
                    'qty'         => 100,
                ),
            )
        );

        $product->save();
        $product->load($product->getId());

        return $product;
    }

    private function createQuote()
    {
        return Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore('default')->getId());
    }
}

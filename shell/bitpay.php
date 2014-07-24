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

        echo $this->usageHelp();
    }

    /**
     * Removes expired IPNs from database and updates order if they are
     * open
     */
    public function cleanExpired()
    {
        Mage::helper('bitpay')->clearExpired();
    }

    /**
     * Display help on how to use this bad boy
     */
    public function usageHelp()
    {
        $figlet = new Zend_Text_Figlet(
            array(
                'justification' => Zend_Text_Figlet::JUSTIFICATION_CENTER
            )
        );

        return <<<USAGE

{$figlet->render('BitPay')}

Usage:  php -f bitpay.php

  --clean <status>    Delete all IPN records based on <status>

List of Statuses:

  expired

USAGE;
    }
}

$shell = new Bitpay_Shell_Bitpay();
$shell->run();

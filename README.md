BitPay Inc Magento Plugin
=========================

# Status

[![Build Status](https://travis-ci.org/bitpay/magento-plugin.svg?branch=master)](https://travis-ci.org/bitpay/magento-plugin)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/JoshuaEstes/magento-plugin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bitpay/magento-plugin/?branch=master)

[![Code Coverage](https://scrutinizer-ci.com/g/JoshuaEstes/magento-plugin/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bitpay/magento-plugin/?branch=master)

[![Coverage Status](https://img.shields.io/coveralls/bitpay/magento-plugin.svg)](https://coveralls.io/r/bitpay/magento-plugin)

# Installation

## Download
<strong>1.</strong> [Download](https://github.com/bitpay/magento-plugin/archive/master.zip) and Unzip this archive and copy the files to the location of your
[Magento CE](http://magento.com/) installation on your web server. For Ubuntu-based servers, the default
location for website files is the `/var/www` folder. Your web hosting provider may
use a different location for storing your website files so check with them if the
`/var/www` folder does not exist or your Magento files are in an otherwise unknown
location.

Many web hosting accounts have a graphical, web-based control panel for your server.
This is the easiest method for copying the [BitPay Magento Plugin](https://github.com/bitpay/magento-plugin) files to your Magenento
CE directory. If your provider has one of these graphical control panels, log into
your hosting account and move the files using that tool. However, if that is not
an option and you can only access your web server using a shell account via SSH,
open a new connection and issue these commands:

```bash
bitpay@bitpay:~$ unzip magento-plugin-master.zip
bitpay@bitpay:~$ cd magento-plugin-master
bitpay@bitpay:~$ cp -R ./* /location/of/your/magento/installation/
```
<strong>Note:</strong>  You may need to have superuser privileges to copy files
to `/var/www` on Ubuntu-based servers.  If you receive “Permission denied” errors
when using the cp command above, use sudo before the cp command and specify the
superuser password when asked:

```bash
bitpay@bitpay:~$ sudo cp -R ./* /location/of/your/magento/installation/
[sudo] password for (username):
```
<strong>2.</strong>  Verify the files have been copied correctly by checking
your Magento CE installation folder for one or more of them.  You can choose
to check for any of the files present in the BitPay plugin archive.  The file
I’m looking for in this example should be in the `/var/www/magento/app/code/community/Bitpay/Bitcoins/Model`
directory along with the `Ipn.php` file on my Ubuntu server:

```bash
bitpay@bitpay:~$ ls -l /var/www/magento/app/code/community/Bitpay/Bitcoins/Model/
total 24
-rw-r--r-- 1 root root  3097 Mar 25 14:06 Ipn.php
-rw-r--r-- 1 root root 10786 Mar 25 14:06 PaymentMethod.php
drwxr-xr-x 3 root root  4096 Mar 25 13:54 Resource
drwxr-xr-x 2 root root  4096 Mar 25 13:54 Source
```

If the files were copied correctly and are present in the directory, you should
see the files listed when you issue the ls command.  If you do not see any files
listed, try the cp command again to retry the copying procedure.  However, if
you still do not see any files listed or you receive an error copying the files,
contact your web hosting support for assistance.

## modman
Using [modman](https://github.com/colinmollenhour/modman) you can
install the BitPay Magento Plugin. Once
you have modman installed, run `modman init` if you have not already done so. Next
just run `modman clone https://github.com/bitpay/magento-plugin.git` in the root
of the Magento installation. In this case it is `/var/www/magento`.

# Magento CE 1.8.x - 1.9.x Installation Tips
In some instances for merchants using Magento CE version 1.8.x, the BitPay
Bitcoins payment plugin might not appear in the Payment Methods configuration
section even though all plugin files have been correctly installed. To
resolve this issue, log into your admin control panel and choose the
System -> Cache Management configuration screen. Click the check box next
to the Configuration cache type and choose the Disable action from the Actions
drop-down list box. Click the Submit button to disable this cache.

Next, click both the Flush Magento Cache and Flush Cache Storage buttons (Clicked
"Ok" when the pop-up box is displayed) to remove the stale configuration cache files.

Finally, log completely out of the administrative control panel and then log back
in. The Bitcoins option is now correctly displaying under Payment Methods in the
configuration screen. The BitPay plugin parameters are exactly the same on Magento
CE 1.8.x as on older Magento CE releases.

# Configuration
<strong>NOTE:</strong>  SSL is <em>required</em> for use of the BitPay plugin for Magento CE.

1. Create an API key at bitpay.com by clicking My Account > API Access Keys > Add New API Key.
2. In Admin panel under "System > Configuration > Sales > Payment Methods > Bitcoins":
  - Verify that the module is enabled. 
  - Enter your API key.  
  - Select a transaction speed.  The **high** speed will send a confirmation as
    soon as a transaction is received in the bitcoin network (usually a few
    seconds).  A **medium** speed setting will typically take 10 minutes.
    The **low** speed setting usually takes around 1 hour.  See the bitpay.com
    merchant documentation for a full description of the transaction speed settings. 
  - Verify that the currencies option includes your store's currencies.  If it
    doesn't, check bitpay.com to see if they support your desired currency. If
    so, you may simply add the currency to the list using this setting.  If not,
    you will not be able to use that currency. 
  - (optional) Adjust the "Fullscreen Invoice" setting. "No" means that payment
    instructions are embedded in the checkout page.  "Yes" means that the buyer
    will be redirected to bitpay.com to pay their order.  The default setting is
    "No".

# Usage
When a shopper chooses the Bitcoin payment method, they will be presented with an
order summary as the next step (prices are shown in whatever currency they've
selected for shopping).  If the fullscreen option is disabled, they can pay for
their order using the address shown on the screen.  Otherwise they will place
their order and be redirected to bitpay.com to pay.

The order status in the admin panel will be "Processing" if payment has been confirmed. 

Note: This extension does not provide a means of automatically pulling a current BTC
exchange rate for presenting BTC prices to shoppers.

# Troubleshooting
The official BitPay support website should always be your first reference for
troubleshooting any problems you may encounter: https://support.bitpay.com

The official Magento Community Edition support website might also be helpful
if the problem you are experiencing is not directly related to the payment
plugin: https://www.magentocommerce.com/support/ce/

<strong>Other troubleshooting tips:</strong>

1. Ensure a valid SSL certificate is installed on your server. Also ensure your root CA cert is
   updated. If your CA cert is not current, you will see curl SSL verification errors.
2. Verify that your web server is not blocking POSTs from servers it may not recognize. Double
   check this on your firewall as well, if one is being used.
3. Check the `bitpay.log` file for any errors during BitPay payment attempts. If you contact BitPay
   support, they will ask to see the log file to help diagnose the problem.  The log file will be found 
   inside your Magento's `var/log/` directory.
4. Check the version of this plugin against the official plugin repository to ensure you are using
   the latest version. Your issue might have been addressed in a newer version!
5. If all else fails, send an email describing your issue *in detail* to support@bitpay.com

NOTE: When contacting support it will help us is you provide:
* Magento Version
* Other plugins you have installed
* Some configuration settings such as:
  * Transaction Speed
  * Set order complete with "complete" IPN

# Change Log
<strong>Version 1</strong><br />
- Initial version, tested against Magento 1.6.0.0

<strong>Version 2</strong><br />
- Now supports API keys instead of SSL files.  Tested against 1.7.0.2.
 
<strong>Version 3</strong><br />
- Now gives the option to show an iframe on the checkout page instead of redirecting to bitpay.com.

<strong>Version 4</strong><br />
- Improved README documentation.
- Additional testing performed against 1.8.1.0 and installation instructions updated to reflect differences.
- Added parameter to automatically create a shipment and mark orders complete (off by default).
- Version incremented, other bug fixes and enhancements (see commit notes).

<strong>Version 5</strong><br />
- Added new HTTP header for version tracking

<strong>Version 6</strong></br >
- Updated BitPay logo in admin settings
- Tested & validated against latest 1.9.0.1
- Tested & validated with default one-page checkout settings

# License

<strong>©2011-2014 BITPAY, INC.</strong>

Permission is hereby granted to any person obtaining a copy of this software
and associated documentation for use and/or modification in association with
the bitpay.com service.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Bitcoin payment module for Magento Community Edition using the bitpay.com service.

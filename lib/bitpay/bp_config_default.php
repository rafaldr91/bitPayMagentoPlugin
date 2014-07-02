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
 
global $bpconfig;

$bpconfig['host']        = "bitpay.com";
$bpconfig['port']        = 443;
$bpconfig['hostAndPort'] = $bpconfig['host'];

if ($bpconfig['port'] != 443)
{
    $bpconfig['hostAndPort'] .= ":".$bpconfig['host'];
}

$bpconfig['ssl_verifypeer'] = 1;
$bpconfig['ssl_verifyhost'] = 2;

//include custom config overrides if it exists
try {
    include 'bp_config.php';
} catch (Exception $e) {
    // do nothing
}

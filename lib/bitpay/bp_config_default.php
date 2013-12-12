<?php
$bpconfig_host="bitpay.com";
$bpconfig_port=443;
$bpconfig_hostAndPort=$bpconfig_host;
if ($bpconfig_port!=443)
	$bpconfig_hostAndPort.=":".$bpconfig_port;
$bpconfig_ssl_verifypeer=1;
$bpconfig_ssl_verifyhost=2;
//include custom config overrides if it exists
if (file_exists('bp_config.php'))
	require_once 'bp_config.php';
?>

<?php
$bpconfig_host="bitpay.com";
$bpconfig_port=443;
$bpconfig_hostAndPort=$bpconfig_host;
if ($bpconfig_port!=443)
	$bpconfig_hostAndPort.=":".$bpconfig_port;

//include custom config overrides if it exists
if (file_exists('bp_config.php'))
	require_once 'bp_config.php';
?>

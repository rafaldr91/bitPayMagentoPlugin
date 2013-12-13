<?php
global $bpconfig;
$bpconfig['host']="bitpay.com";
$bpconfig['port']=443;
$bpconfig['hostAndPort']=$bpconfig['host'];
if ($bpconfig['port']!=443)
	$bpconfig['hostAndPort'].=":".$bpconfig['host'];
$bpconfig['ssl_verifypeer']=1;
$bpconfig['ssl_verifyhost']=2;
//include custom config overrides if it exists
try {
	include 'bp_config.php';
} catch (Exception $e) {
}
?>

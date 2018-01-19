#!/usr/bin/php
<?php

#############################################################################
# Licensed Materials - Property of HCL*
# (C) Copyright HCL Technologies Ltd. 2017, 2018 All rights reserved.
# * Trademark of HCL Technologies Limited
#############################################################################

$host = "";
$user = "";
$pwd = "";
$jsName = "";
$workStationName = "";
$port = "31116";
$validFrom = "";
$validTo = "";
$jsAlias = "";

if($argc < 6 || $argc > 7){
    echo "Usage: $argv[0] <tws_host> <tws_user> <password> <js_name> <workstation_name> [<js_alias>]\n";
    exit(1);
}

$host = $argv[1];
$user = $argv[2];
$pwd = $argv[3];
$jsName = $argv[4];
$workStationName = $argv[5];


if($argc >= 7){
    $jsAlias = $argv[6];
}

    
$service_url = "https://$host:$port/twsd/model/jobstream/header/query";

$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "root:Hclrome00");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json","How-Many: 1"));

$data = array(
            "filters" => array(
                "jobstreamFilter" => array(
                    "jobStreamName" => $jsName,
                    "workstationName" => $workStationName,
                    "validFrom" => $validFrom,
                    "validTo" => $validTo
                )
            )
        );
$data_json = json_encode($data);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

$response = curl_exec($ch);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    echo var_export($info),"\n";
    die('error occured during curl exec. ' . var_export(json_decode($response)->messages ,true) . "\n");
}

curl_close($ch);

echo "$response \n";

if(json_decode($response) == NULL){
    echo "job stream not found\n";
    exit(1);
}

$jsId = json_decode($response)[0]->id;

echo "the js id is: $jsId \n";

//now we can submit the js
date_default_timezone_set('UTC');
$now = date("Y-m-d\TH:i:s.000\Z");

$service_url = "https://$host:$port/twsd/plan/current/jobstream/$jsId/action/submit_jobstream";
$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "root:Hclrome00");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json"));

$data = array(
    "inputArrivalTime" => $now,
    "alias" => $jsAlias
);

$data_json = json_encode($data);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

$response = curl_exec($ch);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    echo var_export($info),"\n";
    die('error occured during curl exec. ' . var_export(json_decode($response)->messages ,true) . "\n");
}

curl_close($ch);

echo "$response \n";

?>
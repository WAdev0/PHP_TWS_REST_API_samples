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
$jobName = "";
$workStationName = "";
$taskString = "";
$port = "31116";

if($argc != 7){
    echo "Usage: $argv[0] <tws_host> <tws_user> <password> <job_name> <workstation_name> <task_string>\n";
    exit(1);
}

$host = $argv[1];
$user = $argv[2];
$pwd = $argv[3];
$jobName = $argv[4];
$workStationName = $argv[5];
$taskString = $argv[6];

    
$service_url = "https://$host:$port/twsd/model/jobdefinition";

$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "root:Hclrome00");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json"));

$data = array(
            "header" => array(
                "jobDefinitionKey" => array(
                    "name" => $jobName,
                    "workstationName" => $workStationName
                ),
                "description" => "Added by REST API.",
                "taskType" => "UNIX",
                "userLogin" => $user
            ),
            "taskString" => $taskString,
            "recoveryOption" => "STOP"
        );
$data_json = json_encode($data);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

$response = curl_exec($ch);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 201) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    echo var_export($info),"\n";
    die('error occured during curl exec. ' . var_export(json_decode($response)->messages ,true) . "\n");
}

curl_close($ch);

echo "$response\n";

?>
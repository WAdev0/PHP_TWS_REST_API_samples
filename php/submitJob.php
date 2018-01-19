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
$jobAlias = "";
$jobWorkStationName = "";
$jsInternalIdentifier = "";
$jsWorkstationName = "";
$port = "31116";

if($argc < 8 || $argc > 9){
    echo "Usage: $argv[0] <tws_host> <tws_user> <password> <job_name> <job_alias> <job_workstation_name> <job_stream_id> [<js_workstation_name>]\n";
    exit(1);
}

$host = $argv[1];
$user = $argv[2];
$pwd = $argv[3];
$jobName = $argv[4];
$jobAlias = $argv[5];
$jobWorkStationName = $argv[6];
$jsInternalIdentifier = $argv[7];

$jsWorkstationName = $argv[6];
if($argc >= 9){
    $jsWorkstationName = $argv[8];
}

# first rest call to get the jd id

$service_url = "https://$host:$port/twsd/model/jobdefinition/header/query";

$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "root:Hclrome00");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json","How-Many: 1"));

$data = array(
            "filters" => array(
                "jobDefinitionFilter" => array(
                    "jobDefinitionName" => $jobName,
                    "workstationName" => $jobWorkStationName
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
    echo "job not found\n";
    exit(1);
}

$jobId = json_decode($response)[0]->id;

echo "the job id is: $jobId \n";

# now we get the job in plan instance

$service_url = "https://$host:$port/twsd/plan/current/jobstream/$jsWorkstationName%3B$jsInternalIdentifier/action/submit_job";
$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "root:Hclrome00");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json"));

$data = array(
    "jobDefinitionId" => $jobId,
    "alias" => $jobAlias
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

$jobInplanInstance = json_decode($response);

# now we can submit the job into the js

$service_url = "https://$host:$port/twsd/plan/current/job/action/submit_ad_hoc_job";
$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "root:Hclrome00");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json"));

$data = array(
    "job" => $jobInplanInstance
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
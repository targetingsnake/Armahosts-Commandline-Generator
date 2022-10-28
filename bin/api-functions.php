<?php

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

function generateJson($array)
{
    echo json_encode($array);
}

function generateFalse($json = array())
{
    return array(
        "payload" => $json,
        "success" => false,
        "code" => 1
    );
}

/**
 * generates an success state as array with additional payload
 * @param array|int|bool|string $json optional payload
 * @return array success state as array
 */
function generateSuccessMAPI($json = array())
{
    return array(
        "payload" => $json,
        "success" => true,
        "code" => 0
    );
}

function getFolderNames($json) {
    $steamIds = $json['ids'];
    $result = array();
    foreach ($steamIds as $steamId) {
        $folderName = getFoldernameByID($steamId);
        if (count($folderName) != 0) {
            $result[$steamId] = $folderName['foldername'];
        }
    }
    return $result;
}

function getUnmappedFolders() {
    $dbRes = getUnmappedFolder();
    $result = array();
    foreach ($dbRes as $res) {
        $result[] = $res['foldername'];
    }
    return $result;
}

function addModIdFolderName($json) {
    $foldername = $json['fn'];
    $id = $json['id'];
    $name = $json['name'];
    addModMapByFoldername($foldername, $id, $name);
}

function getAllServerFolder() {
    if (!checkForAdmin()){
        return generateFalse();
    }
    $con = ftpConnect();
    $list = requestDirectoryList($con, true);
    ftp_close($con);
    return generateSuccessMAPI($list);
}

function updateModDataAPI($json) {
    if (!checkForAdmin()){
        return generateFalse();
    }
    $list = $json['data'];
    $result = getModDataFromServer($list);
    if (!is_array($result)) {
        return generateSuccessMAPI(false);
    }
    return generateSuccessMAPI($result);
}

function updateModDatabase($json) {
    if (!checkForAdmin()){
        return generateFalse();
    }
    return generateSuccessMAPI(updateMods($json['data']));
}
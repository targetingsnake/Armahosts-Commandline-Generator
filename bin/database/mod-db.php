<?php

/**
 * This File includes all needed functions for roles-table
 *
 * @package default
 */

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

/**
 * Selects role based on unique role identifier
 * @param int $id unique role identifier of role which should be selected
 * @return array|bool|null structured result data if $id was existent
 */
function getFoldernameByID($id)
{
    $prep_stmt = "select `foldername` from `" . config::$SQL_PREFIX . "mods` WHERE `steam-id` = :id AND `existing` = '1';";
    $params = array();
    $params[0] = array();
    $params[0]['typ'] = 's';
    $params[0]['val'] = $id;
    $params[0]['nam'] = ":id";
    $result = ExecuteStatementWR($prep_stmt, $params);
    if (count($result) == 0) {
        return array();
    }
    return $result[0];
}

function getAllModDataOrderedByID () {
    $prep_stmt = 'select * from `' . config::$SQL_PREFIX . 'mods` ;';
    $params = array();
    $resultTemp = ExecuteStatementWR($prep_stmt, $params);
    if (count($resultTemp) == 0) {
        return array();
    }
    $result = array();
    foreach ($resultTemp as $mod) {
        $id = is_null($mod['steam-id']) ? $mod['foldername'] : $mod['steam-id'];
        $result[$id] = array(
            "folder" => $mod['foldername'],
            "id" => $mod["steam-id"],
            "name" => $mod["name"]
        );
    }
    return $result;
}

/**
 * Selects role based on unique role identifier
 * @param int $id unique role identifier of role which should be selected
 * @return array|bool|null structured result data if $id was existent
 */
function getUnmappedFolder()
{
    $prep_stmt = 'select `id`, `foldername` from `' . config::$SQL_PREFIX . 'mods` WHERE `steam-id` IS NULL;';
    $params = array();
    $result = ExecuteStatementWR($prep_stmt, $params);
    return $result;
}


function addModMapByFoldername($foldername, $id, $name, $server = 1, $existing=true)
{
    $prep_stmt = 'REPLACE INTO `' . config::$SQL_PREFIX . 'mods` ( `name` , `steam-id` , `foldername` , `existing` , `server` ) VALUES ( :mname , :id , :fn, :ex , :sr) ;';
    $params = array();
    $params[0] = array();
    $params[0]['typ'] = 's';
    $params[0]['val'] = is_null($id) ? "" : $id;
    $params[0]['nam'] = ":id";
    $params[1] = array();
    $params[1]['typ'] = 's';
    $params[1]['val'] = is_null($name) ? "" : $name;
    $params[1]['nam'] = ":mname";
    $params[2] = array();
    $params[2]['typ'] = 's';
    $params[2]['val'] = $foldername;
    $params[2]['nam'] = ":fn";
    $params[3] = array();
    $params[3]['typ'] = 's';
    $params[3]['val'] = $existing ? 1 : 0;
    $params[3]['nam'] = ":ex";
    $params[4] = array();
    $params[4]['typ'] = 's';
    $params[4]['val'] = $server;
    $params[4]['nam'] = ":sr";
    $disableNull = true;
    if ($id === "" || $name === "" ) {
        $disableNull = false;
    }
    $result = ExecuteStatementWR($prep_stmt, $params, false, $disableNull);
    return $result;
}

/**
 * deletes a role
 * @param int $rid unique role identifier of role which should be removed
 */
function deleteModEntryByFoldername($foldername)
{
    $prep_stmt = "DELETE FROM `" . config::$SQL_PREFIX . "mods` WHERE `foldername` = :fn";
    $params = array();
    $params[0] = array();
    $params[0]['typ'] = 's';
    $params[0]['val'] = $foldername;
    $params[0]['nam'] = ":fn";
    dump($params, 8);
    ExecuteStatementWR($prep_stmt, $params, false);
}

function updateModMapByFoldername($foldername, $id, $name, $server, $existing=true )
{
    $prep_stmt = 'update `' . config::$SQL_PREFIX . 'mods` set `name` = :mname , `foldername` = :fn , `existing` = :ex , `server` = :sr where `steam-id` = :id ;';
    $params = array();
    $params[0] = array();
    $params[0]['typ'] = 's';
    $params[0]['val'] = is_null($id) ? "" : $id;
    $params[0]['nam'] = ":id";
    $params[1] = array();
    $params[1]['typ'] = 's';
    $params[1]['val'] = is_null($name) ? "" : $name;
    $params[1]['nam'] = ":mname";
    $params[2] = array();
    $params[2]['typ'] = 's';
    $params[2]['val'] = $foldername;
    $params[2]['nam'] = ":fn";
    $params[3] = array();
    $params[3]['typ'] = 's';
    $params[3]['val'] = $existing ? 1 : 0;
    $params[3]['nam'] = ":ex";
    $params[4] = array();
    $params[4]['typ'] = 's';
    $params[4]['val'] = $server;
    $params[4]['nam'] = ":sr";
    $disableNull = true;
    if ($id === "" || $name === "" ) {
        $disableNull = false;
    }
    $result = ExecuteStatementWR($prep_stmt, $params, false, $disableNull);
    return $result;
}

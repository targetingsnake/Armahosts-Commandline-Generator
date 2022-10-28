<?php

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}


function addLogEntry($text) {
    $prep_stmt = 'INSERT INTO `' . config::$SQL_PREFIX . 'log` ( `message` ) VALUES ( ? );';
    $params = array();
    $params[0] = array();
    $params[0]['typ'] = 's';
    $params[0]['val'] = $text;
    $result = ExecuteStatementWOR($prep_stmt, $params);
    return $result;
}
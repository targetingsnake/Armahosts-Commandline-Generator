<?php
/**
 * Management api endpoint
 *
 * @package default
 */

/**
 * @const enables loading of other files without dying to improve security
 */
define('NICE_PROJECT', true);
require_once "bin/inc.php";
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    permissionDenied("Not Post");
}
if (key_exists("CONTENT_TYPE", $_SERVER) === false) {
    permissionDenied("Content type unset");
}
if ($_SERVER["CONTENT_TYPE"] !== "application/json") {
    permissionDenied("Content type");
}
$json = array();
if (count($_POST) > 0) {
    foreach (array_keys($_POST) as $key) {
        $json[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
    }
} else if (count($_GET) > 0) {
    foreach (array_keys($_GET) as $key) {
        $json[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
    }
} else {
    $input = file_get_contents('php://input');
    $json = decode_json($input);
}
switch ($json['type']) {
    case 'gef': //get Foldernames for modset
        generateJson(generateSuccessMAPI(getFolderNames($json)));
        break;
    case 'aef': //add mod - folder mapping
        addModIdFolderName($json);
        generateJson(generateSuccessMAPI());
        break;
    case 'umf': // request unmapped folder
        generateJson(generateSuccessMAPI(getUnmappedFolders()));
        break;
    case 'imu': //initiate Mod Update
        generateJson(getAllServerFolder());
        break;
    case 'umd': //updates Mod Data
        generateJson(updateModDataAPI($json));
        break;
    case 'udb':
        generateJson(updateModDatabase($json));
        break;
    default:
        $result = generateFalse("wrong request parameters.");
        generateJson($result);
        break;
}

<?php
if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}


/**
 * starts a ftp connection
 * @return resource|null ressource if connection is fine, else null
 */
function ftpConnect () {
    $conn = ftp_connect(config::$FTP_HOSTNAME , config::$FTP_PORT);
    if (ftp_login($conn, config::$FTP_USERNAME, config::$FTP_PASSWORD)) {
        ftp_chdir($conn, config::$FTP_PATH);
        ftp_pasv($conn, true);
        dump("connected", 4);
        return $conn;
    }
    dump("not connected", 4);
    return null;
}

/**
 * get's a list of files and folders
 * @param $con resource ftp connection
 * @return array empty if no mod is found
 */
function requestDirectoryList ($con, $filter= false) {
    $list = ftp_nlist($con, "");
    if (!$filter) {
        return $list;
    }
    $result = array();
    for ($i = 0; $i < count($list); $i++) {
        if ((substr($list[$i], 0, 1) === "@")){
            $result[] = $list[$i];
        }
    }
    return $result;
}

function getFileContent($con, $filename) {
    $helper_file = fopen('php://temp', 'r+');

    ftp_fget($con, $helper_file, $filename, FTP_BINARY, 0);

    $fstats = fstat($helper_file);
    fseek($helper_file, 0);
    $contents = fread($helper_file, $fstats['size']);
    fclose($helper_file);
    return $contents;
}

function changeDir ($con, $foldername) {
    ftp_chdir($con, config::$FTP_PATH .  "/" . $foldername);
}

function readModsCPP($con, $list) {
    $result = array();
    $failed = array();
    foreach ($list as $folder) {
        changeDir($con, $folder);
        $files = requestDirectoryList($con);
        if (!is_array($files)) {
            $errorMessage = "Folder " . $folder . " could not be read.";
            addLogEntry($errorMessage);
            $failed[] = $folder;
            continue;
        }
        if (!in_array("meta.cpp", $files))
        {
            $errorMessage = "Folder " . $folder . " is missing meta.cpp.";
            addLogEntry($errorMessage);
            $failed[] = $errorMessage;
            continue;
        }
        $content = getFileContent($con, "meta.cpp");
        $modData = readModCPP($content);
        if (!isset($modData["name"])) {
            if (in_array("mod.cpp", $files))
            {
                $modCpp = getFileContent($con, "mod.cpp");
                $modCpp = readModCPP($modCpp);
                if (isset($modCpp["description"])) {
                    $modData["name"] = $modCpp["description"];
                    $errorMessage = "Folder " . $folder . " mods is missing his name.";
                    addLogEntry($errorMessage);
                } else {
                    $modData["name"] = $folder;
                }
            } else {
                $errorMessage = "Folder " . $folder . " is missing meta.cpp.";
                addLogEntry($errorMessage);
                $modData["name"] = $folder;
            }
        }
        $result[$modData["publishedid"] == 0 ? $folder : $modData["publishedid"]] = array(
            "folder" => $folder,
            "id" => $modData["publishedid"],
            "name" => $modData["name"]
        );
    }
    changeDir($con, "");
    return array(
        'data' => $result,
        'failed' => $failed
    );
}

function getModDataFromServer($list) {
    $con = ftpConnect();
    $result = readModsCPP($con, $list);
    ftp_close($con);
    if (!is_array($result)) {
        return false;
    }
    return $result;
}
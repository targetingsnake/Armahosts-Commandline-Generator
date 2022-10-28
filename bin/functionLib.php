<?php
/**
 * This file contains functions, which are used all over the whole project
 *
 * @package default
 */

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

/**
 * Generates Header of Page (NavBar) and some modals
 * @param bool $LOGIN need to be true if user is logged in
 * @param bool $loginpage defines if header is on login/logout page
 * @param bool $rightpages defines if it is currently on a page of impressum or privacy policy
 */
function generateHeader()
{
    ?>
    <nav class="navbar navbar-dark navbar-expand-lg sticky-top bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/index.php">CMD-Gen</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon">
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/index.php">Home</a>
                    </li>
                    <li class="nav-item ms-3">
                        <?php
                        if (checkForAdmin()) {
                            ?>
                            <a class="btn btn-danger ml-3" onclick="updateModsInit();">Update Mods</a>
                            <?php
                        }
                        ?>
                    </li>
                </ul>
                <?php
                if (isset($_SESSION['name'])) {
                    if ($_SESSION['name'] != "") {
                        ?>
                        <a class="btn btn-secondary"
                           href="/index.php?<?php echo http_build_query(array('logout' => true), '', '&amp;'); ?>">Abmelden
                        </a>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </nav>
    </label><input type="text" value="<?php echo createCSRFtokenClient()?>" id="TokenScriptCSRF" hidden>
    <?php
}

/**
 * dumpes data, if debug mode is enabled and filters debug output based on config::$DEBUG_LEVEL
 * @param mixed $data data that should be printed for debug purposes
 * @param int $level If the value is greater or equal than config::$DEBUG_LEVEL the variable to debug will be printed out
 * @param bool $dark if true dump has dark background
 */
function dump($data, $level = -1, $dark = false)
{
    if ($level != -1) {
        if ($level > config::$DEBUG_LEVEL) {
            return;
        }
    }
    if (config::$DEBUG) {
        ?>
        <code style='display: block;white-space: pre-wrap;'>
            <?php
            var_dump($data);
            ?>
        </code>
        <br/>
        <br/>
        <?php
        if ($dark) {
            ?>
            <div class="bg-dark">
                <code style='display: block;white-space: pre-wrap;'>
                    <?php
                    var_dump($data);
                    ?>
                </code>
                <br/>
                <br/>
            </div>
            <?php
        }
    }
}

/**
 * returns a http-status Redirect
 * @param string $url URI where user should be redirected
 * @param bool $permanent sets if redirect has http-status-code 301 (permanent redirect) or 302 (temporary redirect) if true there will be 301 in response, default it is false
 */
function Redirect($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    die();
}

/**
 * generates a hmac string from a given string with the hmac secret from config.php
 * @param string $string inout string
 * @return string finished hmac
 */
function generateStringHmac($string)
{
    if (!is_string($string)) {
        die("No String!");
    }
    $hmac = hash_hmac("sha512", $string, config::$HMAC_SECRET);
    return $hmac;
}

/**
 * generates Validatable data for example for opening uapi.php, with timestamp
 * @param string $tokenstring input string, with all needed information encoded in it
 * @param string $time set time if not set, current server time will be used
 * @return array structured result wwith time, tokenstring and seccode (hmac)
 */
function generateValidatableDataMaterial($tokenstring, $time = "")
{
    if ($time === "") {
        $time = time();
    }
    $hmac = generateStringHmac($tokenstring . $time);
    $result = array(
        "token" => $tokenstring,
        "seccode" => $hmac,
        "time" => $time
    );
    return $result;
}

/**
 * checks if validatable data is correct used from e.g. uapi.php
 * @param array $data consists of timestamp, tokenstring and seccode (hmac)
 * @return bool true if hmac fits to given data
 */
function checkValidatableMaterial($data)
{
    if (isset($data['token'], $data['seccode'], $data['token']) === false) {
        die("wrong keys in array");
    }
    $hmac = generateValidatableDataMaterial($data['token'], $data['time']);
    if ($data['time'] + (24 * 60 * 60) < time()) {
        permissionDenied("Your time has exceeded.");
    }
    if (hash_equals($hmac['seccode'], $data['seccode'])) {
        return true;
    }
    permissionDenied();
    return false;
}

/**
 * generates a random sting as token and creates validatable data with this string and the username
 * @param string $username of user for which data is generated
 * @param string $tokenstring can be set, if not string will be randomly generated by parameters found in config file
 * @return array structured validatable data
 */
function generateValidatableData($username, $tokenstring = "")
{
    if ($tokenstring == "") {
        $tokenstring = generateRandomString();
    }
    $hmac = generateStringHmac($username . $tokenstring);
    $result = array(
        "token" => $tokenstring,
        "username" => $username,
        "seccode" => $hmac
    );
    return $result;
}

/**
 * generates Validatable data for example for changing a users password on its own
 * @param string $username username to generate timed validatable data
 * @param string $tokenstring input string, with all needed information encoded in it
 * @param string $time set time if not set, current server time will be used
 * @return array structured result wwith time, tokenstring and seccode (hmac) and username
 */
function generateValidatableDataTimed($username, $tokenstring = "", $time = "")
{
    if ($time === "") {
        $time = time();
    }
    $hmac = generateStringHmac($username . $tokenstring . $time);
    $result = array(
        "token" => $tokenstring,
        "username" => $username,
        "seccode" => $hmac,
        "time" => $time
    );
    return $result;
}

/**
 * generates string from validatable data
 * @param string $username username of user which should use the link
 * @param string $targetside path to target page of link in cosp
 * @param string $tokenstring can be set, if not string will be randomly generated by parameters found in config file
 * @return string resulting link from inputs
 */
function generateValidateableLink($username, $targetside, $tokenstring = "")
{
    if ($tokenstring == "") {
        $data = generateValidatableData($username);
    } else {
        $data = generateValidatableData($username, $tokenstring);
    }
    $result = "https://" . config::$DOMAIN . "/" . $targetside . "?" . http_build_query($data);
    return $result;
}

/**
 * generate link to cosp with validatable material
 *
 * @param string $username username of user which should use the link
 * @param string $targetside path to target page of link in cosp
 * @param string $tokenstring can be set, if not string will be randomly generated by parameters found in config file
 * @return string resulting link from inputs with timestamp
 */
function generateValidateableLinkTimed($username, $targetside, $tokenstring = "")
{
    if ($tokenstring == "") {
        $data = generateValidatableDataTimed($username);
    } else {
        $data = generateValidatableDataTimed($username, $tokenstring);
    }
    $result = "https://" . config::$DOMAIN . "/" . $targetside . "?" . http_build_query($data);
    return $result;
}

/**
 * checks if link from generateValidateableLink used by user is correct and data was not changed
 * @param array $data data from link (GET-Method)
 * @return bool true if data is correct
 */
function checkValidatableLink($data)
{
    if (isset($data['token'], $data['username'], $data['seccode']) === false) {
        die("wrong keys in array");
    }
    $hmac = "";
    if (isset($data['time'])) {
        $hmac = generateValidatableDataTimed($data['username'], $data['token'], $data['time']);
    } else {
        dump("validate version", 3);
        $hmac = generateValidatableData($data['username'], $data['token']);
    }
    dump($hmac, 4);
    dump($data, 4);
    if ($hmac !== "") {
        if (hash_equals($hmac['seccode'], $data['seccode'])) {
            dump('validated', 3);
            return true;
        }
    }
    dump('not validated', 3);
    return false;
}

/**
 * generates random string
 * @param int $length can be set, if not using parameters from config file
 * @param bool $special if true special chars are added
 * @return string random string with certain length
 */
function generateRandomString($length = -1, $special = false)
{
    if ($length === -1) {
        $length = config::$RANDOM_STRING_LENGTH;
    }
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($special) {
        $characters = $characters . ",.;:-_+#/*";
    }
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * denies access to a page if called
 * @param string $string optional deny message
 */
function permissionDenied($string = "")
{
    if ($string === "") {
        echo "You shall not pass!";
    } else {
        echo $string;
    }
    http_response_code(403);
    die();
}

/**
 * set http-code 418 (I'm a teapot), called if parameters of api not matching
 */
function ServerError()
{
    echo "Correct your parameters, until then im acting like a teapot!";
    http_response_code(418);
    die();
}

/**
 * generates standard header-content of html-page like javascript includes used everywhere
 * @param array $additional additional stylesheets or javascript could be inserted with this
 */
function generateHeaderTags($additional = array())
{
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">

    <?php
    //map for stylesheed to be included in header
    //key -> path + name to file;
    //value -> true: toggle with debug mode, false: outputs minified version every time
    $css_map = [
        "csse/bootstrap" => false,
        "fontawesome/css/all" => false,
        "css/main" => true,
    ];
    foreach ($css_map as $name => $use_debug) {
        echo '<link rel="stylesheet" type="text/css" href="'.$name.(!$use_debug || !config::$DEBUG? '.min' : '').'.css">';
    }

    //map for scriptfiles to be included in header
    //key -> path + name to file;
    //value -> true: toggle with debug mode, false: outputs minified version every time
    $js_map = [
        "jse/jquery-3.6.0" => false,
        "jse/bootstrap.bundle" => false,
        "fontawesome/js/all" => false
    ];

    foreach ($js_map as $name => $use_debug) {
        echo '<script type="text/javascript" src="'.$name.(!$use_debug || !config::$DEBUG? '.min' : '').'.js"></script>';
    }

    if (sizeof($additional) > 0) {
        foreach ($additional as $line) {
            $href = $line['hrefmin']?? $line['href'];  //php 7 syntax
            if (config::$DEBUG) {
                $href = $line['href'];
            }
            switch ($line['type']) {
                case 'style':
                case 'css':
                case 'link':
                    echo '<link rel="' . $line['rel'] . '" href="' . $href . '"'. (($line['typeval']?? '')? ' type="' . $line['typeval'] . '"': '') .'>';
                    break;
                case 'js':
                case 'script':
                    echo '<script type="' . $line['typeval'] . '" src="' . $href . '" ></script>';
                    break;
            }
        }
    }
}

/**
 * checks if a given role-id is existent
 * @param int $rid id of role which existent should be checked
 * @return bool true if id existent
 */
function checkRoleID($rid)
{
    $Roles = getAllRolles();
    foreach ($Roles as $Role) {
        if ($Role['id'] == $rid) {
            return true;
        }
    }
    return false;
}

/**
 * decodes a json from a string
 * @param string $string input json
 * @return mixed structured data from json as array
 */
function decode_json($string)
{
    return json_decode($string, true);
}

/**
 * checks if mail address seems to be valid
 * @param string $email given mail address
 * @return mixed false if mail address syntax is incorrect
 */
function checkMailAddress($email)
{
    $result = filter_var($email, FILTER_VALIDATE_EMAIL);
    dump("Email-Address-Checker:" . $email, 8);
    dump($result, 8);
    return $result;
}

/**
 * checks if user has the required permission
 * @param int $requiredPermission permission value needed to access page
 */
function checkPermission($requiredPermission)
{
    dump($_SESSION, 5);
    dump($requiredPermission, 5);
    if ($_SESSION['role'] < $requiredPermission) {
        permissionDenied("#39: There is no such thing as a coincidence.");
    }
}

/**
 * hashes a string with sha512
 * @param string $string given string
 * @return string sha512 value of string
 */
function hashString($string)
{
    return hash('sha512', $string);
}

/**
 * sends passwort reset mail to user
 * @param string $username username of user which requested to change his password
 * @param string $email mail to send reset password to
 */
function PasswordResetViaMail($username, $email)
{
    $completeLink = generateValidateableLinkTimed($username, "resetPwd.php");
    dump($email, 3);
    //sendMail($email, "Neusetzen des Passwortes für " . $username, MailTemplates::ResetPasswordByMail($completeLink, $username), true);
}



/**
 * checks if a user is listed as staff
 * @param string $name username of user, which should be checked
 * @return bool True if user is Staff
 */
function isStaff($name)
{
    $user = getUserData($name);
    $roleval = getRoleByID($user['role'])['value'];
    if ($roleval >= config::$ROLE_EMPLOYEE) {
        return true;
    }
    return false;
}

/**
 * get ip adress of user
 * @return string ip adress of user
 */
function getUserIp(){
    $ip = "0.0.0.0";
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * checks if mailadress is already in database
 * @param string $email emailadress to check
 * @param bool $debug_enable enables all times false in debug mode
 * @return bool true if email is already known
 */
function checkMailAddressExistent($email, $debug_enable = false) {
    $users = getAllUsers();
    $mails = array();
    foreach ($users as $user) {
        $mails[] = $user['email'];
    }
    if (config::$DEBUG && $debug_enable == true){
        return false;
    }
    return in_array($email, $mails);
}

/**
 * sends user his name via his mailadress
 * @param string $mailadress mailadress of the user
 */
function sendusernameByMail($mailadress) {
    $userdata = getUserDataByMailadress($mailadress);
    $username = $userdata['name'];
    dump($username, 2);
    //sendMail($userdata['email'], "Ihr Nutzername auf " . config::$MAIN_CAPTION, MailTemplates::SendUsernameByMail($userdata['name']), true, config::$SENDER_ADDRESS);
}

/**
 * reads Mods CPP data into good format
 * @param $content
 * @return array
 */
function readModCPP($content) {
    $split = explode(PHP_EOL, $content);
    $result = array();
    foreach ($split as $part) {
        if (strlen($part) == 0) {
            continue;
        }
        $changed = explode("=", $part);
        for ($i = 0; $i < count($changed) - 1; $i = $i+2) {
            $name = trim($changed[$i]);
            $value = trim($changed[$i+1]);
            $value = str_replace(array('"', "'", ";"), "", $value);
            $result[$name] = $value;
        }
    }
    return $result;
}

function updateMods($files) {
    $db = getAllModDataOrderedByID();
    dump($files, 4);
    $modsWithFalseSteamID = array();
    $missingSteamIds = array();
    $nonExisting = array_diff_key($db, $files);
    foreach ($nonExisting as $mod) {
        if (is_null($mod["id"]) && is_null($mod["folder"])) {
            dump($mod, 8);
            deleteModEntryByFoldername($mod["folder"]);
            continue;
        }
        if ($mod['id'] == 0 || is_null($mod["id"])) {
            dump($mod, 8);
            $mod['id'] = "";
            if (array_key_exists($mod['name'], $modsWithFalseSteamID)){
                $mod['id'] = $modsWithFalseSteamID[$mod['name']];
            } else {
                $missingSteamIds[$mod['folder']] = array(
                    'name' => [$mod['name']],
                    'folder' => $mod["folder"],
                    'currentID' => $mod['id']
                );
            }
        }
        updateModMapByFoldername($mod["folder"], $mod["id"], $mod['name'], 1, false);
    }
    $newMods = array_diff_key($files, $db);
    foreach ($newMods as $mod) {
        if ($mod['id'] == 0) {
            dump($mod, 8);
            $mod['id'] = "";
            if (array_key_exists($mod['name'], $modsWithFalseSteamID)){
                $mod['id'] = $modsWithFalseSteamID[$mod['name']];
            } else {
                $missingSteamIds[$mod['folder']] = array(
                    'name' => [$mod['name']],
                    'folder' => $mod["folder"],
                    'currentID' => $mod['id']
                );
            }
        }
        addModMapByFoldername($mod["folder"], $mod["id"], $mod['name'], 1);
    }
    $existingMods = array_intersect_key($files, $db);
    foreach ($existingMods as $mod) {
        if ($mod['id'] == 0) {
            dump($mod, 8);
            $mod['id'] = "";
            if (array_key_exists($mod['name'], $modsWithFalseSteamID)){
                $mod['id'] = $modsWithFalseSteamID[$mod['name']];
            } else {
                $missingSteamIds[$mod['folder']] = array(
                    'name' => [$mod['name']],
                    'folder' => $mod["folder"],
                    'currentID' => $mod['id']
                );
            }
        }
        updateModMapByFoldername($mod["folder"], $mod["id"], $mod['name'], 1 );
    }
    return array(
        "WSI" => $missingSteamIds,
        "UM" => $newMods
    );
}

function checkForAdmin () {
    return in_array($_SERVER['PHP_AUTH_USER'], config::$ADMINS);
}
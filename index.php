<?php
/**
 * frontpage of platform
 *
 * @package default
 */

/**
 * @const enables loading of other files without dying to improve security
 */
define('NICE_PROJECT', true);
require_once "bin/inc.php";
$logout = false;
$username = "";
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = "test";
    $_SESSION['role'] = 20;
    Redirect("cmd-gen.php", false);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (count($_POST) > 0) {
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $csrf = filter_input(INPUT_POST, 'FormTokenScriptCSRF', FILTER_SANITIZE_STRING);
        if (!isset($_SESSION['csrf'])){
            Redirect('index.php', false);
        }
        if (!checkCSRFtoken($csrf)) {
            Redirect('index.php', false);
        }
        //$result = checkPassword($password, $username);

        //temporary break login

        $_SESSION['name'] = "test";
        $_SESSION['role'] = 20;
        Redirect("cmd-gen.php", false);
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (count($_GET) > 0 && isset($_GET['logout'])) {
        $logout = filter_input(INPUT_GET, 'logout', FILTER_SANITIZE_STRING);
    }
    if (count($_GET) > 0 && isset($_GET['type'], $_GET['username'])) {
        $username_rest = filter_input(INPUT_GET, "username", FILTER_SANITIZE_STRING);
        $type_rest = filter_input(INPUT_GET, "type", FILTER_SANITIZE_STRING);
        if ($type_rest == "rup") {
            $userdata = getUserData($username_rest);
            PasswordResetViaMail($username_rest, $userdata['email']);
            $verhalten = 4;
        }
    }
}
if ($logout) {
    session_destroy();
    Redirect("/index.php", false);
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anmelden</title>
    <?php
    generateHeaderTags();
    ?>
</head>
<body>
<?php
generateHeader();
?>
    <div class=" position-absolute top-50 start-50 translate-middle">
        <div class="card bg-secondary" style="width: 20rem">
            <div class="card-body">
                <div class="card-text">
                    <form method="post" action="index.php">
                        <input type="text" value="<?php echo createCSRFtokenClient()?>" id="FormTokenScriptCSRF" name="FormTokenScriptCSRF" hidden>
                        <div class="input-group mb-3">
                            <span class="input-group-text" style="width: 45px">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" name="username" class="form-control"
                                   placeholder="Username">

                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" style="width: 45px">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Password">
                        </div>
                        <input type="hidden" id="FormTokenScriptCSRF" name="FormTokenScriptCSRF" value="<?php echo createCSRFtokenClient() ?>">
                        <div class="form-group d-flex">
                            <input type="submit" name="Einloggen" class="btn btn-success"
                                   value="Login">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
if (config::$DEBUG) {
    dump($_SESSION);
}
?>

<div class="modal fade col-6 offset-3" id="CookieBannerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
        <!-- because normal overflow-y: auto is displaying scrollbar next to modal and not on right side of browser window-->
        <div class="modal-content">
            <div class="modal-header" style="color: black">
                <h5 class="modal-title" style="color: white">Cookies und Datenschutz</h5>
            </div>
            <div class="modal-body modal-body-unround" style="" id="CookieModalBody">
                <div class="container">
                    <div class="text-light">
                        Diese Website nutzt Cookies, um die gewünschte Funktionalität zu erbringen und zu verbessern.
                        Durch Nutzung dieser Seite akzeptieren Sie Cookies. <a href="privacy-policy.php"
                                                                               class="text-light">Weitere
                            Informationen</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-important" id="CookieAcceptButton"
                        onclick="AcceptCookies();">
                    Akzeptieren
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
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
if (!isset($_SESSION['name'])) {
    Redirect("/index.php");
} else {
    if ($_SESSION['name'] == "") {
        Redirect("/index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Commandline Generator</title>
    <?php
    generateHeaderTags(
        array(
            array(
                'href' => 'tjs/cmdGenMainLib.js',
                'hrefmin' => 'tjs/cmdGenMainLib.min.js',
                'type' => 'script',
                'typeval' => 'text/javascript'
            )
        )
    );
    ?>
</head>
<body>
<?php
generateHeader();
?>
<div id="card-upload" class="position-absolute top-50 start-50 translate-middle mt-3">
    <div class="card bg-secondary" style="width: 50rem; height: 40rem">
        <div id="drop_zone" class="position-absolute top-50 start-50 translate-middle" style="width: 48rem; height: 38rem" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);">
            <div class="position-absolute top-50 start-50 translate-middle">
                <i class="fa-solid fa-file-arrow-up fa-7x"></i>
            </div>
        </div>
    </div>
</div>
<div id="card-display" class="mt-2 ms-auto me-auto align-items-center display-none">
    <div class="card bg-secondary ms-auto me-auto" style="width: 70rem; height: 40rem"">
        <div class="position-absolute top-50 start-50 translate-middle" style="width: 68rem; height: 38rem;">
            <div id="drop_display" class="custom-scrollbar-css" style="color: white; background:black" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);">

            </div>
        </div>
    </div>
</div>
<div id="card-warning" class="mt-2 ms-auto me-auto align-items-center display-none">
    <div class="card bg-danger ms-auto me-auto p-2" style="width: 70rem; height: auto">
        <div id="warningText" class="text-bg-danger text-center" >
            Not all mods are from Steam or could be mapped to a folder.
        </div>
        <div id="warningTextMods" class="text-bg-danger" >
        </div>
    </div>
</div>
<div id="card-commandline" class="mt-2 ms-auto me-auto align-items-center display-none">
    <div class="card bg-secondary ms-auto me-auto p-2" style="width: 70rem; height: auto">
        <div class="bg-dark p-2 code">
            <code id="Commandline" class="text-bg-dark">

            </code>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-bg-dark">
            <div class="modal-header bg-dark">
                <h5 class="modal-title" id="staticBackdropLabel">Mod not found</h5>
            </div>
            <div class="modal-body bg-body-main">
                <form>
                    <div class="mb-3">
                        <label for="ModName" class="form-label">Name of mod</label>
                        <input type="text" class="form-control" id="ModName" name="ModName" disabled="disabled" aria-describedby="mod name">
                    </div>
                    <div class="mb-3 form-switch">
                        <input type="checkbox" class="form-check-input" role="switch"  id="AddNewFolderCheck" onchange="ToggleFolderSelect();">
                        <label class="form-check-label" for="AddNewFolderCheck">Add new Folder</label>
                    </div>
                    <div id="SelectExitingFolderDiv" class="mb-3">
                        <label for="existingModList" class="form-label">Select existing folder</label>
                        <select class="form-select" id="existingModList" aria-label="Default select example">
                            <option selected>Open this select menu</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div id="SelectNewFolderDiv" class="mb-3 display-none">
                        <label for="ModFolderName" class="form-label">Name of new folder</label>
                        <input type="text" class="form-control" id="ModFolderName" name="ModFolderName" aria-describedby="mod folder name">
                    </div>
                    <div id="SteamIDdiv" class="mb-3 display-none">
                        <label for="ModSteamID" class="form-label">Name of new folder</label>
                        <input type="text" class="form-control" id="ModSteamID" name="ModSteamID" disabled="disabled" aria-describedby="mod steam id">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="aboardSubmit()">Skip</button>
                <button type="button" class="btn btn-success" onclick="submitFolderMapping()">Confirm and Next</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="updateModsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content text-bg-dark">
            <div class="modal-header bg-dark">
                <h5 class="modal-title" id="staticBackdropLabel">Updating existing mods</h5>
            </div>
            <div class="modal-body bg-body-main">
                <div class="progress">
                    <div id="modUpdateProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="ModUpdateText">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" disabled="disabled" id="closeUpdateModsModalBtn">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
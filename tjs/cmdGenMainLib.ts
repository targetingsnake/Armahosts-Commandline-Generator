var Mods = [];
var unknownMods = [];
var SkipperMods = [];
var Commandline = [];
var localMods = false;
var ModFolderModal = null;
var updateModModal = null;
var Progress = 0;
var Modlist = [];
var ModPos = 0;
var ModStepPercentage = 100;
var ModData = {};
var failedMods = [];

window.onload = function () {
    ModFolderModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {backdrop: 'static'})
    updateModModal = new bootstrap.Modal(document.getElementById('updateModsModal'), {backdrop: 'static'})
}

function dropHandler(ev) {
    // Prevent default behavior (Prevent file from being opened)
    ev.preventDefault();
    if (ev.dataTransfer.items.length <= 0) {
        return
    }
    let item = ev.dataTransfer.items[0];
    if (item.kind === 'file') {
        const file = item.getAsFile();
        readFile(file);
    }
}

function dragOverHandler(ev) {
    ev.preventDefault();
}

function hideElement(elementID : string, state = true) :void {
    let uploadCard = document.getElementById(elementID);
    let clases = uploadCard.getAttribute('class');
    if (state) {
        clases = clases + ' display-none';
    } else {
        clases = clases.replace(' display-none', '');
    }
    uploadCard.setAttribute("class", clases);
}

function unhideElement(elementID : string) :void {
    hideElement(elementID, false);
}

function readFile(file: File): void {
    var reader = new FileReader();
    reader.readAsText(file);
    reader.onload = function () {
        let text = reader.result as string;
        displayHtmlAndCreateCMDLine(text);
    }
}

function displayHtmlAndCreateCMDLine(text: string) :void {
    let displayElement = document.getElementById('drop_display');
    let shadowOpen = null;
    if (!displayElement.shadowRoot) {
        shadowOpen = displayElement.attachShadow({mode: 'open'});
    } else {
        shadowOpen = displayElement.shadowRoot;
    }
    shadowOpen.innerHTML = text;
    getModData(shadowOpen);

    hideElement('card-upload');
    unhideElement('card-display');
}

function getModData(shadowRoot) : void {
    let entries = shadowRoot.querySelectorAll('[data-type="ModContainer"]');
    console.log("test");
    for (let i = 0; i < entries.length; i++) {
        let srourceMod = entries[i].getElementsByTagName('span');
        srourceMod = srourceMod[0].innerText;
        if (srourceMod != "Steam") {
            localMods = true;
        } else {
            let name = entries[i].querySelector('[data-type="DisplayName"]')
            name = name.innerText
            let link = entries[i].getElementsByTagName('a');
            link = link[0].href;
            link = link.replace("https", "http");
            link = link.replace('http://steamcommunity.com/sharedfiles/filedetails/?id=', '');
            console.log(link);
            var ModData = {
                displayName: name,
                id: link
            };
            Mods.push(ModData);
        }
    }
    RequestFolderNames()
}

function RequestFolderNames() {
    if (Mods.length == 0) {
        return;
    }
    let ModIds = [];
    for (let i = 0; i < Mods.length; i++) {
        ModIds.push(Mods[i].id);
    }
    let result = sendApiRequest({
        type: 'gef',
        ids: ModIds
    }, false);
    for (let i = 0; i < Mods.length; i++) {
        if (Mods[i].id in result) {
            Commandline.push({
                id: Mods[i].id,
                name: Mods[i].displayName,
                folder: result[Mods[i].id]
            });
        } else {
            unknownMods.push(Mods[i]);
        }
    }
    if (unknownMods.length > 0) {
        loadModSelectModal();
        return;
    }
    showResult()
}

function loadModSelectModal() {
    if (unknownMods.length == 0) {
        return;
    }
    let unmapped = sendApiRequest({
        type: 'umf'
    }, false);
    let entries = "";
    for(let i = 0; i < unmapped.length; i++){
        let entry = '<option value="' + unmapped[i] + '">' + unmapped[i] + '</option>';
        entries += entry;
    }
    document.getElementById("existingModList").innerHTML = entries;
    let mod = unknownMods.pop();
    let ModName = mod.displayName;
    let modId = mod.id;
    let idField = document.getElementById('ModSteamID') as HTMLInputElement;
    let NameField = document.getElementById('ModName') as HTMLInputElement;
    idField.value = modId;
    NameField.value = ModName;
    ModFolderModal.show();
}

function submitFolderMapping() {
    let idField = document.getElementById('ModSteamID') as HTMLInputElement;
    let NameField = document.getElementById('ModName') as HTMLInputElement;
    let selectField = document.getElementById('existingModList') as HTMLSelectElement;
    let checkbox = document.getElementById('AddNewFolderCheck') as HTMLInputElement;
    let ModFoldernameInput = document.getElementById('ModFolderName') as HTMLInputElement;
    let modId = idField.value;
    let modName = NameField.value;
    let modFolder = "";
    if (checkbox.checked) {
        modFolder = ModFoldernameInput.value;
        ModFoldernameInput.value = "";
    } else {
        modFolder = selectField.options[selectField.selectedIndex].value;
    }
    Commandline.push({
        id: modId,
        name: modName,
        folder: modFolder
    });
    sendApiRequest({
        type: 'aef',
        id: modId,
        fn: modFolder,
        name: modName
    }, false);
    if (unknownMods.length > 0) {
        loadModSelectModal();
        return;
    }
    ModFolderModal.hide();
    showResult()
}

function aboardSubmit() {
    localMods = true;
    let idField = document.getElementById('ModSteamID') as HTMLInputElement;
    let NameField = document.getElementById('ModName') as HTMLInputElement;
    let modId = idField.value;
    let modName = NameField.value;
    SkipperMods.push({
        id: modId,
        name: modName
    });
    if (unknownMods.length > 0) {
        loadModSelectModal();
        return;
    }
    ModFolderModal.hide();
    showResult();
}

function showResult() {
    let resultOutput = document.getElementById('Commandline');
    let skippedModsOutput = document.getElementById('warningTextMods');
    let cmdLine = "";
    for (let i = 0; i < Commandline.length; i++) {
        cmdLine += Commandline[i].folder + ";"
    }
    resultOutput.innerText = cmdLine;
    unhideElement('card-commandline')
    let skippedMods = document.createElement('ul');
    skippedMods.setAttribute('class', 'list-group text-bg-danger border-none rounded-4')
    for (let i = 0; i < SkipperMods.length; i ++) {
        let entry = document.createElement('li');
        let checkboxDiv = document.createElement("div");
        checkboxDiv.setAttribute('class', 'form-check')
        let checkBox = document.createElement("input") as HTMLInputElement;
        checkBox.setAttribute('class', 'form-check-input')
        checkBox.setAttribute('type', 'checkbox')
        checkBox.setAttribute('name',  SkipperMods[i].id);
        //checkBox.setAttribute('onchange', 'checkUnmatechedMods(this)')
        checkBox.onchange = checkUnmatechedMods;
        checkBox.value = '';
        checkBox.setAttribute('id', SkipperMods[i].id);
        checkboxDiv.appendChild(checkBox);
        let CheckBoxDesc = document.createElement('label') as HTMLLabelElement;
        CheckBoxDesc.setAttribute('class', 'form-check-label');
        CheckBoxDesc.setAttribute('for', SkipperMods[i].id);
        CheckBoxDesc.innerText = SkipperMods[i].name;
        checkboxDiv.appendChild(CheckBoxDesc);
        entry.appendChild(checkboxDiv);
        entry.setAttribute('class', 'list-group-item text-bg-danger border-none')
        skippedMods.appendChild(entry);
    }
    let children = skippedModsOutput.children;
    for (let j = 0; j < children.length; j++) {
        skippedModsOutput.removeChild(children[j]);
    }
    skippedModsOutput.appendChild(skippedMods);
    hideElement('card-warning', !localMods);
    Mods = [];
    unknownMods = [];
    SkipperMods = [];
    Commandline = [];
    localMods = false;
}

function checkUnmatechedMods(ev: Event) {
    console.log(ev);
    let element = ev.target as HTMLInputElement;
    let parent = element.parentElement.parentElement;
    let classes = parent.getAttribute('class');
    if (element.checked) {
        classes = classes.replace('text-bg-danger', "text-bg-success")
    } else {
        classes = classes.replace('text-bg-success', "text-bg-danger");
    }
    parent.setAttribute('class', classes);
}

function ToggleFolderSelect() {
    let checkbox = document.getElementById('AddNewFolderCheck') as HTMLInputElement;
    let ExistingFolder = 'SelectExitingFolderDiv';
    let NewFolder = 'SelectNewFolderDiv';
    if (checkbox.checked) {
        hideElement(ExistingFolder);
        unhideElement(NewFolder);
    } else {
        unhideElement(ExistingFolder);
        hideElement(NewFolder);
    }
}

/**
 * sends API-Request to mapi
 * @param {json} json data to transmit
 * @param {boolean} reload enables or disables page reload
 * @returns {array} is in json form and is already parsed

 */

function sendApiRequest(json, reload) {

    var csrfTokenField = document.getElementById('TokenScriptCSRF') as HTMLInputElement;
    json.csrf = csrfTokenField.value;
    reload = reload || false;
    var otherReq = new XMLHttpRequest();
    otherReq.open("POST", "api.php", false);
    otherReq.withCredentials = true;
    otherReq.setRequestHeader("Content-Type", "application/json");
    otherReq.send(JSON.stringify(json));
    var resp = otherReq.responseText;
    var result = JSON.parse(resp);

    if (result.code > 0) {
        throw new Error("Something went badly wrong!");
    }
    if (reload) {
        location.reload();
    }
    return result.payload;
}

function updateModsInit() {
    if (!window.confirm("Really update Mods?")) {
        return;
    }
    let ModUpdateText = document.getElementById('ModUpdateText');
    ModUpdateText.innerHTML = "";
    updateModModal.show();
    setTimeout(function () {
        Progress = 0;
        let modsOnServer = sendApiRequest({type: 'imu'}, false);

        let countOfMods = modsOnServer.length;
        let runs = Math.ceil(countOfMods / 10) + 2;
        ModStepPercentage = 100 / runs;
        ModPos = 0;
        for (let i = 0; i< countOfMods; i = i+10) {
            let testAR = [];
            for (let j = 0; i+j < countOfMods && j < 10 ; j++) {
                testAR.push(modsOnServer[i+j]);
            }
            Modlist.push(testAR);
        }
        updateMods();
        Progress += ModStepPercentage;
        updateProgressbar();
    }, 1000)

}

function updateMods() {
    setTimeout(function () {
        let result = sendApiRequest(
            {
                type: "umd",
                data: Modlist[ModPos]
            }, false
        );
        if (typeof result != "object") {
            alert("update gone wrong");
            return;
        }
        ModData = {...ModData,...result['data']};
        failedMods = failedMods.concat(result['failed']);
        ModPos += 1;
        Progress += ModStepPercentage;
        updateProgressbar();
        if (ModPos >= Modlist.length){
            transmitFinalModData();
        } else {
            updateMods();
        }
    }, 1000)
}

function transmitFinalModData() {
    setTimeout(function () {
        sendApiRequest(
            {
                type: "udb",
                data: ModData
            }, false
        );
        Progress = 100;
        updateProgressbar();
        finalizeUpdate();
    }, 1000);
}

function finalizeUpdate() {
    let BtnModalClose = document.getElementById('closeUpdateModsModalBtn');
    BtnModalClose.removeAttribute('disabled');
    let ModUpdateText = document.getElementById('ModUpdateText');
    let updateText = "";
    if (failedMods.length > 0) {
        updateText += "<div class=\"card text-bg-danger mt-2 w-100\" style=\"width: 18rem;\">\n" +
            "  <div class=\"card-body\">\n" +
            "    <h5 class=\"card-title\">Failed Mods</h5>\n" +
            "   <p class=\"card-text\">\n";
        for (let i = 0; i < failedMods.length; i++){
            if (i != 0) {
                updateText += ", "
            }
            updateText += failedMods[i];
        }
        updateText +=
            "     </p>\n"+
            "  </div>\n" +
            "</div>";
    }
    let keys = Object.keys(ModData);
    updateText += "<div class=\"card text-bg-secondary mt-2 w-100\" style=\"width: 18rem;\">\n" +
        "  <div class=\"card-body\">\n" +
        "    <h5 class=\"card-title\">Updated Mods </h5>\n" +
        "   <p class=\"card-text\">\n";
    for (let i = 0; i < keys.length; i++) {
        if (i != 0) {
            updateText += " "
        }
        updateText += ModData[keys[i]].name;
    }
    updateText +=
        "   </p>\n" +
        "  </div>\n" +
        "</div>";
    ModUpdateText.innerHTML = updateText;
}

function updateProgressbar() {
    let ProgressBar = document.getElementById("modUpdateProgressBar");
    ProgressBar.setAttribute("style", "width: " + Progress + "%" );
    ProgressBar.innerText = Math.round(Progress) + "%";
}
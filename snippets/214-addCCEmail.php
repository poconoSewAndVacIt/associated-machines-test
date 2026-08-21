$tpl = $modx->getOption("tpl", $scriptProperties, "addCCEmail");
$thanksTpl = $modx->getOption("thanksTpl", $scriptProperties, "thanksAddCCEmail");

// Load Constant Contact API
$ctct = $modx->getService('psvconstantcontact','PsvConstantContact', MODX_CORE_PATH . 'components/psvconstantcontact/model/psvconstantcontact/');
if (!($ctct instanceof PsvConstantContact)) return '';

if ($_REQUEST["type"] === "add" && isset($_REQUEST["profile"]) && isset($_REQUEST["lists"])) {
    $update = $ctct->addContact($_REQUEST["profile"], $_REQUEST["lists"]);
    
    if ($update) {
        return $modx->getChunk($thanksTpl);
    } else {
        return '<div class="callout alert">There was a problem adding your email, please try again. It this error continues, contact <a href="mailto:it@poconosewandvac.com">it@poconosewandvac.com</a></div>';
    }
}

return $modx->getChunk($tpl);
$id = $modx->getOption('id', $scriptProperties, 0);
$img = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";

if ($id === 0) {
    return $img;
}

$resource = $modx->getObject('modResource', $id);
if (!$resource) {
    return $img;
}

$smImg = $resource->getTVValue('Primary Product Image Small');
if ($smImg) {
    $img = $smImg;
} else {
    $img = $resource->getTVValue('Primary Product Image');
}

return $modx->runSnippet('imgix', [
    'input' => $img,
    'options' => 'w=100&h=100&fit=fill&bg=ffffff&auto=format&fm=png'
]);
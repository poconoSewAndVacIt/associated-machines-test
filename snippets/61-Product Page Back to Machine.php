$pidref = $modx->getOption('pidref', $_GET, 0);
if (intval($pidref) <= 0) {
    return;
}

$in = $modx->getObject('modResource', (int) $pidref);
if (!$in) {
    return;
}

$machine = $modx->runSnippet('pdoResources', [
    'parents' => '0',
    'resources' => $pidref,
    'where' => '{"template": "2"}',
    'includeTVs' => 'Primary Product Image, Primary Product Image Small',
    'tvPrefix' => '',
    'limit' => 1,
    'tpl' => 'Associated Back to Machine'
]);
    
return $machine;
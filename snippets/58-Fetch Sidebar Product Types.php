// Set parameters
if (isset($_GET['pidref']) && $_GET['pidref'] != '') {
    $relmc = $modx->getObject('modResource', (int) $_GET['pidref']);
    if($relmc) {
        $asmcs = $relmc->getTVValue('Associated Products on Sidebar');
    }
} else if (isset($pidref)) {
    $relmc = $modx->getObject('modResource', $pidref);
    if($relmc) {
        $asmcs = $relmc->getTVValue('Associated Products on Sidebar');
    }
} else {
    $asmcs = $modx->resource->getTVValue('Associated Products on Sidebar');    
}

if (empty($asmcs)) {
    return;
}

// Set up values for templating
if (isset($_GET['pidref']) && $_GET['pidref'] != '') {
    $modx->setPlaceholder('pidref', (int) $_GET['pidref']);
} else if(isset($pidref)) {
    $modx->setPlaceholder('pidref',$pidref);
} else {
    $nopidref = $modx->resource->get('id');
    $modx->setPlaceholder('pidref',$nopidref);
}
    
// Fetch all associated items in a comma seperated list
$ids = $modx->runSnippet('pdoResources', [
    'parents' => 11,
    'returnIds' => 1,
    'tvPrefix' => '',
    'limit' => 0,
    'tvFilters' => $asmcs
]);

// Run through all associated items finding their tags, then sort by Tagger rank (not menuindex)
$output = $modx->runSnippet('TaggerGetTags', [
    'resources' => $ids,
    'groups' => 'product-types',
    'rowTpl' => 'Associated Products Sidebar List',
    'sort' => '{"rank": "ASC"}'
]);
    
return $output;
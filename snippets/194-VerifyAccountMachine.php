if (isset($_POST['mc'])) {
    $mcs = $modx->runSnippet('pdoResources', [
        'parents' => 11,
        'limit' => 0,
        'includeTVs' => 'Associated Products on Sidebar',
        'tvPrefix' => '',
        'tvFilters' => 'Associated Products on Sidebar!==',
        'return' => 'ids'
    ]);
    $mcs = explode(",", $mcs);

    if (in_array($_POST['mc'], $mcs)) {
        $modx->runSnippet('addToUserValues', [
            'addTpl' => '',
            'removeTpl' => '',
            'key' => 'machines',
            'addKey' => 'machines',
            'anonymousAllowed' => true,
            'value' => $_POST['mc']
        ]);
    }
}
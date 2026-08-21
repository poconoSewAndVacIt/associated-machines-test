// Check if user is logged in and not on a machine page product

if (isset($_GET['pidref'])) {
    return;
}

$isLoggedIn = $modx->user->isAuthenticated('web');

if ($isLoggedIn) {
    $mcs = $modx->runSnippet('getUserValues', [
        'key' => 'machines' 
    ]);
} else {
    $mcs = isset($_COOKIE['machines']) && !empty($_COOKIE['machines']) ? $_COOKIE['machines'] : '';
}

if ($mcs) {
    $mcs = json_decode($mcs, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Unset the machines cookie if the JSON is invalid
        setcookie('machines', '', time() - 3600, '/');
        unset($_COOKIE['machines']);
        $mcs = [];
    }
} else {
    $mcs = [];
}

$list = $output = "";

if (empty($mcs)) {
    return $modx->getChunk('MyGuaranteedToFitWrap', array(
        'output' => "",
        'status' => "none"
    ));
}

// Bit of an inefficent way to check fit. TODO: make perform better 2/25/2018.
foreach ($mcs as $mc) {
    $mcpg = $modx->getObject('modResource', $mc);
    if (!$mcpg) {
        continue;
    }
    $modx->setPlaceholder('guaranteedToFitMachine', $mcpg->get('pagetitle'));
    $mcpgTv = $mcpg->getTVValue('Associated Products on Sidebar');
    
    // Don't want to run pdoresources on an empty tv filter set
    if (!empty($mcpgTv)) {
        $result = $modx->runSnippet('pdoResources', [
            'parents' => "11",
            'limit' => "50",
            'resources' => $modx->resource->get('id'),
            'includeTVs' => "Associated Machines",
            'tvFilters' => $mcpgTv,
            'tpl' => "@CODE: [[+guaranteedToFitMachine]]",
        ]);
            
        // Add to output list if a result is found
        if(!empty($result))
            $list .= $result . ', ';
    }
}
            
// Remove extra comma
$list = substr($list, 0, -2);
            
// Render list wrapper
if (!empty($list)) {
    $output = $modx->getChunk('MyGuaranteedToFitWrap', [
        'output' => $list,
        'status' => "success"
    ]);
} else {
    $output = $modx->getChunk('MyGuaranteedToFitWrap', [
        'output' => "",
        'status' => "alert"
    ]);
}
            
return $output;
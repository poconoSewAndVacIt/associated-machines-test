// Add the package
$pkg = $modx->addPackage('psveventlocations', MODX_CORE_PATH.'components/psveventlocations/model/');
if (!$pkg) {
    return "Failed to add package";
}

$output = "";

if(!isset($fetch)) {
    // Listbox multiple builder
    // Build the query for redirects first
    $locations = $modx->getCollection('psvEventLocations');

    $i = 1;
    foreach ($locations as $location) {
        $output .= $location->get('title') . "==" . $location->get('id');
        if($i !== $count)
            $output .= "||";
        $i++;
    }
    
} else if ($fetch === "locations") {
    // Frontend output
    $assignedLocation = $modx->resource->getTVValue('Event Location');

    // Database Queries
    $location = $modx->getObject('psvEventLocations', array(
        'id' => $assignedLocation,
        'deleted' => 0
    ));

    // Fall back for old style
    if (!$location) {
        return $modx->resource->getTVValue('Event Location');
    }
    
    $output = $location->get('address');
}

return $output;
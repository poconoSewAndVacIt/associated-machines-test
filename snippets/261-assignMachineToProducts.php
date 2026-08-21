$machine = trim($hook->getValue('machine'));
$resourceIds = array_map('trim', explode(',', $hook->getValue('resources')));

if (substr($machine, -1) !== ';') {
    $hook->addError('machine', 'Machine must have a semi-colon at the end of it!');
    return false;
}

$messages = [];
$addedCount = 0;
foreach ($resourceIds as $resourceId) {
    $resource = $modx->getObject('modResource', $resourceId);

    if (!$resource) {
        $messages[] = "Could not find resource with ID $resourceId.";
        continue;
    }

    $ascMcs = $resource->getTVValue('Associated Machines');

    // Check if already associated
    if (strpos($ascMcs, $machine) !== false) {
        $messages[] = "Skipped adding $machine on " . $resource->get('pagetitle') . ' (' . $resource->get('id') . '), already associated';
        continue;
    }
    
    // Check if product is assigned to groups instead of machines
    if (strpos($ascMcs, 'GROUP-') !== false && strpos($ascMcs, '->') === false) {
        $messages[] = "Skipped adding $machine on " . $resource->get('pagetitle') . ' (' . $resource->get('id') . '), product is associated only with groups';
        continue;
    }
    
    $resource->setTVValue('Associated Machines', $ascMcs .= " $machine");

    $messages[] = "Added $machine to " . $resource->get('pagetitle') . ' (' . $resource->get('id') . ')';
    $addedCount++;
}

$messages[] = "$machine added to $addedCount products";
$modx->setPlaceholder('amtp_messages', implode('<br>', $messages));

// Refresh cache
if ($addedCount > 0) {
    $modx->cacheManager->refresh();
}

return true;
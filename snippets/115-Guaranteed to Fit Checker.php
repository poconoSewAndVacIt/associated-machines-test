/**
 * POCONOSEWANDVAC.COM
 * Generates the list for part checker. Filters out groups.
 */

$return = $modx->getOption('return', $scriptProperties, '');
$id = $modx->getOption('id', $scriptProperties, 0);

// Support pulling in from external resources by passing in the modResource id into the id parameter
$resource = $id === 0 ? $modx->resource : $modx->getObject('modResource', $id);

if (!$resource) {
    return false;
}

// Return if no verify fit
$pSettings = $resource->getTVValue('Product Settings');
if (strpos($pSettings, 'verifyfit') === false) {
    return false;
}

// Get this products machines
$ascMcs = $resource->getTVValue('Associated Machines');
$ascMcs = array_map('trim', explode(';', $ascMcs));

// Gets all machines on the website
$allMcs = $modx->runSnippet('pdoResources', array(
    'return' => 'json',
    'select' => '{"modResource":"id,pagetitle,uri"}',
    'parents' => '11',
    'limit' => '0',
    'includeTVs' => 'Associated Products on Sidebar',
    'tvFilters' => 'Associated Products on Sidebar!='
));
$allMcs = json_decode($allMcs, true);

// Groups and machines are gotten separately as to not get group values duplicated.
$outputList = [];
$output = [];

// Groups
$groups = array_filter($ascMcs, function($value) {
    if (strncmp($value, "GROUP", 5) !== 0) {
        return false;
    }
    return true;
});

// Build the group filter
$groupIterator = new CachingIterator(new ArrayIterator($groups));
foreach ($groupIterator as $group) {
    if ($groupIterator->hasNext()) {
        $groupFilter .= 'Associated Products on Sidebar==%'.$group.'%||';
    } else {
        $groupFilter .= 'Associated Products on Sidebar==%'.$group.'%';
    }
}

// Add group machines to the output array
if ($groupFilter) {
    $groupMachines = $modx->runSnippet('pdoResources', array(
        'return'=>'json',
        'select'=>'{"modResource":"id,pagetitle,uri"}',
        'parents'=>'11',
        'limit'=>'0',
        'includeTVs'=>'Associated Products on Sidebar',
        'tvFilters'=>$groupFilter
    ));
    $groupMachines = json_decode($groupMachines, true);

    foreach ($groupMachines as $groupMachine) {
        $outputList[] = [
            'name' => $groupMachine['pagetitle'],
            'url' => $modx->makeUrl($groupMachine['id'])
        ];
    }
}

// Machines
$mcs = array_filter($ascMcs, function($value) {
    if (strncmp($value, "GROUP", 5) === 0
        || strncmp($value, "PSV", 3) === 0
    ) {
        return false;
    }
    return true;
});

foreach ($mcs as $key => $value) {
    if (empty($value)) {
        continue;
    }
    
    foreach ($allMcs as $v) {
        if (stripos($v["tv.Associated Products on Sidebar"], $value.";")) {
            $outputList[] = [
                'name' => $v['pagetitle'],
                'url' => $modx->makeUrl($v['id'])
            ];
            continue 2;
        }
    }

    // Filter out -> and prefix.
    $filtered = explode("->", $value);
    $outputList[] = [
        'name' => $filtered[1]
    ];
}

// Sort in alphabetical order
usort($outputList, function ($item1, $item2) {
    return $item1['name'] <=> $item2['name'];
});

if ($return === 'data') {
    return $outputList;
}

foreach ($outputList as $value) {
    if ($value['url']) {
        $output[] = '<li><a href="' . $value["url"] . '">' . $value["name"] . '</a></li>';
    } else {
        $output[] = '<li>' . $value["name"] . '</li>';
    }
}

$output = array_unique($output);

return $modx->getChunk('Guaranteed to Fit Checker Output',array(
    'machines' => !empty($output) ? implode('', $output) : 'No machines found'
));
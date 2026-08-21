$type = $modx->getOption('type', $_GET, '');
$brand = $modx->getOption('brand', $_GET, '');
$query = $modx->getOption('query', $_GET, '');
$limit = $modx->getOption('limit', $_GET, 10);
$offset = $modx->getOption('offset', $_GET, 0);

// Construct the where
$searchWhere = [];

$searchWords = explode(' ', $query);
foreach ($searchWords as $searchWord) {
    $searchWhere[] = [
        'pagetitle:LIKE' => '%' . $searchWord . '%',
        'OR:introtext:LIKE' => '%' . $searchWord . '%',
    ];
}

$searchWhere['isfolder'] = 0;
$search = json_encode($searchWhere);

$machines = $modx->runSnippet('pdoResources', [
    'parents' => 0,
    'tvPrefix'=>'',
    'includeTVs'=>'storeDepartment, Associated Products on Sidebar',
    'tvFilters'=> $type ? 'Associated Products on Sidebar!=,storeDepartment==' . $type : 'Associated Products on Sidebar!=',
    'select'=>'id, pagetitle, introtext, uri',
    'depth' => 1,
    'sortby'=>'{"menuindex":"ASC"}',
    'return' => 'json',
    'limit' => $limit,
    'offset' => $offset,
    'where'=>$modx->runSnippet('TaggerGetResourcesWhere', [
        'groups'=>'brands',
        'tags'=>$brand,
        'where'=>$search
    ])
]);

return $machines;
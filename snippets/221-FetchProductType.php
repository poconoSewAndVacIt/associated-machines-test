$brand = $modx->getOption("brand", $_GET, 'all');
$type = $modx->getOption("type", $_GET, '');
$machine = $modx->getOption("machine", $_GET, 0);
$limit = $modx->getOption("limit", $_GET, 0);
$offset = $modx->getOption("offset", $_GET, 0);
$output = [
    'machine' => null,
    'brand' => null,
    'product_type' => null,
    'products' => []    
];

if (!$type) {
    return false;
}

if ($brand === 'all') {
    $brand = '';
}

$modx->addPackage('tagger', $modx->getOption('tagger.core_path', null, $modx->getOption('core_path'). 'components/tagger/'). 'model/');

$props = [
    'parents'=>'11',
    'leftJoin'=>'{
        "ProductVariation":{
            "class":"ProductVariation",
            "on":"modResource.id = ProductVariation.resource_id"
        }
    }',
    'select'=>'{
        "modResource":"modResource.id AS id, modResource.pagetitle AS pagetitle, modResource.longtitle AS longtitle, modResource.introtext AS introtext, modResource.uri AS uri",
        "ProductVariation":"ProductVariation.sku AS sku, ProductVariation.regular_retail AS regular_retail, ProductVariation.regular_sale AS regular_sale, ProductVariation.stock AS stock, ProductVariation.nla, ProductVariation.covered"
    }',
    //'groupby' => 'id',
    'includeTVs'=>'Primary Product Image, Primary Product Image Small, Associated Machines, Tagger Hold, genuine',
    'tvPrefix'=>'',
    'limit'=>$limit,
    'offset' => $offset,
    'return' => 'json',
    'fastMode' => 1,
    'sortby'=>'{"menuindex":"ASC"}',
    'where'=>$modx->runSnippet('TaggerGetResourcesWhere', [
        'tags' => $brand ? $type . ',' . $brand : $type,
        'where' => '{"isfolder": 0}',
        'matchAll' => $brand ? 1 : 0,
    ])
];

// Fetch product type information
$productTypeQuery = $modx->newQuery('TaggerTag');
$productTypeQuery->where(['alias' => $type, 'group' => 2]); // product type group
$productType = $modx->getObject('TaggerTag', $productTypeQuery);
if ($productType) {
    $output['product_type'] = $productType->toArray();
}

// Fetch brand information
$brandQuery = $modx->newQuery('TaggerTag');
$brandQuery->where(['alias' => $brand, 'group' => 1]); // brand group
$brandType = $modx->getObject('TaggerTag', $brandQuery);
if ($brandType) {
    $output['brand'] = $brandType->toArray();
}

if ($machine) {
    $machineResource = $modx->getObject('modResource', $machine);
    
    if (!$machineResource) {
        return json_encode([
            'success' => false,
            'message' => 'Machine not found'
        ], true);
    }
    
    $props['tvFilters'] = $machineResource->getTVValue('Associated Products on Sidebar');
    $output['machine'] = [
        'id' => $machineResource->get('id'),
        'label' => $machineResource->get('pagetitle'),
        'uri' => $machineResource->get('uri'),
    ];
}

$results = json_decode($modx->runSnippet('pdoResources', $props), true);
$products = [];

// Render property values for API
$resultIds = [];
$unsetIds = [];
for ($i = 0; $i < count($results); $i++) {
    $resultId = $results[$i]['id'];
    
    // Check if multi-option product
    if (array_key_exists($resultId, $resultIds)) {
        $results[$resultIds[$resultId]]['add_to_cart_text'] = 'See Options'; 
        $unsetIds[] = $i;
        // We don't want to re-add the "same" product
        continue;
    }
    
    // Check for genuine part
    $results[$i]['is_genuine'] = (isset($results[$i]['genuine']) && $results[$i]['genuine'] !== 'GuaranteedToFit');
    unset($results[$i]['genuine']);

    // Keywords
    $results[$i]['keywords'] = $results[$i]['introtext'] . ' ' . $results[$i]['Tagger Hold'];
    unset($results[$i]['introtext']);
    unset($results[$i]['Tagger Hold']);

    // Images
    $results[$i]['image_original'] = $results[$i]['Primary Product Image'];

    $results[$i]['image_small'] = $modx->runSnippet('imgix', [
        'input' => $results[$i]['Primary Product Image Small'],
        'options' => 'w=280&h=280&fit=fill&bg=ffffff'
    ]) ?: null;
    unset($results[$i]['Primary Product Image Small']);
    
    $results[$i]['image'] = $modx->runSnippet('imgix', [
        'input' => $results[$i]['Primary Product Image'],
        'options' => 'w=280&h=280&fit=fill&bg=ffffff'
    ]) ?: null;
    unset($results[$i]['Primary Product Image']);
    
    // Associated machines
    unset($results[$i]['Associated Machines']);
    
    // Type casts
    $results[$i]['id'] = intval($results[$i]['id']);
    $results[$i]['stock'] = intval($results[$i]['stock']);
    
    // Remove pricing from NLA/covered products
    if ($results[$i]['nla'] == 1 || $results[$i]['covered'] == 1) {
        $results[$i]['regular_retail'] = null;
        $results[$i]['regular_sale'] = null;
    }
    
    $resultIds[$resultId] = $i;
}

$results = array_diff_key($results, array_flip($unsetIds));
$output['products'] = array_values($results);

return json_encode($output);
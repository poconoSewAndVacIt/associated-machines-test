$query = $modx->getOption('query', $_REQUEST, null);
// Resources to ignore in quick search
$disallowedResources = [2316, 2801, 2767, 2795, 2794, 2310, 2317, 2757, 2315, 2314, 2312, 2311, 2306, 2299, 2284, 1098, 2939];

if (!$query) {
    return;
}

$query = trim($query);

// Don't proceed if input doesn't look like a barcode
/*if (!$query || !preg_match("/^[0-9]{11,13}$/", $query)) {
    return;
}*/

$q = $modx->newQuery('ProductVariation');

$q->where([
    'ProductVariation.resource_id:NOT IN' => $disallowedResources,
    'ProductVariation.deleted:=' => false,
    'ProductVariation.published:=' => true,
]);

// Internal barcodes based off recnum
if (strtolower(substr($query, 0, 3)) === 'psv') {
    $q->where([
        'ProductVariation.psvdb_ids:=' => substr($query, 3),
    ]);
} else {
    $q->where([
        [
            'ProductVariation.barcode:=' => $query,    
        ],
        [
            'OR:ProductVariation.sku:=' => $query,
        ],
    ]);
}

/*$q->innerJoin('modResource', 'modResource', ['modResource.id = ProductVariation.resource_id']);
$q->where([
    'modResource.introtext:LIKE' => '%' . $query . '%'
]);*/

$productCount = $modx->getCount('ProductVariation', $q);

if ($productCount!==1) {
    return;
}

$product = $modx->getObject('ProductVariation', $q);

$resourceId = $product->get('resource_id');
$resource = $modx->getObject('modResource', [
    'id' => $resourceId,
    'deleted' => 0,
    'published' => 1,
]);
    
if ($resource) {
    $url = $modx->makeUrl($product->get('resource_id'));
    $modx->sendRedirect($url);
}
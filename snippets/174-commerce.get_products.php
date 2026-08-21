/**
 * @var modX $modx
 * @var array $scriptProperties
 */

// Instantiate the Commerce class
$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.get_product snippet.');
    return 'Could not load Commerce. Please try again later.';
}

// Determine which products to retrieve. Either "all" or specific product ids.
$allProducts = false;
$productIds = [];
$productsValue = (string)$modx->getOption('products', $scriptProperties, 0);
if (strtolower(trim($productsValue)) === 'all') {
    $allProducts = true;
    $sortby = (string)$modx->getOption('sortby', $scriptProperties, 'comProduct.id');
}
else {
    $productIds = strpos($productsValue, '||') !== false ? explode('||', $productsValue) : explode(',', $productsValue);
    $productIds = array_map('intval', array_map('trim', $productIds));
    $sortby = (string)$modx->getOption('sortby', $scriptProperties, 'FIELD(comProduct.id, ' . implode(',', $productIds) . ')');
}

// Some more options that can be provided to the snippet
$tpl = (string)$modx->getOption('tpl', $scriptProperties, '');
$wrapperTpl = (string)$modx->getOption('wrapperTpl', $scriptProperties, '');
$separator = (string)$modx->getOption('separator', $scriptProperties, '');
$sortdir = (string)$modx->getOption('sortdir', $scriptProperties, '');
$where = (string)$modx->getOption('where', $scriptProperties, '[]');
$logSql = (bool)$modx->getOption('logSql', $scriptProperties, false);

// Attempt to load the products
$c = $commerce->adapter->newQuery('comProduct');

if ($allProducts) {
    $c->where([
        'removed:=' => false
    ]);
}
else {
    $c->where([
        'id:IN' => $productIds,
        'AND:removed:=' => false
    ]);
}

if ($sortby === 'price') {
    $c->innerJoin('comProductPriceIndex', 'PriceIndex');
    $c->where([
        'PriceIndex.currency' => $commerce->currency->get('alpha_code'),
    ]);
    $c->sortby('PriceIndex.price', $sortdir);
}
else {
    $c->sortby($sortby, $sortdir);
}

$extraConditions = json_decode($where, true);
if (is_array($extraConditions)) {
    $c->andCondition($extraConditions);
}

// Support pagination with getPage/pdoPage
$total = $commerce->adapter->getCount('comProduct', $c);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');
$modx->setPlaceholder($totalVar, $total);

$limit = (int)$modx->getOption('limit', $scriptProperties, 0);
$offset = (int)$modx->getOption('offset', $scriptProperties, 0);
$c->limit($limit, $offset);

if ($logSql) {
    $c->prepare();
    $modx->log(modX::LOG_LEVEL_ERROR, '[commerce.get_products] Fetching products with query: ' . $c->toSQL() . ' - generated from properties ' . print_r($scriptProperties, true));
}

/** @var comProduct[] $products */
$products = $commerce->adapter->getCollection('comProduct', $c);
$i = 0;
$output = [];
foreach ($products as $product) {
    // Sync the product info
    $product->synchronise();

    // Grab the data
    $data = $product->toArray();
    $data['scriptProperties'] = $scriptProperties;

    // Add the formatted weight
    $weight = $product->getWeight();
    if ($weight instanceof \PhpUnitsOfMeasure\PhysicalQuantity\Mass) {
        $data['weight_formatted'] = (string)$weight;
    }

    $data['total_products'] = $total;
    $data['idx'] = $i;

    $i++;
    if ($tpl !== '') {
        $output[] = $commerce->getChunk($tpl, $data);
    } else {
        $output[] = '<pre>' . print_r($data, true) . '</pre>';
    }
}
$output = implode($separator, $output);

if ($wrapperTpl !== '') {
    return $commerce->getChunk($wrapperTpl, [
        'output' => $output,
        'total_products' => $total,
    ]);
}

return $output;
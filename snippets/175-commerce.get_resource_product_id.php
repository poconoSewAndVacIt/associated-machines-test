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
    return '0';
}

// A couple of options that can be provided to the snippet
$resourceId = (int)$modx->getOption('resource', $scriptProperties, $modx->resource->get('id'));
$classKey = (string)$modx->getOption('classKey', $scriptProperties, 'comResourceProduct');
$autoCreate = (bool)$modx->getOption('autoCreateIfNotExists', $scriptProperties, true);

// Make sure we're fetching a registered product instance
$descendants = $commerce->adapter->getDescendants('comProduct');
if (!in_array($classKey, $descendants, true)) {
    $classKey = 'comResourceProduct';
}

// Attempt to load the product
$product = $commerce->adapter->getObject($classKey, [
    'target' => $resourceId,
    'class_key' => $classKey,
    'removed' => false,
]);

// If we have the product, return its ID
if ($product instanceof comProduct) {
    return $product->get('id');
}

// If we don't have the product, but we're allowed to auto create it, create it.
if ($autoCreate) {
    $product = $commerce->adapter->newObject($classKey);
    $product->fromArray([
        'target' => $resourceId
    ]);
    $product->save();
    $product->synchronise();
    return $product->get('id');
}

$modx->log(modX::LOG_LEVEL_ERROR, '[Commerce.get_resource_product_id] Unable to fetch or create resource product id for resource ' . $resourceId);
return 0;
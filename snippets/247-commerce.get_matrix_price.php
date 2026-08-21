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

$matrixId = (int)$modx->getOption('matrix', $scriptProperties, 0);
$object = $modx->getObject('comProductMatrix', [
    'id' => $matrixId,
    'removed' => false,
]);
if (!($object instanceof comProductMatrix)) {
    return '';
}

$getMin = $modx->getOption('getMin', $scriptProperties, true);
$getMax = $modx->getOption('getMax', $scriptProperties, false);
$inStockOnly = $modx->getOption('inStockOnly', $scriptProperties, false);
$separator = (string)$modx->getOption('separator', $scriptProperties, '&ndash;');
$noPrice = $modx->getOption('noPriceAvailable', $scriptProperties, $commerce->adapter->lexicon('commerce.out_of_stock'));

$c = $commerce->adapter->newQuery('comProductMatrixProduct');
$c->innerJoin('comProductMatrixRow', 'Row');
$c->innerJoin('comProductMatrixColumn', 'Column');
$c->innerJoin('comProductPriceIndex', 'PriceIndex');
$c->where([
    'PriceIndex.currency' => $commerce->currency->get('alpha_code'),
]);

$desc = array_merge(['comProductMatrixProduct'], $commerce->adapter->getDescendants('comProductMatrixProduct'));
$c->where([
    'removed' => false,
    'class_key:IN' => $desc,
    'matrix' => $matrixId,
    'Row.active' => true,
    'Column.active' => true,
]);

if ($inStockOnly) {
    $c->where([
        'stock:>' => 0,
    ]);
}

if ($getMin) {
    $c->select([
        'MIN(PriceIndex.price) as `min_price`',
    ]);
}
if ($getMax) {
    $c->select([
        'MAX(PriceIndex.price) as `max_price`',
    ]);
}
$c->prepare();

if ($stmt = $commerce->adapter->query($c->toSQL())) {
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $output = [];
    if ($getMin && $data['min_price'] !== null) {
        $output[] = $commerce->formatValue($data['min_price'], 'financial');
    }
    if ($getMax && $data['max_price'] !== null) {
        $output[] = $commerce->formatValue($data['max_price'], 'financial');
    }

    // Are both values the same? Then only show the single value.
    $output = array_unique($output);

    // Return the no price string if there were no prices found.
    if (count($output) === 0) {
        return $noPrice;
    }

    return implode($separator, $output);
}
return $noPrice;
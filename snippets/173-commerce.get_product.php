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

// A couple of options that can be provided to the snippet
$productIds = (string)$modx->getOption('product', $scriptProperties, 0);
$productIds = strpos($productIds, '||') !== false ? explode('||', $productIds) : explode(',', $productIds);
$productIds = array_map('intval', array_map('trim', $productIds));
$productIds = array_filter($productIds);
if (count($productIds) === 0) {
    $where = $modx->resource instanceof modResource ? 'on resource ' . $modx->resource->get('id') : '';
    $modx->log(modX::LOG_LEVEL_WARN, '[Commerce.get_product] Unable to fetch product information ' . $where . '; &product is empty or does not contain valid ID(s).');
    return '';
}

$toJson = (bool)$modx->getOption('toJson', $scriptProperties, false);
$jsonFields = (string)$modx->getOption('jsonFields', $scriptProperties, '');
$toPlaceholders = (string)$modx->getOption('toPlaceholders', $scriptProperties, '');
$tpl = (string)$modx->getOption('tpl', $scriptProperties, '');
$field = (string)$modx->getOption('field', $scriptProperties, '');
$sortby = (string)$modx->getOption('sortby', $scriptProperties, 'FIELD(comProduct.id, ' . implode(',', $productIds) . ')');
$sortdir = (string)$modx->getOption('sortdir', $scriptProperties, '');

$c = $commerce->adapter->newQuery('comProduct');
$c->where([
    'removed' => false,
]);
if (count($productIds) > 1) {
    $c->where([
        'id:IN' => $productIds,
    ]);
}
else {
    $c->where([
        'id' => reset($productIds)
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
$c->limit(1);

// Attempt to load the product
$product = $commerce->adapter->getObject('comProduct', $c);

if ($product instanceof comProduct) {
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

    $data['pricing'] = $product->getPricing($commerce->currency);

    if ($toPlaceholders !== '') {
        $modx->toPlaceholders($data, rtrim($toPlaceholders, '.'));
        return '';
    }
    if ($toJson) {
        // Filter output fields
        $jsonData = $data;
        $jsonFields = $specifiedFields = !empty($jsonFields) ? explode(',', $jsonFields) : [];
        if (!empty($jsonFields)) {
            $jsonData = [];

            foreach ($jsonFields as $fld) {
                $parts = explode('.', $fld);
                $data[$parts[0]] = $data[$parts[0]] ?? '';
                if (is_array($data[$parts[0]]) && isset($parts[1])) {
                    $jsonData[$parts[0]] = $jsonData[$parts[0]] ?? [];
                    $jsonData[$parts[0]][$parts[1]] = $data[$parts[0]][$parts[1]] ?? '';
                }
                else {
                    $jsonData[$parts[0]] = $data[$parts[0]];
                }
            }
        }

        // Handle encoding a Pricing object
        if (!empty($jsonData['pricing']) && $jsonData['pricing'] instanceof \modmore\Commerce\Pricing\Pricing) {
            $jsonData['pricing'] = $jsonData['pricing']->serialize();
        }

        // If a tpl is also specified, parse and add it to the JSON output
        if ($tpl !== '') {
            $jsonData['tpl'] = $commerce->getChunk($tpl, $data);
        }

        return json_encode($jsonData);
    }
    if ($tpl !== '') {
        return $commerce->getChunk($tpl, $data);
    }
    if ($field !== '') {
        if (array_key_exists($field, $data)) {
            return $data[$field];
        }
        $parts = explode('.', $field);
        if (array_key_exists($parts[0], $data) && isset($parts[1])) {
            return $data[$parts[0]][$parts[1]] ?? '';
        }
    }

    return '<pre>' . print_r($data, true) . '</pre>';
}

$where = $modx->resource instanceof modResource ? ' on resource ' . $modx->resource->get('id') : '';
$modx->log(modX::LOG_LEVEL_ERROR, '[Commerce.get_product] Unable to fetch product information for ' . implode(',', $productIds) . $where);
return '';
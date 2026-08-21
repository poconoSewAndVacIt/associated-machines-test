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
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.render_quantity_price snippet.');
    return 'Could not load Commerce. Please try again later.';
}

// A couple of options that can be provided to the snippet
$productIds = (string)$modx->getOption('product', $scriptProperties, 0);
$productIds = strpos($productIds, '||') !== false ? explode('||', $productIds) : explode(',', $productIds);
$productIds = array_map('intval', array_map('trim', $productIds));

$c = $commerce->adapter->newQuery('comProduct');
$c->where([
    'removed' => false,
]);
if (count($productIds) > 1) {
    $c->where([
        'id:IN' => $productIds,
    ]);
    $c->sortby('FIELD(comProduct.id, ' . implode(',', $productIds) . ')');
}
else {
    $c->where([
        'id' => reset($productIds)
    ]);
}
$c->limit(1);

// Attempt to load the product
$product = $commerce->adapter->getObject('comProduct', $c);

if ($product instanceof comProduct) {
    $phs = [
        'product' => $product->toArray(),
    ];

    $pricing = $product->getPricing($commerce->currency);
    $regularPrice = $pricing->getRegularPrice()->getInteger();
    foreach ($pricing->getPriceTypes() as $type) {
        if ($type instanceof \modmore\Commerce\Pricing\PriceType\Quantity) {
            $prices = $type->getPrices();
            foreach ($prices as $price) {
                $price['price'] = $price['amount'];
                $price['price_formatted'] = $commerce->currency->format($price['amount']);
                $price['discount'] = $regularPrice - $price['amount'];
                $price['discount_formatted'] = $commerce->currency->format($price['discount']);
                $price['discount_percentage'] = number_format($price['discount'] / $regularPrice * 100);
                $phs['prices'][] = $price;
            }
        }
    }

    $tpl = $modx->getOption('tpl', $scriptProperties, 'frontend/pricetypes/quantity.twig');

    try {
        return $commerce->view()->render($tpl, $phs);
    }
    catch (\modmore\Commerce\Exceptions\ViewException $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[Commerce] Exception parsing ' . $tpl . ': ' . $e->getMessage());
        return $e->getMessage();
    }
}

$modx->log(modX::LOG_LEVEL_ERROR, '[Commerce.render_quantity_price] Unable to fetch product information for ' . implode(',', $productIds));
return '';
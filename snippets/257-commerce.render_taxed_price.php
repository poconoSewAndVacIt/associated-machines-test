/**
 * @var modX $modx
 * @var array $scriptProperties
 */

// Instantiate the Commerce class
use modmore\Commerce\Pricing\Price;
use modmore\Commerce\Pricing\Renderer\TwigRenderer;

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
    /** @var comSessionCartOrderItem $item */
    $item = $commerce->adapter->newObject('comSessionCartOrderItem');
    $order = comOrder::loadUserOrder($commerce);
    $item->setOrder($order);

    $currency = $item->getCurrency();

    $item->set('product', $product->get('id'));
    $item->set('quantity', 1);
    $item->fromProduct($product);

    // The price is the total as calculated by the comOrderItem class, including taxes
    $currentPrice = new Price($currency, $item->get('total'));

    // We fetch the pricing again here, to calculate the previous price
    $pricing = $product->getPricing($commerce->currency);
    $price = $pricing->getPriceForItem($item);
    if ($price->hasPreviousPrice()) {
        $prev = $price->getPreviousPrice();
        $item->set('price', $prev->getInteger());
        $currentPrice->setPreviousPrice(new Price($currency, $item->get('total')));
    }

    return (new TwigRenderer($commerce->view()))->format($currentPrice);
}

return '';
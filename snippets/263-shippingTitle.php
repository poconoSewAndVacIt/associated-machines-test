$noShipMsg = " - 1000's of Parts ";
$shipMsg = ' - FREE Shipping over $49.99 ';

$products = $modx->getCollection('ProductVariation', [
    'nla' => 0,
    'deleted' => 0,
    'published' => 1,
    'resource_id' => $modx->resource->get('id'),
]);

if (!$products) {
    return $noShipMsg;
}

foreach ($products as $product) {
    if ($product->getPrice(false) && $product->isFreeShip()) {
        return $shipMsg;
    }
}

return $noShipMsg;
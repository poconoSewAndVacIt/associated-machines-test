$id = $modx->getOption('id', $scriptProperties);
$sku = $modx->getOption('sku', $scriptProperties);

if (empty($id) || empty($sku)) {
    return;   
}

// Get the product with the requested params
$product = $modx->getObject('ProductVariation', [
    'resource_id' => $id,
    'sku' => $sku
]);

if ($product && $product->getRegularSale(false) > 0) {
    return '<p class="prodpricingretail nomargin strike">$'.$product->getRegularRetail().'</p>';
}
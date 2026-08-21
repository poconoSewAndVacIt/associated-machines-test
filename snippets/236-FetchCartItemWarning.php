$productId = (int) $modx->getOption('product', $scriptProperties, 0);
if ($productId === 0) {
    return;
}

$product = $modx->getObject('ProductVariation', $productId);
if (!$product) {
    return;
}

if ($product->isSpecialOrder()) {
    return 'Special order item. May take 5-7 days to ship from warehouse. See <a href="policies" class="underline">store policies</a> for details.';
}
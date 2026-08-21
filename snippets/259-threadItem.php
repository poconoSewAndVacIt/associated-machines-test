$color = $modx->getOption('color', $scriptProperties, '');
$image = $modx->getOption('image', $scriptProperties, '');
// Product variation ID
$product = $modx->getOption('product', $scriptProperties, 0);
$tpl = $modx->getOption('tpl', $scriptProperties, 'threadItem');

return $modx->getChunk($tpl, [
    'color' => $color,
    'image' => $image,
    'product' => $product,
]);
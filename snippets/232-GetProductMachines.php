$brand = $modx->getOption('brand', $scriptProperties, htmlspecialchars($_GET['filter']));
$productType = $modx->getOption('productType', $scriptProperties, null);
$department = $modx->getOption('department', $scriptProperties, null);
$productTpl = $modx->getOption('productTpl', $scriptProperties, 'Machine Listings Output');
$wrapTpl = $modx->getOption('wrapTpl', $scriptProperties, 'MachineListingsWrap');

if (!$productType || !$department) {
    return 'An error occured displaying products.';
}

$output = $modx->runSnippet('pdoResources', [
    'parents' => 11,
    'limit' => 0,
    'includeTVs' => 'Tagger Hold, Primary Product Image, Primary Product Image Small, ProductImageOverlays, listing_info, Product Settings',
    'processTVs' => 'ProductImageOverlays',
    'tvPrefix' => '',
    'tpl' => $productTpl,
    'tplWrapper' => $wrapTpl,
    'sortby' => '{"Product Settings": "ASC", "menuindex":"ASC"}',
    'where' => $modx->runSnippet('TaggerGetResourcesWhere', [
        'tags' => $productType,
        'where' => '{"isfolder": 0}'
    ])
]);

return $output;
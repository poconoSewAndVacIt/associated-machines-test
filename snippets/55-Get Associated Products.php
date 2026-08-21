if (!isset($_GET['associated']) || $_GET['associated'] === '') {
    return;
}

$ptypereq = $_GET['associated'];
$asmcs = $modx->resource->getTVValue('Associated Products on Sidebar');

// Don't allow loading product associations on products that have no associations
if (empty(trim($asmcs))) {
    $modx->sendErrorPage();
}

$ptypeout = $modx->runSnippet('pdoResources', array(
    'parents' => '11',
    'includeTVs' => 'Product Variations,Primary Product Image,Primary Product Image Small,ProductImageOverlays,genuine,listing_info',
    'processTVs' => 'Product Variations,ProductImageOverlays',
    'tvPrefix' => '',
    'limit' => 0,
    'sortby' => '{"createdon":"DESC"}',
    'tpl' => 'Associated Parts Output',
    'tplFirst' => 'Associated Parts Output First',
    'tplLast' => 'Associated Parts Output Last',
    'tvFilters' => $asmcs,
    'where' => $modx->runSnippet('TaggerGetResourcesWhere', array(
        'groups' => 'product-types',
        'tags' => "$ptypereq",
        'where' => "{'isfolder': 0}"
    ))  
));
    
// 404 if no products were found (no products in requested category or incorrect category entered in URL)
if (empty($ptypeout)) {
    $modx->sendErrorPage();
}
    
return $ptypeout;
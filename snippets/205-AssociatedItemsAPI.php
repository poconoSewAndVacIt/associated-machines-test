$resource = $modx->getObject('modResource', $_GET["resource"]);

// Default error message
$associated = array(
    'success' => false,
    'output' => 'Invalid input.'
);

// Test if successfully submitted id and associated product type.
if (isset($_GET["resource"]) && isset($_GET["associated"]) && !is_null($resource)) {
    $title = $resource->get('pagetitle');
    $asmcs = $resource->getTVValue('Associated Products on Sidebar');
    if (empty($asmcs)) {
        return json_encode($associated, true);
    }
    $tag = $modx->runSnippet('Product Page Association Title', array('associated' => $_GET["associated"]));
    $modx->setPlaceholder('associatedPageTypeId', $_GET["resource"]);
    $modx->runSnippet('Product Page Association Link Alias');

    $associated = array(
        'success' => true,
        'title' => $title . ' ' . $tag,
        'tag' => $tag,
        'output' => $modx->runSnippet('pdoResources', array(
            'parents' => '11',
            'includeTVs' => 'Product Variations,Primary Product Image,Primary Product Image Small,ProductImageOverlays,listing_info',
            'processTVs' => 'Product Variations,ProductImageOverlays',
            'includeContent' => 1,
            'tvPrefix' => '',
            'limit' => 0,
            'sortby' => '{"menuindex":"ASC"}',
            'tpl' => 'Associated Parts Output',
            'tvFilters' => $asmcs,
            'where' => $modx->runSnippet('TaggerGetResourcesWhere', array(
                'groups' => 'product-types',
                'tags' => $_GET["associated"],
                'where' => "{'isfolder': 0}"
            ))  
        ))
    );
}

return json_encode($associated, true);
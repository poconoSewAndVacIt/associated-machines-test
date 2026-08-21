// Get relevant information to fetch product type pages
if($resource) {
    $inputResource = $modx->getObject('modResource', $resource);
    $ptypes = $inputResource->getTVValue('Brand Page Product Types');
} else {
    $ptypes = $modx->resource->getTVValue('Brand Page Product Types');  
}

$filter = $modx->getOption('filter', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, 'BrandPageProductTypes');
$placeholder = $modx->getOption('placeholder', $scriptProperties, '');

// Set placeholder filter for use in snippet
$modx->setPlaceholder('filter', $filter);

$output = "";
// Fetch all product types assigned. Don't allow ptypes to be empty.
if(!empty($ptypes)) {
    $output = $modx->runSnippet('pdoResources', [
        'resources' => $ptypes,
        'tvPrefix' => '',
        'includeTVs' => 'Primary Product Image, Brand Page Filter',
        'parents' => '11',
        'limit' => '0',
        'sortby' => 'menuindex',
        'sortdir' => 'ASC',
        'tpl' => $tpl
    ]);
}

if (empty($placeholder)) return $output;
$modx->setPlaceholder($placeholder, $output);
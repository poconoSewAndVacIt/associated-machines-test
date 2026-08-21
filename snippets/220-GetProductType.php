$brand = $modx->getOption('brand', $scriptProperties, htmlspecialchars($_GET['filter']));
$productType = $modx->getOption('productType', $scriptProperties, null);
$department = $modx->getOption('department', $scriptProperties, null);
$ignoreFilter = $modx->getOption('ignoreFilter', $scriptProperties, 0);
$wrapTpl = $modx->getOption('wrapTpl', $scriptProperties, 'FetchProductTypeWrap');
$brandTpl = $modx->getOption('brandTpl', $scriptProperties, 'FPTDefaultBrands');
$sidebarTpl = $modx->getOption("sidebarTpl", $scriptProperties, 'FetchProductTypeSidebar');
$productTpl = $modx->getOption('productTpl', $scriptProperties, 'FetchProductTypeProduct');

if (!$productType || !$department) {
    return 'An error occured displaying products.';
}

// Add needed css and js.
$modx->regClientHTMLBlock('
    <script defer src="/assets/js/filter.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js"></script>
    <script defer src="/assets/js/pfilter.js"></script>
');

$modx->setPlaceholder('productTypeSidebar', $modx->getChunk($sidebarTpl, [
    'brand' => $brand,
    'department' => $department,
    'brandTpl' => $brandTpl
]));

return $modx->getChunk($wrapTpl, [
    'brand' => $brand,
    'productType' => $productType,
    'department' => $department,
    'ignoreFilter' => $ignoreFilter,
    'productTpl' => $modx->getChunk($productTpl)
]);
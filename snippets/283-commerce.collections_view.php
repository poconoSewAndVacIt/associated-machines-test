/**
 * This snippet can be used as a "Snippet Renderer" in a Collections view.
 * Set the system setting "commerce.collections_view_tv_name" to the name of your products TV.
 *
 * See full instructions here: [https://docs.modmore.com/en/Commerce/v1/Snippets/collections_view.html]
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

// Instantiate the Commerce class
$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.collections_view snippet.');
    return 'Could not load Commerce. Please try again later.';
}

// Determine the row, and get the child resource
$row = $modx->getOption('row', $scriptProperties, '');
/** @var modResource $resource */
$resource = $modx->getObject(modResource::class, ['id' => $row['id']]);
if (!$resource) {
    return '';
}

// Get any product ids in the TV
$tvName = $commerce->adapter->getOption('commerce.collections_view_tv_name', [], 'products');
$productIds = $resource->getTVValue($tvName);
if (!$productIds) {
    return '';
}
$productIds = explode(',', $productIds);

// Get the first published product
$c = $commerce->adapter->newQuery('comProduct');
$c->where([
    'removed' => false,
]);
if (count($productIds) > 1) {
    $c->where([
        'id:IN' => $productIds,
    ]);
    $c->sortby('FIELD(comProduct.id, ' . implode(',', $productIds) . ')');
} else {
    $c->where([
        'id' => reset($productIds)
    ]);
}
$c->limit(1);
$product = $commerce->adapter->getObject(comProduct::class, $c);

$output = '';
if ($product instanceof comProduct) {
    $product->synchronise();
    $productArray = $product->toArray();

    // Sanitize all input values
    foreach ($productArray as $k => $v) {
        $productArray[$k] = filter_var($v, FILTER_SANITIZE_STRING);
    }

    // Vary the output depending on the column name
    $column = $modx->getOption('column', $scriptProperties, '');
    if ($column) {
        switch ($column) {
            case 'name':
            case 'product_name':
                $output .= $productArray['name'];
                break;
            case 'description':
            case 'product_description':
                $output .= $productArray['description'];
                break;

            case 'image':
                $output .= $productArray['image'];
                break;

            case 'price':
            case 'price_rendered':
                $output .= $productArray['price_rendered'];
                break;
            case 'regular_price_formatted':
                $output .= $productArray['regular_price_formatted'];
                break;

            case 'sku':
                $output .= $productArray['sku'];
                break;

            case 'stock':
                $output .= $productArray['stock'];
                break;
            case 'stock_infinite':
            case 'stock_unlimited':
                $output .= $productArray['stock_infinite']
                    ? $commerce->adapter->lexicon('yes')
                    : $commerce->adapter->lexicon('no');
                break;

            case 'weight':
                $output .= $productArray['weight'];
                break;
            case 'weight_unit':
                $output .= $productArray['weight_unit'];
                break;
            case 'weight_with_unit':
                $output .= $productArray['weight'] . $productArray['weight_unit'];
                break;

            /*
             * Using anything not in the list above for the column name will return all the product fields as json.
             * A custom JavaScript renderer is needed to process it on the browser side. See docs for details.
             */
            default:
                $output .= json_encode($productArray);
                break;
        }
    }
}
return $output;
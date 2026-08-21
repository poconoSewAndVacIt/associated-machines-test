$query = $modx->getOption('query', $scriptProperties, null);
$resource_id = $modx->resource->get('id');

$q = $modx->newQuery('ProductVariation');
$q->where([
    'deleted'       => false,
    'nla'           => false,
    'published'     => true,
    'resource_id'   => $resource_id
]);

if ($query) {
    $q->where(['name:LIKE' => '%' . $query . '%']);
}

$products = $modx->getCollection('ProductVariation', $q);

$html = "";

foreach ($products as $product) {
    $id = (int) $product->get('id');
    $name = htmlspecialchars($product->get('name'));
    $sku = htmlspecialchars($product->get('sku'));
    $img = htmlspecialchars($product->get('image'));
    $color = htmlspecialchars($product->get('colorcode'));
    
    $img_html = "<img data-product-id='$id' src='$img' />";
    
    if(!is_null($color) && $color!=="") {
        $img_html = 
        "<ul style='list-style:none; display:inline-block; font-size:12px; text-align:left; width:100px; max-width:100px'>
                <li data-product-id='$id'>$sku</li>
                <li data-product-id='$id'>$name</li>
            </ul>".
        "<div id='$id' style='background:$color; border:3px solid #000; width:36px; height:36px; margin:10px 10px 0px 10px; display:inline-block' data-product-id='$id'></div>";
    }
    
    $html .= "<div class='column column-block thread' data-product-id='$id' data-name='$name' data-sku='$sku' data-color='$color'>".
            "<button class='quickview-modal-open modal-open addtocart' data-product-id='$id'>".
                $img_html.
            "</button>".
        "</div>";
}

return $html;
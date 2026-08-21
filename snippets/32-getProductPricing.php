/**
* POCONOSEWANDVAC.COM
* Structures the pricing.
*/

$resourceId = $modx->getOption('resourceId', $scriptProperties, $modx->resource->get('id'));
$placeholder = $modx->getOption('placeholder', $scriptProperties, 'product');
$type = $modx->getOption('type', $scriptProperties, 'product');
$displayNeedHelp = (bool) $modx->getOption('displayNeedHelp', $scriptProperties, true);

// Fetch the product page
if ($resourceId !== $id) {
    $productPage = $modx->getObject('modResource', $resourceId);
} else {
    $productPage = $modx->resource;
}

// Get the variations
$variations = [];
$vQuery = $modx->newQuery('ProductVariation');
// sort by pricing for display
$vQuery->sortby('CAST(regular_retail as SIGNED)', 'ASC');
$vQuery->sortby('CAST(regular_sale as SIGNED)', 'ASC');
$vQuery->where([
    'resource_id' => $resourceId,
    'nla' => 0,
    'deleted' => 0,
    'published' => 1,
]);
$pvariations = $modx->getCollection('ProductVariation', $vQuery);

foreach ((array) $pvariations as $v) {
    if ($v->getPrice(false) !== '') {
        $variations[] = $v;
    }
}

// If no variations found, no need to run rest of snippet
if (empty($variations)) return;

$variationsCount = 0;
$orderItem = [];
foreach ($variations as $v) {
    // Ignore covered products for buttons
    if ($v->get('covered')) {
        continue;
    }
    
    $values = [
        'Regular Retail' => $v->getRegularRetail(),
        'Regular Sale' => $v->getRegularSale(),
        'has_limited_sale' => (int) $v->hasLimitedSale(),
        'limited_sale' => $v->getLimitedSale(),
        'Stock' => $v->getStock(),
        'Price' => $v->getPrice(),
        'Part Number' => $v->getSku(),
        'Variation Name' => $v->getName(),
        'Variation Description' => $v->getDescription(),
        'special_order' => (int) $v->isSpecialOrder(),
        'back_order' => (int) $v->isBackOrder(),
        'id' => $v->get('id'),
        'condition' => $v->get('condition'),
        'store_only' => $v->get('store_only'),
    ];
    
    if ($variationsCount === 0) {
        $output .= $modx->getChunk('Product Variation Output', $values);
        $outputList .= $modx->getChunk('Product Variation Output List First', $values); 
    } else {
        $output .= $modx->getChunk('Product Variation Output', $values);
        $outputList .= $modx->getChunk('Product Variation Output List', $values);
    }
    
    $productOrderItem = $modx->getObject('ProductVariationOrderItem', ['product_id' => $v->get('id'), 'published' => 1]);
    if ($productOrderItem) {
        $orderItem[$v->get('id')] = $productOrderItem->toArray();
    }
    
    $variationsCount++;
}

// Display multi variation dropdown
if ($variationsCount > 1) {
    $outvarList = '<select class="productvariations">'.$outputList.'</select>';
}
$cartBtns = '<div class="cartbtns">'.$output.'</div>';

$stockFormProduct = 0;

// Render first product chunks for better SEO
if ($variations[0]->getPrice(false) !== '' && $variations[0]->get('covered') == 0) {
    $quantity = $modx->getChunk('Pricing Structure Add to Cart', [
        'condition' => $variations[0]->get('condition'),
        // conditional rendering for quantity field
        'hide' => (int) ($variations[0]->get('store_only') || $variations[0]->get('nla') || $variations[0]->get('covered') || $variations[0]->get('back_order')),
    ]);
    
    if ($variations[0]->get('stock') == 0) {
        $stockFormProduct = $variations[0]->get('id');
    }
            
    $pricing = $modx->getChunk('productInfoPrice', [
        'Regular_Retail' => $variations[0]->getRegularRetail(),
        'Regular_Sale' => $variations[0]->getRegularSale(),
        'has_limited_sale' => (int) $variations[0]->hasLimitedSale(),
        'limited_sale' => $variations[0]->getLimitedSale(),
        'savings' => $variations[0]->getSavings(),
        'savingsPercent' => $variations[0]->getSavingsPercent(),
        'condition' => $variations[0]->get('condition'),
        'covered' => $variations[0]->get('covered'),
        'store_only' => $variations[0]->get('store_only'),
        'display_need_help' => (int) $displayNeedHelp,
    ]);
}
        
// Special order or Drop ship message
if ($variations[0]->get('store_only')) {
    $shipping .= $modx->getChunk('productInfoStoreOnly');
} else if ($variations[0]->get('covered') == 1) {
    // No message for covered products non-store only products
    $shipping .= '';   
} else if ($variations[0]->isSpecialOrder()) {
    $shipping .= $modx->getChunk('productInfoSpecialOrder');
} else if ($variations[0]->get('dropship')) {
    $shipping .= $modx->getChunk('productInfoDropShip');
} else {
    $shipping .= $modx->getChunk('Pricing Structure Free Shipping', [
        'price' => $variations[0]->getPrice(false),
        'is_free_ship' => (int) $variations[0]->isFreeShip(),
    ]);
}

// Paypal credit, only valid on items $99 or up
if ($variations[0]->getPrice(false) > 99 && $variations[0]->get('store_only') == 0) {
    $shipping .= $modx->getChunk('productInfoPaypalCredit', [
        'price' => $variations[0]->getPrice(false),    
    ]);
}
    
// Wrap up the shipping messages if it isn't empty
if ($shipping) {
    $shipping = $modx->getChunk('productInfoShipping', ['output' => $shipping]);
}

$modx->toPlaceholders([
    'available' => '1', // cannot be not available at this point
    'sku' => $variations[0]->getSku(),
    'pricing' => $pricing,
    'is_limited_sale' => $variations[0]->is_limited_sale,
    'variations' => $cartBtns,
    'variations_count' => $variationsCount,
    'variations_listing' => $outvarList,
    'shipping' => $shipping,
    'quantity' => $quantity,
    'notify_form_product' => $stockFormProduct,
    'display_contact_info' => 0, //(int) (($variations[0]->getPrice(false) >= 150 || $variations[0]->getPrice(false) == '') && $variations[0]->get('covered') == 1),
    'order_item' => json_encode($orderItem),
], $placeholder);
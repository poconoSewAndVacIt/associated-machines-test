$object =& $modx->getOption('object', $scriptProperties, null);//reference to the saved object
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);//the processors scriptProperties
$postvalues = $modx->getOption('postvalues', $scriptProperties, []);//the posted values

// Dimensions
$dimensionUnit = $modx->getOption('Dimension_dimension_unit', $postvalues, 'in');
$length = $modx->getOption('Dimension_length', $postvalues, 0);
$width = $modx->getOption('Dimension_width', $postvalues, 0);
$height = $modx->getOption('Dimension_height', $postvalues, 0);

// Weight
$weightUnit = $modx->getOption('Dimension_weight_unit', $postvalues, $modx->getOption('commerce.default_weight_unit'));
$weight = $modx->getOption('Dimension_weight', $postvalues, 0);

// Order item fields
$orderItemType = $modx->getOption('OrderItem_type', $postvalues, '');
$orderItemTitle = $modx->getOption('OrderItem_title', $postvalues, '');
$orderItemDesc = $modx->getOption('OrderItem_description', $postvalues, '');
$orderItemPh = $modx->getOption('OrderItem_placeholder', $postvalues, '');
$orderItemOptions = $modx->getOption('OrderItem_options', $postvalues, '');
$orderItemReq = $modx->getOption('OrderItem_required', $postvalues, 0);
$orderItemPub = $modx->getOption('OrderItem_published', $postvalues, 0);

$result = []; 
if (!$object) {
    return $result;
}

$objectId = $object->get('id');

// do/whiles to break out without returning from this snippet
do {    
    // Quit early if no l/w/h/w set.
    if ($length == 0 && $width == 0 && $height == 0 && $weight == 0) {
        break;
    }
        
    $dimension = $modx->getObject('ProductVariationDimension', $object->get('dimension'));
    
    if (!$dimension) {
        $dimension = $modx->newObject('ProductVariationDimension');
    }
        
    $dimension->fromArray([
        'dimension_unit' => $dimensionUnit,
        'length' => $length,
        'width' => $width,
        'height' => $height,
        'weight_unit' => $weightUnit,
        'weight' => $weight
    ]);
        
    $dimension->save();
    
    $object->set('dimension', $dimension->get('id'));
} while(false);

do {
    if (empty($orderItemTitle)) {
        break;
    }
    
    $orderItem = $modx->getObject('ProductVariationOrderItem', ['product_id' => $objectId]);
    if (!$orderItem) {
        $orderItem = $modx->newObject('ProductVariationOrderItem');
    }
    
    $orderItem->fromArray([
        'product_id' => $objectId,
        'type' => $orderItemType,
        'title' => $orderItemTitle,
        'description' => $orderItemDesc,
        'placeholder' => $orderItemPh,
        'options' => $orderItemOptions,
        'required' => $orderItemReq,
        'published' => $orderItemPub,
    ]);
    
    $orderItem->save();
} while (false);

$object->save();

return $modx->toJson($result);
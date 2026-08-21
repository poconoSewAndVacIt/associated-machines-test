$properties = $modx->getOption('scriptProperties', $scriptProperties, []);//the processors scriptProperties
$data = $modx->fromJson($properties['data']);
$objectId = (int) $properties['object_id'];
$object = $modx->getObject('ProductVariation', $objectId);

$regularRetail = $modx->getOption('regular_retail', $data, $object ? $object->get('regular_retail') : '');
$regularSale = $modx->getOption('regular_sale', $data, $object ? $object->get('regular_sale') : '');
$result = [];

// Verify sale price is valid
if ((empty($regularRetail) && !empty($regularSale)) || ($regularRetail > 0 && $regularRetail <= $regularSale)) {
    $result['error'] = 'Regular sale must be less than the regular retail!';
    return $modx->toJson($result);
}

return '';
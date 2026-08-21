if(!empty($image) && !empty($partNumber)) {
    $id = $modx->resource->get('id');
    
    $out = $modx->getCollection('ProductVariation', [
        'deleted' => 0,
        'resource_id' => $id,
        'sku' => $partNumber,
        'nla' => 0
    ]);
    if (!$out) {
        return;
    }

    foreach($out as $value) {
        // temp
        $value = $value->toArray();

        $output = $modx->getChunk('FetchThreadOutput', array(
            'name' => $value['name'],
            'description' => $value['description'],
            'sku' => $value['sku'],
            'stock' => $value['stock'],
            'regular_retail' => $value['regular_retail'],
            'regular_sale' => $value['regular_sale'],
            'image' => $image,
            'id' => $value["id"],
            'back_order' => $value['back_order'],
        ));
        
        return $output;
    }
} else if(empty($image)) {
    return "Must pass image parameter.";
} else if (empty($partNumber)) {
    return "Must pass part number parameter.";
}
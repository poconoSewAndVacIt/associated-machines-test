// Quick view should not be indexed by Google
header("X-Robots-Tag: noindex", true);
// Prevent caching the modal
header("Cache-Control: no-cache, must-revalidate"); 

$type = $modx->getOption('type', $_REQUEST, 'variation');
$id = $modx->getOption('id', $_REQUEST, 0);
$errMsg = $modx->getChunk('variationQuickViewNotFound', []);

if (intval($id) === 0 || empty($type)) {
    echo $errMsg;
    die();
}

switch ($type) {
    case 'variation':
        $product = $modx->getObject('ProductVariation', [
            'deleted' => false,
            'nla' => false,
            'published' => true,
            'id' => $id
        ]);
        
        if (!$product) {
            echo $errMsg;
            die();
        }
        
        $placeholders = $product->toArray();
        
        if (empty($product->get('image'))) {
            $productResource = $modx->getObject('modResource', $product->get('resource_id'));
            if ($productResource) {
                $smImg = $productResource->getTVValue('Primary Product Image Small');
                if ($smImg) {
                    $placeholders['small_image'] = $smImg;
                }
                
                $placeholders['image'] = $productResource->getTVValue('Primary Product Image');
            }
        }
        echo $modx->getChunk('variationQuickView', $placeholders);
        
        break;
        
    default:
        echo $errMsg;
        break;
}
die();
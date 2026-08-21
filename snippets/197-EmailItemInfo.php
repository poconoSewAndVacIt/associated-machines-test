$output = "";

$variation = $modx->getObject('ProductVariation', $itemId);
if($variation) {
    $page = $modx->getObject('modResource', $variation->get('resource_id'));
} else {
    return $output;
}

if ($page->getTVValue('storeDepartment') === "Classes and Events") {
    $pdf = $page->getTVValue('Event PDF Link');
    if ($pdf) {
        $output .= $modx->getChunk('EmailClassPDF', [
            'link' => $pdf,
            'pagetitle' => $page->get('pagetitle')
        ]);
    }
    
    $output .= $modx->runSnippet('getImageList', array(
        'tvname' => 'Event Times',
        'tpl' => 'Event Times List',
        'docid' => $variation->get('resource_id')
    ));
}

return $output;
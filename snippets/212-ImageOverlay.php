$tv = $modx->getOption("tv", $scriptProperties, "ProductImageOverlays");
$overlays = $modx->getOption("value", $scriptProperties);

if (!$overlays) {
    $resource = $modx->getObject('modResource', $modx->getOption("resource", $scriptProperties, $modx->resource->get('id')));
    $overlays = $resource->getTVValue($tv);
}

$overlays = json_decode($overlays, true);


foreach ((array) $overlays as $o) {
    $ignoreDate = (bool) $o["ignore_date"];
    
    // Only allow 1 overlay (first found)
    if ((strtotime($o["pub_date"]) < time() && strtotime($o["unpub_date"]) > time()) || $ignoreDate) {
        $output = $o["content"];
        $position .= " " . $o["position"];
        break;
    }
}

return '<figcaption class="lazy image-overlay' . $position . '">' . $output . '</figcaption>';
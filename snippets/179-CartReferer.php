$ref = $_SERVER['HTTP_REFERER'];
$url = $modx->getOption('site_url');

if(!empty($ref) && !strpos($ref, "cart") && strpos($ref, $url) !== false) {
    return $ref;
} else {
    return $url;
}
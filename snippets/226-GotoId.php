$goto = $modx->getOption('goto', $_GET);

if (!$goto) {
    return;
}

// Redirect to current page if invalid URL
$url = $modx->makeUrl($goto);
if (empty($url)) {
    $url = $modx->makeUrl($modx->resource->get('id'));
}

$modx->sendRedirect($url);
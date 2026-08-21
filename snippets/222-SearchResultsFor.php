if ($modx->resource->get('id') !== 14 || empty($_GET['query'])) {
    return;
}

return '&nbsp;results for ' . htmlentities('"' . $_GET['query'] . '"');
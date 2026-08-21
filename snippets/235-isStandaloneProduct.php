if (!empty($modx->resource->getTVValue('Associated Products on Sidebar')) ||
    (isset($_GET['associated']) && $_GET['associated'] != '') ||
    (isset($_GET['pidref']) && $_GET['pidref'] != '') ||
    (isset($_GET['ptref']) && $_GET['ptref'] != '')) {
    return 0;
}

return 1;
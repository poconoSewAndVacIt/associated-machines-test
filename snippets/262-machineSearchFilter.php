$machine = $modx->getOption('machine', $_GET);
$query = $modx->getOption('query', $_GET);

if (!$machine || !$query) {
    return;
}

$_GET['query'] = $query . ' for ' . $machine;
$_REQUEST['query'] = $query . ' for ' . $machine;
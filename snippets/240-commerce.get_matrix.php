/**
 * @var modX $modx
 * @var array $scriptProperties
 */

// Instantiate the Commerce class
$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.get_product snippet.');
    return 'Could not load Commerce. Please try again later.';
}

$matrixId = (int)$modx->getOption('matrix', $scriptProperties, 0);
$object = $modx->getObject('comProductMatrix', [
    'id' => $matrixId,
    'removed' => false,
]);
if (!($object instanceof comProductMatrix)) {
    return '';
}

$matrix = new \modmore\Commerce\TVs\Matrix($commerce, $object);
$phs = $matrix->getValues();
$phs['properties'] = $scriptProperties;

$tpl = $modx->getOption('tpl', $scriptProperties, 'frontend/matrix/tabular.twig');

try {
    return $commerce->view()->render($tpl, $phs);
}
catch (\modmore\Commerce\Exceptions\ViewException $e) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Commerce] Exception parsing ' . $tpl . ': ' . $e->getMessage());
    return $e->getMessage();
}
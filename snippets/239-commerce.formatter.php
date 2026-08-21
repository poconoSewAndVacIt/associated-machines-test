/**
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

// Instantiate the Commerce class
$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.get_cart snippet.');
    return $input;
}

$formatter = isset($options) && !empty($options) ? $options : 'financial';
return $commerce->formatValue($input, $formatter);
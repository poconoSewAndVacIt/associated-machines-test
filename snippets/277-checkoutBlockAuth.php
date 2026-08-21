$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);

$order = \comOrder::loadUserOrder($commerce);
if (!$order) {
    return 0;
}

$address = $order->getBillingAddress();
if (!$address) {
    return 0;
}

$failedCount = $modx->getCount('comTransaction', ['order' => $order->get('id'), 'test' => $commerce->isTestMode()]);

if ($failedCount > 5) {
    return 1;
}

return 0;
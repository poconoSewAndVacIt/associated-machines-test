$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);

$order = \comOrder::loadUserOrder($commerce);
if (!$order) {
    return;
}

$address = $order->getBillingAddress();
if (!$address) {
    return;
}

if (strtolower($address->get('address1')) === 'danti court' || (strtolower($address->get('address1')) === 'danti ct') || (strtolower($address->get('fullname')) === 'emily rose')) {
    $modx->sendErrorPage();
}
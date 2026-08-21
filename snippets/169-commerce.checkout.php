use modmore\Commerce\Frontend\Checkout\Standard;

/**
 * @var modX $modx
 * @var array $scriptProperties
 */

// Instantiate the Commerce class in the controller

$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];

/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    return '<p class="error">Oops! It is not possible to checkout currently. We\'re sorry for the inconvenience. Please try again later.</p>';
}

if ($commerce->isDisabled()) {
    return $commerce->adapter->lexicon('commerce.mode.disabled.message');
}

$class = $modx->getOption('commerce.checkout_class', null, Standard::class, true);
if (!class_exists($class)) {
    return 'Invalid commerce.checkout_class: ' . htmlentities($class, ENT_QUOTES) . ' does\'t exist.';
}

$order = \comOrder::loadUserOrder($commerce);

/** @var \modmore\Commerce\Frontend\Checkout\Process $checkout */
$checkout = new $class($commerce, $order);
$checkout->run(array_merge($_GET, $_POST));
$response = $checkout->getResponse();

// If we requested the page via AJAX, we automatically return the JSON representation of the output.
$isXhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isAccept = !empty($_SERVER['HTTP_ACCEPT']) && strtolower($_SERVER['HTTP_ACCEPT']) === 'application/json';
if ($isXhr || $isAccept) {
    $data = $response->toArray();
    $data['output'] = $commerce->adapter->parseMODXTags($data['output']);
    header('Content-Type: application/json');
    echo json_encode($data);
    @session_write_close();
    exit();
}

// If we have a redirect (which might include redirects for payment gateways or moving to a different step in the checkout), send the user on their way, while keeping messages.
if ($response->getRedirect()) {
    $response->persistMessages();
    $response->sendRedirect();
}

// Register the default CSS if requested
if ($commerce->getBooleanOption('commerce.register_checkout_css')) {
    $modx->regClientCSS($commerce->config['assets_url'] . 'frontend/layout.simple.css');
    $modx->regClientCSS($commerce->config['assets_url'] . 'frontend/style.simple.css');
}

// Register the commerce payments js
$modx->regClientStartupScript($commerce->config['assets_url'] . 'frontend/payments.js?v=11');

// Return the markup to the user
$placeholders = $response->toArray();
$placeholders['order'] = $order->toArray();
$placeholders['steps'] = $checkout->getSteps();
$placeholders['currentKey'] = $checkout->currentKey;
$placeholders['commerce_mode'] = $commerce->getMode();

try {
    return $commerce->view()->render('frontend/checkout/wrapper.twig', $placeholders);
}
catch (\modmore\Commerce\Exceptions\ViewException $e) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Commerce] Exception parsing frontend/checkout/wrapper.twig: ' . $e->getMessage());
    return $e->getMessage();
}
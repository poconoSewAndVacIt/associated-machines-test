/**
 * @var modX $modx
 * @var array $scriptProperties
 */

use modmore\Commerce\Events\OrderPlaceholders;

// Instantiate the Commerce class
$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.get_orders snippet.');
    return 'Could not load Commerce. Please try again later.';
}

if ($commerce->isDisabled()) {
    return $commerce->adapter->lexicon('commerce.mode.disabled.message');
}
$modx->lexicon->load('commerce:frontend');

// Make sure that only logged in users can actually see the orders
$user = $commerce->adapter->getUser();
if (!$user || $user->get('id') < 1) {
    return $modx->sendUnauthorizedPage();
}

// Get the order ID
$orderId = array_key_exists('order', $_GET) && is_numeric($_GET['order']) ? (int)$_GET['order'] : false;
if (array_key_exists('order', $scriptProperties) && is_numeric($scriptProperties['order']) && $scriptProperties['order'] > 0) {
    $orderId = $scriptProperties['order'];
}

// Get the invoice reference
$invoiceId = (int)$modx->getOption('downloadInvoice', $_GET, 0);
$invoiceId = (int)$modx->getOption('downloadInvoice', $scriptProperties, $invoiceId);

// @todo support different download types
// $invoiceDownloadType = (string)$modx->getOption('downloadType', $_GET, 'pdf');
// $invoiceDownloadType = $modx->getOption('downloadType', $scriptProperties, $invoiceDownloadType);

// A couple of options that can be provided to the snippet
$tpl = (string)$modx->getOption('tpl', $scriptProperties, 'frontend/account/order-detail.twig');
$loadInvoices = (bool)$modx->getOption('loadInvoices', $scriptProperties, true);
$loadItems = (bool)$modx->getOption('loadItems', $scriptProperties, true);
$loadStatus = (bool)$modx->getOption('loadStatus', $scriptProperties, true);
$loadTransactions = (bool)$modx->getOption('loadTransactions', $scriptProperties, true);
$loadShipments = (bool)$modx->getOption('loadShipments', $scriptProperties, true);
$loadBillingAddress = (bool)$modx->getOption('loadBillingAddress', $scriptProperties, true);
$loadShippingAddress = (bool)$modx->getOption('loadShippingAddress', $scriptProperties, true);
$loadOrderFields = (bool)$modx->getOption('loadOrderFields', $scriptProperties, true);

$allowedClasses = ['comProcessingOrder', 'comCompletedOrder'];
foreach ($allowedClasses as $ac) {
    $allowedClasses = array_merge($allowedClasses, $modx->getDescendants($ac));
}
$allowedClasses = array_unique($allowedClasses);

// Attempt to load the orders
$c = $commerce->adapter->newQuery('comOrder');
$c->where([
    'id' => $orderId,
    'user' => $user->get('id'),
    'test' => $commerce->isTestMode(),
    'class_key:IN' => $allowedClasses,
]);

/** @var comOrder $order */
$order = $commerce->adapter->getObject('comOrder', $c);
if (!$order) {
    return $modx->sendErrorPage();
}

// Dispatch event so that modules can merge their own placeholders into the order
$event = $commerce->dispatcher->dispatch(\Commerce::EVENT_ORDER_PLACEHOLDERS, new OrderPlaceholders($order));
$phs = $event->getPlaceholders();

// Grab the data
$data = [];
$data['order'] = $order->toArray();
$data['order'] = array_merge($data['order'], $phs);
$data['state'] = $order->getState();

// if there's an invoice ID set, this will short-circuit the snippet and
// try to download the invoice. If that invoice ID doesn't exist or is
// invalid, the snippet will continue, so users won't really have any indication
// that they tried to get an invalid invoice (like for one that isn't part of their
// order)

if ($invoiceId) {
    $invoice = $order->getInvoice($invoiceId);
    if ($invoice) {
        $invoice->downloadPDF();
    }
}

if ($loadInvoices) {
    $invoices = [];
    foreach ($order->getInvoices() as $invoice) {
        $invoices[] = $invoice->toArray();
    }
    $data['invoices'] = $invoices;
}

if ($loadItems) {
    $items = [];
    foreach ($order->getItems() as $item) {
        $ta = $item->toArray();
        if ($product = $item->getProduct()) {
            $ta['product'] = $product->toArray();
        }
        $items[] = $ta;
    }
    $data['items'] = $items;
}
if ($loadStatus) {
    $status = $order->getStatus();
    $data['status'] = $status->toArray();
}

if ($loadTransactions) {
    $trans = [];
    $transactions = $order->getTransactions();
    foreach ($transactions as $transaction) {
        if ($transaction->isCompleted()) {
            $traa = $transaction->toArray();
            if ($method = $transaction->getMethod()) {
                $traa['method'] = $method->toArray();
            }
            $trans[] = $traa;
        }
    }
    $data['transactions'] = $trans;
}
if ($loadShipments) {
    $ships = [];
    $shipments = $order->getShipments();
    foreach ($shipments as $shipment) {
        $sta = $shipment->toArray();
        if ($method = $shipment->getShippingMethod()) {
            $sta['method'] = $method->toArray();
        }
        $ships[] = $sta;
    }
    $data['shipments'] = $ships;
}

if ($loadBillingAddress) {
    $ba = $order->getBillingAddress();
    $data['billing_address'] = $ba->toArray();
}
if ($loadShippingAddress) {
    $sa = $order->getShippingAddress();
    $data['shipping_address'] = $sa->toArray();
}
if ($loadOrderFields) {
    $data['order_fields'] = [];
    $fields = $order->getOrderFields();
    foreach ($fields as $fld) {
        $rendered = $fld->renderForCustomer();
        if (!empty($rendered)) {
            $data['order_fields'][$fld->getName()] = $rendered;
        }
    }
}

if ($tpl !== '') {
    try {
        $output = $commerce->view()->render($tpl, $data);
    }
    catch (\modmore\Commerce\Exceptions\ViewException $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Error processing get_order snippet on resource #' . $modx->resource->get('id') . ' - ' . get_class($e) . ': ' . $e->getMessage());
        return 'Sorry, could not show your order.';
    }
} else {
    $output = '<pre>' . print_r($data, true) . '</pre>';
}

return $output;
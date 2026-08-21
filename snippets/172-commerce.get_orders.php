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

// Make sure that only logged in users can actually see the orders
$user = $commerce->adapter->getUser();
if (!$user || $user->get('id') < 1) {
    return $modx->sendUnauthorizedPage();
}

// Load frontend lexicon
$commerce->adapter->loadLexicon('commerce:frontend');
// A couple of options that can be provided to the snippet
$tpl = (string)$modx->getOption('tpl', $scriptProperties, 'frontend/account/orders.twig');
$sortby = (string)$modx->getOption('sortby', $scriptProperties, 'created_on');
$sortdir = (string)$modx->getOption('sortdir', $scriptProperties, 'DESC');
$limit = (int)$modx->getOption('limit', $scriptProperties, 10);
$offset = (int)$modx->getOption('offset', $scriptProperties, 0);
$totalVar = (string)$modx->getOption('totalVar', $scriptProperties, 'total');
$loadInvoices = (bool)$modx->getOption('loadInvoices', $scriptProperties, true);
$loadItems = (bool)$modx->getOption('loadItems', $scriptProperties, true);
$loadStatus = (bool)$modx->getOption('loadStatus', $scriptProperties, true);
$loadTransactions = (bool)$modx->getOption('loadTransactions', $scriptProperties, true);
$loadShipments = (bool)$modx->getOption('loadShipments', $scriptProperties, true);
$loadBillingAddress = (bool)$modx->getOption('loadBillingAddress', $scriptProperties, true);
$loadShippingAddress = (bool)$modx->getOption('loadShippingAddress', $scriptProperties, true);
$loadOrderFields = (bool)$modx->getOption('loadOrderFields', $scriptProperties, true);
$loadModuleFields = (bool)$modx->getOption('loadModuleFields', $scriptProperties, false);
$where = (string)$modx->getOption('where', $scriptProperties, '[]');
$logSql = (bool)$modx->getOption('logSql', $scriptProperties, false);

$allowedClasses = ['comProcessingOrder', 'comCompletedOrder'];
foreach ($allowedClasses as $ac) {
    $allowedClasses = array_merge($allowedClasses, $modx->getDescendants($ac));
}
$allowedClasses = array_unique($allowedClasses);

// Attempt to load the orders
$c = $commerce->adapter->newQuery('comOrder');
$c->where([
    'user' => $user->get('id'),
    'test' => $commerce->isTestMode(),
    'class_key:IN' => $allowedClasses,
]);
$extraConditions = json_decode($where, true);
if (is_array($extraConditions)) {
    $c->andCondition($extraConditions);
}
$c->sortby($sortby, $sortdir);

$total = $modx->getCount('comOrder', $c);
$modx->setPlaceholder($totalVar, $total);

$c->limit($limit, $offset);

if ($logSql) {
    $c->prepare();
    $modx->log(modX::LOG_LEVEL_ERROR, '[commerce.get_orders] Fetching orders with query: ' . $c->toSQL() . ' - generated from properties ' . print_r($scriptProperties, true));
}

/** @var comOrder[] $orders */
$orders = $commerce->adapter->getCollection('comOrder', $c);
$total = count($orders);
$i = 0;
$allData = ['orders' => []];
foreach ($orders as $order) {
    // Grab the data
    $data = $order->toArray();

    // Dispatch event so that modules can merge their own placeholders into the order
    if ($loadModuleFields) {
        $event = $commerce->dispatcher->dispatch(\Commerce::EVENT_ORDER_PLACEHOLDERS, new OrderPlaceholders($order));
        $phs = $event->getPlaceholders();
        $data = array_merge($data, $phs);
    }

    $data['state'] = $order->getState();

    if ($loadInvoices) { // Grab the data
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

    $allData['orders'][] = $data;
    $i++;
}

if ($tpl !== '') {
    try {
        $output = $commerce->view()->render($tpl, $allData);
    }
    catch (\modmore\Commerce\Exceptions\ViewException $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Error processing get_orders snippet on resource #' . $modx->resource->get('id') . ' - ' . get_class($e) . ': ' . $e->getMessage());
        return 'Sorry, could not show your orders.';
    }
} else {
    $output = '<pre>' . print_r($allData, true) . '</pre>';
}

return $output;
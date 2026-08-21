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
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service in commerce.get_cart snippet.');
    return 'Could not load Commerce. Please try again later.';
}

if ($commerce->isDisabled()) {
    return $commerce->adapter->lexicon('commerce.mode.disabled.message');
}

$order = comOrder::loadUserOrder($commerce);
// Make sure the order totals add up
$order->calculate();

// A couple of options that can be provided to the snippet
$toPlaceholders = (string)$modx->getOption('toPlaceholders', $scriptProperties, '');
$tpl = (string)$modx->getOption('tpl', $scriptProperties, '');
$emptyTpl = (string)$modx->getOption('emptyTpl', $scriptProperties, '');
$itemTpl = (string)$modx->getOption('itemTpl', $scriptProperties, '');
$separator = (string)$modx->getOption('separator', $scriptProperties, '');
$field = (string)$modx->getOption('field', $scriptProperties, '');
$loadOrderFields = (bool)$modx->getOption('loadOrderFields', $scriptProperties, true);
$hasTpl = $tpl !== '' || $emptyTpl !== '';

$data = $order->toArray();
$items = $order->getItems(false);
$itemData = [];
foreach ($items as $item) {
    $ta = $item->toArray();
    if ($product = $item->getProduct()) {
        $ta['product'] = $product->toArray();
    }
    $itemData[] = $ta;
}
$data['items'] = $itemData;

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

// Either output as placeholders...
if ($toPlaceholders !== '') {
    $modx->toPlaceholders($data, rtrim($toPlaceholders, '.'));
    return '';
}
// ... wrapped in chunks ...
elseif ($hasTpl) {
    // If we have a tpl and items , show that.
    if ($tpl !== '' && count($items) > 0) {
        // Parse items too if needed
        if ($itemTpl !== '') {
            $itemOutput = [];
            foreach ($data['items'] as $item) {
                $itemOutput[] = $commerce->getChunk($itemTpl, $item);
            }
            $itemOutput = implode($separator, $itemOutput);
            $data['items'] = $itemOutput;
        }
        return $commerce->getChunk($tpl, $data);
    }

    // If we have an empty tpl and no items, show that
    if ($emptyTpl !== '' && count($items) === 0) {
        return $commerce->getChunk($emptyTpl);
    }
}
// ... just a specific order field ...
elseif ($field !== '' && array_key_exists($field, $data)) {
    return $data[$field];
}
// ... or dump the data.
else {
    return '<pre>' . print_r($data, true) . '</pre>';
}
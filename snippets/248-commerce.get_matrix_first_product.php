/**
 * @var modX $modx
 * @var array $scriptProperties
 */

// Instantiate the Commerce class
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

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

// A couple of options that can be provided to the snippet
$toJson = (bool)$modx->getOption('toJson', $scriptProperties, false);
$jsonFields = (string)$modx->getOption('jsonFields', $scriptProperties, '');
$toPlaceholders = (string)$modx->getOption('toPlaceholders', $scriptProperties, '');
$tpl = (string)$modx->getOption('tpl', $scriptProperties, '');
$field = (string)$modx->getOption('field', $scriptProperties, '');
$withImage = (bool)$modx->getOption('withImage', $scriptProperties, false);
$withStock = (bool)$modx->getOption('withStock', $scriptProperties, false);
$sort = (string)$modx->getOption('sort', $scriptProperties, 'columns');

$c = $commerce->adapter->newQuery('comProductMatrixProduct');
// Join the matrix rows and columns, specifying the matrix id and conditions specifically
// to avoid potential issues when there are db duplicates
$c->innerJoin('comProductMatrixRow', 'Row', [
    'comProductMatrixProduct.row = Row.id',
    'Row.matrix' => $matrixId,
    'Row.active' => true,
]);
$c->innerJoin('comProductMatrixColumn', 'Column', [
    'comProductMatrixProduct.column = Column.id',
    'Column.matrix' => $matrixId,
    'Column.active' => true,
]);

$desc = array_merge(['comProductMatrixProduct'], $commerce->adapter->getDescendants('comProductMatrixProduct'));
$c->where([
    'removed' => false,
    'class_key:IN' => $desc,
    'matrix' => $matrixId,
    'Row.active' => true,
    'Column.active' => true,
]);

if ($withImage) {
    $c->where([
        'image:!=' => ''
    ]);
}
if ($withStock) {
    $c->where([
        'stock:>' => 0
    ]);
}

if ($sort === 'columns') {
    // Sort by column + row this way, means we'll first get products in column 0 [row 1-x],
    // then column 1 [row 2-x], etc.
    $c->sortby('Column.sortorder', 'ASC');
    $c->sortby('Row.sortorder', 'ASC');
}
elseif ($sort === 'rows') {
    $c->sortby('Row.sortorder', 'ASC');
    $c->sortby('Column.sortorder', 'ASC');
}
elseif ($sort === 'price') {
    $c->innerJoin('comProductPriceIndex', 'PriceIndex');
    $c->where([
        'PriceIndex.currency' => $commerce->currency->get('alpha_code'),
    ]);
    $c->sortby('PriceIndex.price', 'ASC');
    $c->sortby('Column.sortorder', 'ASC');
    $c->sortby('Row.sortorder', 'ASC');
}

$c->limit(1);

// Attempt to load the product
$product = $commerce->adapter->getObject('comProduct', $c);
if ($product instanceof comProduct) {
    // Sync the product info
    $product->synchronise();

    // Grab the data
    $data = $product->toArray();
    if ($row = $product->getOne('Row')) {
        $data['row'] = $row->get(['id', 'sku', 'name', 'description']);
    }
    if ($column = $product->getOne('Column')) {
        $data['column'] = $column->get(['id', 'sku', 'name', 'description']);
    }

    // Add the formatted weight
    $weight = $product->getWeight();
    if ($weight instanceof Mass) {
        $data['weight_formatted'] = (string)$weight;
    }

    if ($toPlaceholders !== '') {
        $modx->toPlaceholders($data, rtrim($toPlaceholders, '.'));
        return '';
    }

    if ($toJson) {
        // Filter output fields
        $jsonData = $data;
        $jsonFields = $specifiedFields = !empty($jsonFields) ? explode(',', $jsonFields) : [];
        if (!empty($jsonFields)) {
            $jsonData = [];

            foreach ($jsonFields as $fld) {
                $parts = explode('.', $fld);
                $data[$parts[0]] = $data[$parts[0]] ?? '';
                if (is_array($data[$parts[0]]) && isset($parts[1])) {
                    $jsonData[$parts[0]] = $jsonData[$parts[0]] ?? [];
                    $jsonData[$parts[0]][$parts[1]] = $data[$parts[0]][$parts[1]] ?? '';
                }
                else {
                    $jsonData[$parts[0]] = $data[$parts[0]];
                }
            }
        }

        // Handle encoding a Pricing object
        if (!empty($jsonData['pricing']) && $jsonData['pricing'] instanceof \modmore\Commerce\Pricing\Pricing) {
            $jsonData['pricing'] = $jsonData['pricing']->serialize();
        }

        // If a tpl is also specified, parse and add it to the JSON output
        if ($tpl !== '') {
            $jsonData['tpl'] = $commerce->getChunk($tpl, $data);
        }

        return json_encode($jsonData);
    }
    if ($tpl !== '') {
        return $commerce->getChunk($tpl, $data);
    }

    if ($field !== '' && array_key_exists($field, $data)) {
        return $data[$field];
    }
    if (strpos($field, '.') !== false) {
        $parts = explode('.', $field, 2);
        if (isset($data[$parts[0]]) && is_array($data[$parts[0]]) && array_key_exists($parts[1], $data[$parts[0]])) {
            return $data[$parts[0]][$parts[1]];
        }
    }

    return '<pre>' . print_r($data, true) . '</pre>';
}

$modx->log(modX::LOG_LEVEL_ERROR, '[Commerce.get_matrix_first_product] Unable to fetch product information for ' . $matrixId . ' with query: ' . $c->toSQL());
return '';
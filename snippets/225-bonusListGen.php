$items = $modx->getOption('items', $scriptProperties, '');
$output = '';

if (!$items) {
    return false;
}

$items = array_map('trim', explode(',', $items));
foreach ($items as $item) {
    $output .= '<li>' . $item . '</li>';
}

return '<ul class="bonus-list nomargin">' . $output . '</ul>';
$input = $modx->getOption('value', $scriptProperties, '');
$img = $modx->runSnippet('imgix', [
    'input' => $input,
    'options' => 'w=100'
]);

if (!empty($img)) {
    return '<img src="' . $img . '" width="100">';
}
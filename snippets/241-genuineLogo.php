$outputType = $modx->getOption('output', $scriptProperties, 'full');
$label = $modx->getOption('label', $scriptProperties, '');

if (!$label) {
    return;
}

switch ($outputType) {
    case 'full':
        return $modx->getChunk($label);
    case 'icon':
        return $modx->getChunk($label . 'Small');
    case 'text':
        if ($label !== 'GuaranteedToFit') {
            return '<i class="fas fa-check-circle"></i> Genuine Part';
        }
        
        return;
    default:
        return;
}
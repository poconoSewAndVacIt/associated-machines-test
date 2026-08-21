$resourceId = $modx->getOption('resource', $scriptProperties, $modx->resource->get('id'));

$threads = $modx->getCollection('ProductVariation', [
    'deleted' => false,
    'nla' => false,
    'published' => true,
    'resource_id' => $resourceId,
]);

if (!$threads) return;

foreach ($threads as $thread) {
    foreach ($thread->toArray() as $k => $v) {
        $modx->setPlaceholder('thread.' . $thread->get('id') . '.' . $k, $v);
    }
}
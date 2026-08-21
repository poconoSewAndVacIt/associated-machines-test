/**
 * Gets tags for all or just the current resource.
 *
 * @author Mark Hamstra for modmore <support@modmore.com>
 * @var modX $modx
 * @var array $scriptProperties
 */

$scriptProperties = array_merge(array(
    'cache' => true,
    'resource' => '',
    'sortBy' => 'display',
    'sortDir' => 'ASC',
    'where' => '',

    'tpl' => 'mgtag',
    'separator' => "\n",
    'wrapperTpl' => '',
    'wrapperIfEmpty' => true,
    'toPlaceholder' => '',

    'includeCount' => false,

    'totalVar' => 'total',
    'limit' => 0,
    'offset' => 0,
), $scriptProperties);
// Default to the current resource if none is set
if (!is_numeric($scriptProperties['resource'])) {
    if ($modx->resource) { // but only if we have a resource
        $scriptProperties['resource'] = $modx->resource->get('id');
    }
}

/** @var moreGallery $moreGallery */
$corePath = $modx->getOption('moregallery.core_path', null, $modx->getOption('core_path') . 'components/moregallery/');
$moreGallery = $modx->getService('moregallery', 'moreGallery', $corePath . 'model/moregallery/');
if (!($moreGallery instanceof moreGallery)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Error loading moreGallery class from ' . $corePath);
    return 'Error loading moreGallery class.';
}

// Get (and set) the working context
$context = ($modx->context instanceof modContext) ? $modx->context->get('key') : 'web';
if ($scriptProperties['resource'] > 0) {
    if ($modx->resource && $modx->resource->get('id') == $scriptProperties['resource']) {
        $context = $modx->resource->get('context_key');
    }
    else {
        $resource = $modx->getObject('modResource', (int)$scriptProperties['resource']);
        if ($resource instanceof modResource) {
            $context = $resource->get('context_key');
        }
    }
}
$moreGallery->setWorkingContext($context);

// Get caching related options
$cacheOptions = array(xPDO::OPT_CACHE_KEY => 'moregallery');
$cacheKey = 'tags/r_' . md5(serialize($scriptProperties));
$chunkHash = md5($moreGallery->getChunk($scriptProperties['tpl'])) . md5($moreGallery->getChunk($scriptProperties['wrapperTpl'], array('output' => '')));

/**
 * Get from cache if we can
 */
if ($scriptProperties['cache']) {
    $cached = $modx->cacheManager->get($cacheKey, $cacheOptions);
    if (is_array($cached)) {
        if ($chunkHash == $cached['chunkHash']) {
            return $cached['formatted'];
        }
    }
}

// Start the query to find tags
$c = $modx->newQuery('mgTag');
$c->select($modx->getSelectColumns('mgTag', 'mgTag'));

// Limit results to a specific resource only?
if ($scriptProperties['resource'] > 0) {
    $subc = $modx->newQuery('mgImageTag');
    $subc->innerJoin('mgImage', 'Image');
    $subc->where(array(
        'resource' => $scriptProperties['resource'],
        '`tag` = mgTag.id',
        'Image.active' => true,
    ));
    $subc->prepare();
    $c->where('EXISTS (' . $subc->toSQL() . ')');
}

// Include the image count?
if ($scriptProperties['includeCount']) {
    $subc = $modx->newQuery('mgImageTag');
    $subc->innerJoin('mgImage', 'Image');
    $subc->select(array(
        'COUNT(Image.id) as image_count'
    ));
    $subc->where(array(
        'mgImageTag.tag = mgTag.id',
        'Image.active' => true,
    ));
    if ($scriptProperties['resource'] > 0) {
        $subc->where(array(
            'Image.resource' => $scriptProperties['resource']
        ));
    }
    $subc->groupby('mgImageTag.tag');

    $subc->prepare();
    $c->select(array(
        '( ' . $subc->toSQL() . ' ) as image_count'
    ));
}

// Any custom filter options?
if (!empty($scriptProperties['where'])) {
    $where = $modx->fromJSON($scriptProperties['where']);
    if (is_array($where))
    {
        $c->where($where);
    }
    else
    {
        $modx->log(modX::LOG_LEVEL_ERROR, '&where property is not valid JSON: ' . $scriptProperties['where'], '', 'mgGetTags', __FILE__, __LINE__);
    }
}

// Sort it as specified
$c->sortby($scriptProperties['sortBy'], $scriptProperties['sortDir']);

// Get the number of results, and support getPage by setting a totalVar and limiting results as requested
$total = $modx->getCount('mgImage', $c);
$modx->setPlaceholder($scriptProperties['totalVar'], $total);
if ($scriptProperties['limit'] > 0) {
    $c->limit($scriptProperties['limit'], $scriptProperties['offset']);
}

// Fetch the tags
$tags = array();
$i = 0;
/** @var mgTag $tag */
foreach ($modx->getIterator('mgTag', $c) as $tag) {
    $tArray = $tag->toArray('', false, true);
    $tArray['idx'] = $i;
    $tArray['image_count'] = empty($tArray['image_count']) ? 0 : $tArray['image_count'];
    $tags[] = $tArray;
    $i++;
}

$formatted = array();
foreach ($tags as $t) {
    $formatted[] = $moreGallery->getChunk($scriptProperties['tpl'], $t);
}
$formatted = implode($scriptProperties['separator'], $formatted);

if (!empty($scriptProperties['wrapperTpl'])) {
    $formatted = $moreGallery->getChunk($scriptProperties['wrapperTpl'], array('output' => $formatted, 'tag_total' => $total));
}

$cached = array(
    'formatted' => $formatted,
    'chunkHash' => $chunkHash,
    'cached' => date('c'),
);
// Write to cache
$modx->cacheManager->set($cacheKey, $cached, 0, $cacheOptions);

if (!empty($scriptProperties['toPlaceholder'])) {
    $modx->setPlaceholder($scriptProperties['toPlaceholder'], $formatted);
} else {
    return $formatted;
}
$modx->addPackage('podcasts', MODX_CORE_PATH . 'components/podcasts/model/');

$queriedPodcasts = $modx->getCollection('Podcast', [
    'resource_id' => $modx->resource->get('id'),
    'published' => 1,
    'deleted' => 0
]);

$podcasts = [];
foreach ((array) $queriedPodcasts as $podcast) {
    $podcasts[] = [
        'name' => $podcast->get('title'),
        'artist' => 'Pocono Sew & Vac',
        'url' => $podcast->get('link'),
        'cover_art_url' => $modx->runSnippet('imgix', [
            'input' => $modx->resource->getTVValue('SewCastPrimaryImage'),
            'options' => 'w=370&h=370&fit=fill&bg=ffffff&auto=format&fm=png&fill=blur'
        ])
    ];
}

return json_encode($podcasts, true);
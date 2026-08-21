$tags = $modx->getOption('tags', $scriptProperties, []);
$groups = $modx->getOption('groups', $scriptProperties, []);

$tags = array_map('trim', explode(',', $tags));
$groups = array_map('trim', explode(',', $groups));

if (empty($tags)) return 0;
if (!$modx->addPackage('tagger', $modx->getOption('tagger.core_path', null, $modx->getOption('core_path').'components/tagger/').'model/')) return 0; 

$q = $modx->newQuery('modResource');
$q->distinct();
$q->select($modx->getSelectColumns('modResource', 'modResource', '', ['parent', 'id']));
$q->leftJoin('TaggerTagResource', 'TaggerTagResource', ['modResource.id = TaggerTagResource.resource']);
$q->leftJoin('TaggerTag', 'TaggerTag', ['TaggerTag.id = TaggerTagResource.tag']);

$q->where([
    'TaggerTag.alias:IN' => $tags,
]);

if (!empty($groups)) {
    $q->leftJoin('TaggerGroup', 'TaggerGroup', ['TaggerGroup.id = TaggerTag.`group`']);
    $q->where([
        'TaggerGroup.alias:IN' => $groups
    ]);
}

$results = $modx->getIterator('modResource', $q);
if (!$results) return 0;

$output = [];
foreach ($results as $r) {
    $output[] = $r->get('parent');
}

return implode(',', $output);
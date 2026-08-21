$id = $modx->getOption('id', $scriptProperties, 0);
$wrapTpl = $modx->getOption('wrapTpl', $scriptProperties, 'instructorUpcomingClassesWrap');
$tpl = $modx->getOption('tpl', $scriptProperties, 'Event Listings Output');

if ($id === 0) {
    return;
}

// Validation
$pkg = $modx->addPackage('teachers', MODX_CORE_PATH.'components/teachers/model/');
if (!$pkg) return;

$instructor = $modx->getObject('Teachers', $id);
if (!$instructor) return;

// Fetch all classes by the teacher
$classIdsQuery = $modx->getCollection('modTemplateVarResource', [
    'FIND_IN_SET('.$id.', value) > 0',
    'tmplvarid' => 66, // fetch teachers
]);
if (!$classIdsQuery) return; //$modx->getChunk($wrapTpl, array_merge($instructor->toArray(), ['error' => 'No upcoming classes found for ' . $instructor->getName() . '.']));

$classIds = [];
foreach ($classIdsQuery as $classId) {
    $classIds[] = $classId->get('contentid');
}

// Get class content
$classesQuery = $modx->newQuery('modResource');
$classesQuery->where([
    'id:IN' => $classIds,
    'deleted' => false,
    'published' => true,
]);
$classesQuery->sortby('unpub_date', 'ASC');
$classes = $modx->getCollection('modResource', $classesQuery);
if (!$classes) return; //$modx->getChunk($wrapTpl, array_merge($instructor->toArray(), ['error' => 'No upcoming classes found for ' . $instructor->getName() . '.']));

$output = '';
foreach ($classes as $class) {
    // Iterate through templates variables to add to output
    $tvs = $class->getTemplateVars();
    $formattedTvs = [];
    foreach ($tvs as $tv) {
        $formattedTvs[$tv->get('name')] = $tv->get('value');
    }
    
    $output .= $modx->getChunk($tpl, array_merge($class->toArray(), $formattedTvs));
}

return $modx->getChunk($wrapTpl, array_merge($instructor->toArray(), ['output' => $output]));
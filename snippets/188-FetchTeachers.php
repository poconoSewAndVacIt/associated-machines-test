$tpl = $modx->getOption('tpl', $scriptProperties, 'FetchTeachers');
$tvName = $modx->getOption('tvName', $scriptProperties, 'Event Teachers');
$separator = $modx->getOption('separator', $scriptProperties, ', ');

// Add the package
$searchPkg = $modx->addPackage('teachers', MODX_CORE_PATH.'components/teachers/model/');
if (!$searchPkg) {
    return "Failed to add package";
}

$output = "";

if(!isset($fetch)) {
    // Listbox multiple builder
    // Build the query for redirects first
    $teachers = $modx->getCollection('Teachers');
    $count = $modx->getCount('Teachers');
    
    $i = 1;
    foreach ($teachers as $teacher) {
        $output .= $teacher->get('teacher_name') . "==" . $teacher->get('id');
        if($i !== $count)
            $output .= "||";
        $i++;
    }
    
    return $output;
} else if ($fetch) {
    // Frontend output
    $assignedTeachers = explode(",", $modx->resource->getTVValue($tvName));
    $query = ['id:IN' => $assignedTeachers];


    // Database Queries
    $teachers = $modx->getCollection('Teachers', $query);
    $count = $modx->getCount('Teachers', $query);

    $i = 1;
    foreach ($teachers as $teacher) {
        $output .= $modx->getChunk($tpl, array(
            'teacher_name' => $teacher->get('teacher_name'),
            'teacher_link' => $teacher->get('teacher_id') ? $modx->makeUrl($teacher->get('teacher_id')) : 0,
            'teacher_image' => $teacher->get('teacher_image'),
            'teacher_count' => $count,
        ));
        if ($i !== $count)
            $output .= $separator;
        $i++;
    }
}

return $output;
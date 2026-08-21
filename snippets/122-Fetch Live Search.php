$qSearchArr = $modx->runSnippet('fetchResults');
    
    if(empty($qSearchArr)) {
        $qSearchArr[0] = "NO-SEARCH-RESULTS";
    }

    $output = json_encode($qSearchArr);
    return $output;
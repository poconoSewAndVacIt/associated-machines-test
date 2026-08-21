if(!(isset($_GET['query']) && $_GET['query'] != '')) {
        return;
    }
    // Get All Results
    $qSearch = $modx->runSnippet('SimpleSearch',array(
        'docFields'=>'pagetitle,longtitle,alias,description,introtext',
        'minChars'=>'1',
        'searchIndex'=>'query',
        'offsetIndex'=>'querypage',
        'highlightClass'=>'',
        'noResultsTpl'=>'',
        'currentPageTpl'=>'',
        'pageTpl'=>'',
        'containerTpl'=>'Store Search Results Live Wrap',
        'tpl'=>'Store Search Results Live'
    ));
    
    // Format Results
    $qSearch = str_replace("\n","", $qSearch);
    $qSearch = substr($qSearch, 0, -3);
    $qSearchArr = explode("===", $qSearch);
        
    $i = 0;
    foreach($qSearchArr as $value) {
        $qSearchTemp = explode("&&&", $value);
        
        $qSearchTemp["name"] = $qSearchTemp[0];
        unset($qSearchTemp[0]);
        $qSearchTemp["url"] = $qSearchTemp[1];
        unset($qSearchTemp[1]);
        
        $qSearchArr[$i] = (object) $qSearchTemp;
        $i++;
    }
    
    if($qSearchArr[0] == "") {
        $qSearchArr = [];
    }

    return $qSearchArr;
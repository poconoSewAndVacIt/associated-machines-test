$searchResults = $modx->runSnippet('fetchResults');

    if(count($searchResults)==1){
        $modx->sendRedirect($searchResults[0]->url);
        return;
    }

    return;
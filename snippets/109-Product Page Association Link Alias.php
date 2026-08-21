if (isset($_GET['associated']) && $_GET['associated'] != '' || isset($associated)) {
    if (isset($associated)) {
        $alias = $associated;
    } else {
        $alias = $_GET['associated'];
    }
    $aliasLink= $modx->runSnippet('migxLoopCollection', array(
        'packageName'=>'tagger',
        'classname'=>'TaggerTag',
        'where'=>'{"group":"2","alias":"'.$alias.'"}',
        'tpl'=>'@CODE:[[+alias]]'
    ));
    $output = $modx->setPlaceholder('aliasLink',$aliasLink);
    return $output;
}
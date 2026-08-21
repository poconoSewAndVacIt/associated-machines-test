/**
* POCONOSEWANDVAC.COM
* This snippet outputs the product type brand title.
*/
if (isset($_GET['filter']) && $_GET['filter'] != '') {
    $alias = $_GET['filter'];
    $aliasToName = $modx->runSnippet('migxLoopCollection', array(
        'packageName'=>'tagger',
        'classname'=>'TaggerTag',
        'where'=>'{"group":"1","alias":"'.$alias.'"}',
        'tpl'=>'@CODE: [[+tag]]'
    ));
    return $aliasToName.' ';
}
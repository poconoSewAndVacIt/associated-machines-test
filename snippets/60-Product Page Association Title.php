/**
* POCONOSEWANDVAC.COM
* This snippet outputs the association product type title.
*
* @author Tony Klapatch <tonyklapatch@hotmail.com>
*/
if ((isset($_GET['associated']) && $_GET['associated'] != '') || isset($associated)) {
    $alias = $_GET['associated'];
    $aliasToName = $modx->runSnippet('migxLoopCollection', array(
        'packageName'=>'tagger',
        'classname'=>'TaggerTag',
        'where'=>'{"group":"2","alias":"'.$alias.'"}',
        'tpl'=>'@CODE: [[+tag]]'
    ));
    return trim($aliasToName);
}
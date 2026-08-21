$results = $modx->query("SELECT * FROM psvmodx.modx_site_content WHERE parent = 961;");
$data = $results->fetchAll(PDO::FETCH_ASSOC);

$arr = [];
foreach($data as $value) {
    $arr[] = $value["id"];
}

foreach ($arr as $a) {
    $tv = $modx->getObject('modTemplateVar',34);
    $new = $modx->runSnippet('TaggerGetTags', array(
        'resources' => $a,
        'separator' => ',',
        'rowTpl' => '@INLINE [[+tag]], [[+alias]]'
    ));
        
    $tv->setValue($a, trim($new));
    $tv->save();
}
$bannerTpl = $modx->getOption("bannerTpl", $scriptProperties, "psvBannerTpl");
$tplWrapper = $modx->getOption("tplWrapper", $scriptProperties, "psvBannerWrapTpl");
$resource = $modx->getOption("resource", $scriptProperties, $modx->resource->get('id'));
$alias = $modx->getOption("alias", $scriptProperties, "");

// Add package
$psvBanners = $modx->addPackage('psvbanners', MODX_CORE_PATH.'components/psvbanners/model/');
if (!$psvBanners) return '';

// Get the banners
$c = $modx->newQuery('psvBanners');
$c->sortby('pos', 'ASC');
$c->where([
    'resource' => $resource,
    'alias' => $alias,
    'deleted' => 0
]);
$banners = $modx->getCollection("psvBanners", $c);

// Get current date and time for comparison for setting publish
$now = new DateTime('now');
$now = $now->format('Y-m-d H:i:s');

$position = 1;
foreach ($banners as $banner) {
    if ($banner->get('ignore_date') === 1 || ($banner->get('unpub_date') > $now && $banner->get('pub_date') < $now)) {
        if ($banner->get('published') == 0) {
            $banner->set('published', 1);
            $banner->save();
        }
        
        if (empty($banner->get('content'))) {
            $bannerOutput .= $modx->getChunk($bannerTpl, array_merge(['position' => $position], $banner->toArray()));
        } else {
            $bannerOutput .= $banner->get('content');
        }
        
        $position++;
    } else  {
        $banner->set('published', 0);
        $banner->save();
    }
}

if ($bannerOutput) {
    return $modx->getChunk($tplWrapper, [
        'banners' => $bannerOutput 
    ]);
}

return;
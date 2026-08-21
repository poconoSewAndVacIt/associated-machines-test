/**
 * Gets the photos for the current (or specified) mgResource.
 *
 * @author Mark Hamstra for modmore <support@modmore.com>
 * @var modX $modx
 * @var array $scriptProperties
 */


/** @var moreGallery $moreGallery */
$corePath = $modx->getOption('moregallery.core_path', null, $modx->getOption('core_path') . 'components/moregallery/');
$moreGallery = $modx->getService('moregallery', 'moreGallery', $corePath . 'model/moregallery/');
if (!($moreGallery instanceof moreGallery) || !($moreGallery instanceof \modmore\Alpacka\Alpacka)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Error loading moreGallery class from ' . $corePath);
    return 'Error loading moreGallery class.';
}

if (!method_exists($moreGallery, 'runSnippet')) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[moreGallery] IMPORTANT! An older version of Alpacka is being used. Alpacka is a shared library used by numerous extras from modmore. MoreGallery 1.5+ requires Alpacka 0.3+, but an earlier version is loaded into memory. To resolve this issue, please make sure all extras from modmore are up to date (especially MoreGallery itself, ContentBlocks and SimpleCart) and that the Alpacka package is also installed and up to date. If the Alpacka package does not appear in your packages grid, you can download it from the modmore.com package provider for free.');
    return '<span style="color: red; background: white;">Error: using an old version of Alpacka. Please see the error log for more information.</span>';
}

return $moreGallery->runSnippet('GetImages', $scriptProperties, false);
$tpl = $modx->resource->getOne('Template');

if ($tpl->get('templatename') === 'scr_podcast') {
    $modx->regClientStartupHTMLBlock('
        <link type="text/css" rel="stylesheet" href="/assets/css/sewcast/amplitude-player.css">
    ');
    
    $modx->regClientHTMLBlock('
        <script src="https://cdn.jsdelivr.net/npm/amplitudejs@3.3.1/dist/amplitude.min.js"></script>
    ');
}

$modx->regClientStartupHTMLBlock('
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/css/sewcast/flex-grid.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="/assets/css/sewcast/sewcast.css">
    <!-- <script defer src="https://cdnjs.cloudflare.com/ajax/libs/turbolinks/5.2.0/turbolinks.js"></script> -->
');

$modx->regClientHTMLBlock('
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
');
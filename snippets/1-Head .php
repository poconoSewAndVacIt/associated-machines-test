$assetVersion = $modx->getOption('psv.asset_version', null, time());
$tpl = $modx->resource->getOne('Template');
$templateName = $tpl->get('templatename');

if ($templateName === 'online_event') {
    $modx->regClientHTMLBlock('
        <script defer src="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.2.0/plyr.min.js"></script>
        <script defer src="/assets/js/player/player.js"></script>
    ');
    $modx->regClientStartupHTMLBlock('
        <link rel="preload" href="/assets/css/player/plyr.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
        <noscript><link rel="stylesheet" href="/assets/css/player/plyr.css"></noscript>
        
        <link rel="preload" href="/assets/css/player/player.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
        <noscript><link rel="stylesheet" href="/assets/css/player/player.css"></noscript>
    ');
}

if ($templateName === 'blog') {
    $modx->regClientHTMLBlock('
        <script id="dsq-count-scr" src="//poconosewandvac.disqus.com/count.js" async></script>
    ');
}

if ($templateName === 'search' || $templateName === 'search_category' || $templateName === 'machine_search_category') {
    $modx->regClientStartupHTMLBlock('
        <link rel="preload" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
        <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css"></noscript>
    ');
}

$modx->regClientStartupHTMLBlock('
    <link rel="dns-prefetch" href="https://embed.tawkto.to">
    <link rel="dns-prefetch" href="https://font.googleapis.com">
    <link rel="dns-prefetch" href="https://www.googleapis.com">
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
    <link rel="dns-prefetch" href="https://d10lpsik1i8c69.cloudfront.net">

    <!-- Async CSS -->
    <!-- <link rel="preload" href="/assets/css/foundation.min.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
    <noscript> --><link rel="stylesheet" href="/assets/css/foundation.min.css"><!-- </noscript> -->
    
    <!-- <link rel="preload" href="/assets/css/main.min.css?v='.$assetVersion.'" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
    <noscript> --><link rel="stylesheet" href="/assets/css/main.min.css?v='.$assetVersion.'"><!-- </noscript> -->
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,700,900&display=swap">
    <link rel="preload" href="/assets/css/fonts/slick.woff">
    
    <link rel="preload" href="/assets/css/lazyframe.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
    <noscript><link rel="stylesheet" href="/assets/css/lazyframe.css"></noscript>
    
    <link rel="preload" href="/assets/css/awesomplete.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
    <noscript><link rel="stylesheet" href="/assets/css/awesomplete.css"></noscript>
    
    <!--<link rel="preload" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
    <noscript>--><link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css"><!--</noscript>-->
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" as="style" onload="this.rel=\'stylesheet\'" onerror="this.rel=\'stylesheet\'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css"></noscript>
    
    <!-- loadCSS https://github.com/filamentgroup/loadCSS -->
    <script>
    !function(t){"use strict";t.loadCSS||(t.loadCSS=function(){});var e=loadCSS.relpreload={};if(e.support=function(){var e;try{e=t.document.createElement("link").relList.supports("preload")}catch(a){e=!1}return function(){return e}}(),e.bindMediaToggle=function(t){function e(){t.media=a}var a=t.media||"all";t.onload=null,t.addEventListener?t.addEventListener("load",e):t.attachEvent&&t.attachEvent("onload",e),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(e,3e3)},e.poly=function(){if(!e.support())for(var a=t.document.getElementsByTagName("link"),n=0;n<a.length;n++){var o=a[n];"preload"!==o.rel||"style"!==o.getAttribute("as")||o.getAttribute("data-loadcss")||(o.setAttribute("data-loadcss",!0),e.bindMediaToggle(o))}},!e.support()){e.poly();var a=t.setInterval(e.poly,500);t.addEventListener?t.addEventListener("load",function(){e.poly(),t.clearInterval(a)}):t.attachEvent&&t.attachEvent("onload",function(){e.poly(),t.clearInterval(a)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:t.loadCSS=loadCSS}("undefined"!=typeof global?global:this);
        
        if("ontouchstart" in document.documentElement)
            document.addEventListener(\'touchstart\', ontouchstart, {passive: true});
    </script>
    
');

$modx->regClientHTMLBlock('

    <!-- Javascript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script defer src="/assets/js/js.cookie.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.1/js/lightbox.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/lazyframe@1.1.6/dist/lazyframe.min.js"></script>
    <script src="/assets/js/awesomplete.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.6/jquery.lazy.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
    
    <script async>var lazy = $(".lazy").Lazy({
        scrollDirection: "vertical",
        effect: "fadeIn",
        effectTime: 100,
        threshold: 300,
        autoDestroy: false,
        visibleOnly: true,
        chainable: false,
        placeholder: "https://cdn.poconosewandvac.com/web/assets/site/img/loadingcircle.svg",
        onError: function(element) {
            console.log("Error loading " + element.data("src"));
        }
    });</script>
    
    <script type="text/javascript" async>
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src=\'https://embed.tawk.to/5a0d9d61198bd56b8c03b89c/default\';
    s1.charset=\'UTF-8\';
    s1.setAttribute(\'crossorigin\',\'*\');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>

    <!--<script defer async src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.8.1/parsley.min.js"></script>-->
    <!-- IE11 -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.6.15/browser-polyfill.min.js"></script>
    <script defer src="/assets/js/main.min.js?v='.$assetVersion.'"></script>
');

if ($templateName === 'search' || $templateName === 'search_category' || $templateName === 'machine_search_category' || $templateName === 'fabric_search_category') {
    $modx->regClientHTMLBlock('
        <script defer src="/assets/js/search.js"></script>
    ');
}

if ($templateName === 'product' || $templateName === 'event') {
    $modx->regClientHTMLBlock('
        <script defer async src="https://platform-api.sharethis.com/js/sharethis.js#property=59cb8fb2cf00260012153db7&product=inline-share-buttons"></script>
    ');
}

// Handle Calendar (don't load on non-calendar pages, its a heavy script.
// TODO: Move to psv.js 
$calChk = $modx->resource->getTVValue('Page Class Name');
if (strpos($calChk, 'calendar') !== false) {
    $modx->regClientCSS('https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css');
    $modx->regClientScript('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js');
    $modx->regClientScript('https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js');
    $modx->regClientHTMLBlock('
    <script>
        $(document).ready(function() {
        $("#calendar").fullCalendar({
            header: {
                left:"title",
                center:"agendaWeek,listMonth,month",
                right:"today prev,next"
            },
            height: "auto",
            events:"/assets/api/cal.json"
        });
    });
    </script>

    ');
}
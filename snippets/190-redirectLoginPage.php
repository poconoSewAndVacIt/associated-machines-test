if ($_GET['service'] !== 'logout' && $modx->user->hasSessionContext($modx->context->get('key'))) {
    // id 53 is account page.
    $url = $modx->makeUrl(53);
    $modx->sendRedirect($url);
}
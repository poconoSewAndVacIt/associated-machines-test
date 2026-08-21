$defaultLimit = 24;
$maxLimit = 96;
$limit = $modx->getOption('limit', $_REQUEST, $defaultLimit);
$cookie = intval($modx->getOption('search_limit', $_COOKIE, 0));

// Don't allow over 96 results per page
if ($limit > $maxLimit) {
    $limit = $maxLimit;
}

// Use user's preferred search limit if set
if ($cookie > 0 && $cookie <= $maxLimit) {
    $limit = $cookie;
}

// Set the user's cookie
if ($limit > 0 && $limit !== $defaultLimit) {
    setcookie('search_limit', $limit, time() + 31556926); // 1 year
}

$modx->setPlaceholder('searchLimit', $limit);
return $limit;
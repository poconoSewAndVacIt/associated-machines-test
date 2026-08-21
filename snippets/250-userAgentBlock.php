$ua = $_SERVER['HTTP_USER_AGENT'];

$blockedAgents = [
    'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:44.0)', // scam orders beginning of April
    //'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
    'Mozilla/5.0 (Linux x86_64; rv:115.0) Gecko/20100101 Firefox/115.0',
];

foreach ($blockedAgents as $blocked) {
    if (strpos($ua, $blocked) !== false) {
        $message = '[userAgentBlock] User attempted to use a restricted user agent\n\n';
        $message .= print_r($_SERVER, true);
        $modx->log(1, $message);
        
        $modx->sendErrorPage();
        
        break;
    }
    
}
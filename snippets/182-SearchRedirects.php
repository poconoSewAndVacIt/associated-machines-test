// Check if search query is set
if (!isset($_REQUEST['query'])) {
    return;   
}

// Trim whitespace
$search = trim($_REQUEST['query']);

// Common stop words
$search = strtolower(str_replace([' for ', ' the '], ' ', $search));

// Abbreviations
$search = str_replace('-', '', $search);

// limit search query to 50 characters to prevent long spam queries
$search = (strlen($search) > 50) ? substr($search, 0, 50) : $search;

// Set the query. The request is used by the search itself in subsequent requests
$_REQUEST['query'] = $search;

// Add the package
$searchPkg = $modx->addPackage('searchredirects', MODX_CORE_PATH.'components/searchredirects/model/');
if (!$searchPkg) {
    return "Failed to add package";
}
    
// Build the query for redirects first
$query = $modx->getObject('searchRedirects', array(
    'Search_Term' => $search,
    'deleted' => 0,
    'published' => 1
));

if (!$query) {
    $modx->setPlaceholder('ReplacedSearchQuery', $search);
    return;
}

$searchType = $query->get('Search_Type');
$searchTerm = trim($query->get('Search_Term'));

switch ($searchType) {
    case 'redirect':
        if (strtolower($search) === strtolower($searchTerm)) {
            $location = $query->get('Redirect_To');
            
            if (is_numeric($location)) {
                $url = $modx->makeUrl($query->get('Redirect_To'));
                $modx->sendRedirect($url);
            }
            
            $modx->sendRedirect($location);
        }
        
        break;
        
    case 'replacement':
        $replaced = str_replace($query->get('Search_Term'), $query->get('Replacement_Term'), $search);
        $modx->setPlaceholders([
            'OriginalSearchQuery'=> htmlentities($search),
            'ReplacedSearchQuery'=> str_replace([' for ', ' the '], ' ', $replaced),
            'ReplacedSearchQueryText'=>$query->get('Search_Text')
        ]);
        
        break;
}
// Check if the user can even view this information
if (!$modx->user->isMember(['Database Viewer', 'Site Editor', 'Administrator'])) {
    if (!$modx->user->hasSessionContext('mgr')) {
        return;
    }
}

$id = $modx->getOption('id', $scriptProperties, $modx->resource->get('id'));
$tpl = $modx->getOption('tpl', $scriptProperties, 'AccessDatabaseResult');

// empty() does not work here? Returns nothing if resource is a machine, since machines aren't in Access. Saves on performance.
$pg = $modx->getObject('modResource', $id);
if (strlen($pg->getTVValue('Associated Products on Sidebar'))) {
    return 'Machines are not in access.';
}

// Connect to the database
try {
    $host = $modx->getOption('psv.access_host');
    $port = $modx->getOption('psv.access_port');
    $database = $modx->getOption('psv.access_db');
    $username = $modx->getOption('psv.access_username');
    $password = $modx->getOption('psv.access_password');
    
    $dsn = "mysql:host=$host;dbname=$database;port=$port;charset=utf8";
    $access = new PDO($dsn, $username, $password, [PDO::ATTR_TIMEOUT => 10]);
    $access->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
    $modx->log(1, '[AccessDatabase] Could not connect to psvdb database! ' . $e->getMessage());
    return 'Could not connect to psvdb database! Error: ' . $e->getMessage();
}

// Get product variations for the resource
$variations = $modx->getCollection('ProductVariation', [
    'resource_id' => $id,
]);
        
$items = [];
foreach ($variations as $variation) {
    // Only get once instance of a record
    $ids = $variation->get('psvdb_ids');
    
    // Empty check is needed or else it will get stuck
    if (!in_array($ids, $items) && !empty($ids)) {
        $items[] = $ids;
    }
}

$department = $pg->getTVValue('storeDepartment');
if (stripos($department, 'vac') !== false) {
    $tableName = 'VAC PARTS';
} else {
    $tableName = 'SEW PARTS';
}
  
$output = '';
foreach ($items as $item) {
    // Sets up the actual query
    try {
        $query = $access->prepare("SELECT * FROM `$tableName` WHERE `REC #` = :recnum");
        $query->execute([':recnum' => $item]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return false;
    }
        
    if($query->rowCount() > 0) {
        // Loop through results and output them one by one. This is for if we have multi-variations with different part numbers all in our database
        // @deprecated 4/6/2019
        foreach ($results as $result) {
            $output .= $modx->getChunk($tpl, $result);
        }
    }
}
    
return $output;
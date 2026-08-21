$userResources = $modx->getUser()
    ->getOne('Profile')
    ->get('extended')["resource_store"];
    
return implode(",", $userResources);
if (!$modx->user->isMember(['Database Viewer', 'Site Editor', 'Administrator'])) {
    if ($modx->user->hasSessionContext('mgr')) {
        return 1;
    }
    
    return 0;
}

return 1;
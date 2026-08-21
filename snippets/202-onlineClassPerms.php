// Snippet options
$playlistTpl = $modx->getOption('playlistTpl', $scriptProperties, 'classPlaylistTpl');
$playlistOuterTpl = $modx->getOption('playlistOuterTpl', $scriptProperties, 'classOuterPlaylistTpl');
$videoTpl = $modx->getOption('videoTpl', $scriptProperties, 'classVideoTpl');

// Add the online class video package
$pkg = $modx->addPackage('onlineclassvideo', MODX_CORE_PATH.'components/onlineclassvideo/model/');
if (!$pkg) {
    return "Failed to add online class package";
}

//if ($modx->user->hasSessionContext($modx->context->get('key'))) {
    $videos = $modx->getCollection('onlineClassVideo', array(
         'resource_id' => $modx->resource->get('id')
    ));

    if ($videos) {
        $allowed = $extra = $allowedGroups = [];
        $output = $rows = "";
        
        $i = 0;
        $idx = 1;
        $videosCount = count($videos);
        foreach ($videos as $video) {
            $userGroups = array_map('trim', explode("||", $video->get("user_group")));
            foreach ($userGroups as $userGroup) {
                // If user has permission, output video
                if ($modx->user->isMember($userGroup) || $userGroup === "(anonymous)") {
                    
                    // Get Source
                    if (strpos($video->get("url"), "youtube.com") !== false) {
                        $extra["youtube"] = 1;
                        $extra["service"] = "youtube";
                    } else if (strpos($video->get("url"), "vimeo.com") !== false) {
                        $extra["vimeo"] = 1;
                        $extra["service"] = "vimeo";
                    }
                    
                    $extra["idx"] = $idx;
                    $rows .= $modx->getChunk($playlistTpl, array_merge($video->toArray(), $extra));
                    if ($i === 0) {
                        $modx->setPlaceholder("classVideo", $modx->getChunk($videoTpl, array_merge(array('url' => $video->get("url")), $extra)));
                        $i++;
                    }
                        
                    // Add a customer group to Personalize placeholder
                    $allowedGroups[] = $userGroup;
                    $idx++;
                }
            }
        }

        // Set placeholders
        $modx->setPlaceholder("classPlaylist", $modx->getChunk($playlistOuterTpl, array('rows' => $rows)));
        $modx->setPlaceholder("classVideoCount", $videosCount);
        $modx->setPlaceholder("allowedGroups", implode(",", array_unique($allowedGroups)));
    }
//}
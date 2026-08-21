$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);

if ($modx->user->isAuthenticated('web')) {
    $profile = $modx->user->getOne('Profile');
    if (!$profile) {
        return;
    }
    
    $email = json_encode($profile->get('email') ?? '');
    $fullname = json_encode($profile->get('fullname') ?? '');
    
    return <<<EOF
<script>
  var customData = {
    name: $fullname,
    email: $email
  }

  window._loq = window._loq || []    
  window._loq.push(['custom', customData])
</script>
EOF;
} else {
    $order = \comOrder::loadUserOrder($commerce);
    // Nothing to go off of for guest users besides the order
    if (!$order) {
        return;
    }
    
    $address = $order->getBillingAddress();
    if (!$address) {
        return;
    }
    
    $email = json_encode($address->get('email') ?? '');
    $fullname = json_encode($address->get('fullname') ?? '');
    
    return <<<EOF
<script>
  var customData = {
    name: $fullname,
    email: $email
  }

  window._loq = window._loq || []    
  window._loq.push(['custom', customData])
</script>
EOF;
}
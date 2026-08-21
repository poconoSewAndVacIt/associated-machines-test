// Only run if the customer currently has machines
if (!isset($_COOKIE['machines'])) {
    return true;
}

$cookieValue = $_COOKIE['machines'];
$machines = json_decode($cookieValue, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // while this is *technically* a failure, we don't want to a block a customer from registering for an account
    // however we do want to verify that what we put in an extended profile field will not cause errors
    return true;
}

$profile = $hook->getValue('register.profile');
$extended = $profile->get('extended');
$extended['machines'] = json_encode($machines);
$profile->set('extended', $extended);

$profile->save();

return true;
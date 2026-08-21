$email = $hook->getValue('emailaddress');
$emailDomain = substr(strstr($email, '@'), 1);

if (strpos(strtolower($emailDomain), 'yandex') !== false || strpos(strtolower($emailDomain), 'domainworld') !== false)  {
    $hook->addError('emailaddress', 'Invalid email address.');
    return false;
}

$badEmails = [
    'jenny@seopackagesprice.com',
    'leslie@seopackagesprice.com',
    'dennis@qualitybloggeroutreach.com',
    'info@domainregistration.com',
    'info@domainregistrationcorp.com',
];

if (in_array($email, $badEmails)) {
    $hook->addError('emailaddress', 'Invalid email address.');
    return false;
}

return true;
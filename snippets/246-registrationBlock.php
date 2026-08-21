$email = $hook->getValue('email');
$domain = substr(strrchr($email, "@"), 1);

$blockedDomains = [
    'att.net',
];

if (in_array($domain, $blockedDomains)) {
    // Build the email
    $message = '<h1>User tried to sign up with a restricted email domain: '.$hook->getValue('email').'.</h1><br><h2>SERVER DUMP</h2><br><pre>';
    $message .= print_r($_SERVER, true);
    $message .= '</pre>';
    
    // Construct the email
    $modx->getService('mail', 'mail.modPHPMailer');
    $modx->mail->set(modMail::MAIL_BODY, $message);
    $modx->mail->set(modMail::MAIL_FROM,'website@poconosewandvac.com');
    $modx->mail->set(modMail::MAIL_FROM_NAME, 'Pocono Sew & Vac Robot');
    $modx->mail->set(modMail::MAIL_SUBJECT, 'Restricted Email Signup Attempt');
    $modx->mail->setHTML(true);
    $modx->mail->address('to','it@poconosewandvac.com');
    if (!$modx->mail->send()) {
        $modx->log(modX::LOG_LEVEL_ERROR,'An error occurred while trying to send the email: '.$err);
    }
    $modx->mail->reset();
    
    $hook->addError('email', 'Entered email address not allowed!');
    return false;
}

return true;
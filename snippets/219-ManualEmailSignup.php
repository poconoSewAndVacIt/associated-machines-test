if ($hook->getValue('email_signup')) {
    $ctct = $modx->getService('psvconstantcontact','PsvConstantContact', MODX_CORE_PATH . 'components/psvconstantcontact/model/psvconstantcontact/');
    if (!($ctct instanceof PsvConstantContact)) return '';
    
    $email = $hook->getValue('emailaddress');
    
    $update = $ctct->addContact(['email_address' => $email], ['1002722974']);
}

return true;
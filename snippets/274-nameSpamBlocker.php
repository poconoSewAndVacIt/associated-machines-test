$firstName = $hook->getValue('fname');
$lastName = $hook->getValue('lname');

if ($firstName === $lastName) {
    $hook->addError('fname', 'Invalid name');
    return false;
}

return true;
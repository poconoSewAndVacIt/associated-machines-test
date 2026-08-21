$tpl = $modx->getOption("tpl", $scriptProperties, "editCCEmail");
$contact = $modx->getOption("contact", $_REQUEST, null);
$couponProductTypes = $modx->getOption("couponProductTypes", $scriptProperties, "bobbins-bobbin-threads,needles");
// see docs for generation
$couponProducts = [24,25,26,27,23,22,21,497,498,1380,516,593,594,595,596,597,598,599,600,601,602,603,604,789,807,868,870,876,877,878,879,880,881,911,912,913,914,915,988,989,990,1002,1218,1379,1381,1382,1383,1384,1385,1478,1482,1483,1484,1485,1486,1487,1488,1489,1492,1493,1495,1494,1496,1497,1498,1499,1500,1501,1502,1503,1504,1505,1506,1507,1508,1509,1510,1511,1512,1513,1514,1515,1516,1535,1537,1538,1539,1540,1541,1542,1543,1544,1545,1546,1547,1548,1549,1550,1551,1552,1553,1554,1555,1556,1557,1558,1559,1560,1561,1562,1563,1564,1565,1566,1567,1568,1570,1571,1572,1573,1574,1575,1576,1577,1578,1579,1580,1581,1582,1583,1584,1585,1586,1587,1588,1589,1590,1591,1592,1593,1594,1595,1596,1597,1598,1599,1600,1601,1602,1603,1604,1605,1606,1607,1611,1612,1613,1614,1615,1616,1617,1618,1619,1620,1621,1622,1623,1624,1625,1654,1656,1659,1662,1664,1666,1667,1669,1673,1675,1676,1677,1681,1691,1692,1693,1695,1698,1754,1755,1756,1757,1758,1759,1761,1762,1763,1765,1766,1770,1771,4182,4183,4184,4185];

// Redirect to add email if email not set.
if (empty($_REQUEST["email"])) {
    $modx->sendRedirect($modx->makeUrl(13));
}

// Load Constant Contact API
$ctct = $modx->getService('psvconstantcontact','PsvConstantContact', MODX_CORE_PATH . 'components/psvconstantcontact/model/psvconstantcontact/');
if (!($ctct instanceof PsvConstantContact)) return '';

if ($contact > 0 && $_REQUEST["type"] === "edit") {
    $update = $ctct->updateContact($contact, $_REQUEST["profile"], $_REQUEST["lists"]);
    
    if ($update) {
        $commercePath = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path') . 'components/commerce/') . 'model/commerce/';
        $commerce = $modx->getService('commerce', 'Commerce', $commercePath, ['mode' => $modx->getOption('commerce.mode')]);
        
        $couponQuery = $modx->getObject('comCoupon', [
            'code' => 'EMAIL25-' . $contact
        ]);
        
        if (!$couponQuery) {
            // Add the coupon
            $coupon = $modx->newObject('comCoupon', [
                'code' => 'EMAIL25-' . $contact,
                'max_uses' => 1,
                'discount_percentage' => 25.0000,
                'available_until' => time() + 2592000, // 30 days
                'products' => implode(",", $couponProducts)
            ]);
            $coupon->save();
            $couponPlaceholders = $coupon->toArray();
            
            // Email the coupon
            $modx->getService('mail','mail.modPHPMailer');
            $modx->mail->set(modMail::MAIL_BODY, $modx->getChunk("couponCCEmail", $couponPlaceholders));
            $modx->mail->set(modMail::MAIL_FROM, 'info@poconosewandvac.com');
            $modx->mail->set(modMail::MAIL_FROM_NAME, 'Pocono Sew & Vac');
            $modx->mail->set(modMail::MAIL_SUBJECT, 'Thanks for updating your email profile, here is your coupon!');
            $modx->mail->address('to', $_REQUEST["email"]);
            $modx->mail->setHTML(true);
            if (!$modx->mail->send()) {
                $modx->log(modX::LOG_LEVEL_ERROR,'An error occurred while trying to send the email in update email profile: '.$modx->mail->mailer->ErrorInfo);
            }
            $modx->mail->reset();            

            return $modx->getChunk("thanksCCEmail", $couponPlaceholders);
        }
    }
    
    // Redirect to new email if user changed it
    $modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', ["email" => htmlspecialchars($_REQUEST["profile"]["email_address"])]));
}

$contact = $ctct->cc->contactService->getContacts($ctct->getAccessToken(), [
    'email' => $_REQUEST["email"],
    'limit' => 1
])->results;

// Redirect to add email if email not found.
if (!$contact) {
    $modx->sendRedirect($modx->makeUrl(13));
}

// Placeholders
$email = (array) $contact[0]->email_addresses[0];
$emailPlaceholders = array_combine(array_map(function($k){ return 'email_address.'.$k; }, array_keys($email)), $email);

$lists = (array) $contact[0]->lists;
foreach ($lists as $list) {
    if ($list->status === "ACTIVE") {
        $listPlaceholders['list.' . $list->id] = 1;
    }
}

$placeholders = array_merge((array) $contact[0], (array) $emailPlaceholders, (array) $listPlaceholders);

return $modx->getChunk($tpl, $placeholders);
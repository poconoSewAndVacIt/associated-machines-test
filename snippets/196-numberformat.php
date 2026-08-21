$number = floatval($input);
$optionsXpld = @explode('&', $options);
$optionsArray = array();
foreach ($optionsXpld as $xpld) {
    $params = @explode('=', $xpld);
    $params = array_map('trim', $params);
    if (isset($params[1])) {
        $optionsArray[$params[0]] = $params[1];
    } else {
        $optionsArray[$params[0]] = '';
    }
}
$decimals = isset($optionsArray['decimals']) ? $optionsArray['decimals'] : null;
$dec_point = isset($optionsArray['dec_point']) ? $optionsArray['dec_point'] : null;
$thousands_sep = isset($optionsArray['thousands_sep']) ? $optionsArray['thousands_sep'] : null;
$output = number_format($number, $decimals, $dec_point, $thousands_sep);
return $output;
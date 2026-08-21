// set IP address and API access key 
$ip = $_SERVER['REMOTE_ADDR'];
$access_key = '8797866db7d0d75e8cfa842b75f13fe9';

// Initialize CURL:
$ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$api_result = json_decode($json, true);

// Restrict to United States 48 continental & DC.
$allowedStates = ["AL", "AZ", "AR", "CA", "CO", "CT", "DE", "FL", "GA", "ID", "IL", "IN", "IA", "KS", "KY", "LA", "ME", "MD", "MA", "MI", "MN", "MS", "MO", "MT", "NE", "NV", "NH", "NJ", "NM", "NY", "NC", "ND", "OH", "OK", "OR", "PA", "RI", "SC", "SD", "TN", "TX", "UT", "VT", "VA", "WA", "WV", "WI", "WY", "DC"];

if ($api_result["country_code"] === "US" && in_array($api_result["region_code"], $allowedStates)) {
    // Output the "capital" object inside "location"
    return $modx->getChunk('ipLocator', $api_result);
}
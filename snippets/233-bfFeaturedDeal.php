$time = date('W', strtotime('tomorrow')); // php weeks start on Monday, so offset it 1 day

$output = 1;
switch ($time) {
    case 1:
        $output = 2093; // Daylight YoYo
        break;
    case 2:
        $output = 923; // Brother Free-Motion Guide Grip
        break;
    case 3:
        $output = 2093; // Daylight YoYo
        break;
    case 4:
        $output = 2934; // Ultra sharp 5 1/2" scissors
        break;
    case 5:
        $output = 1977; // Vac-yum
        break;
    case 6:
        $output = 1977; // Vac-yum
        break;
    case 7:
        $output = 2880; // Daylight wafer 1 cutting mat
        break;
    case 8:
        $output = 2880; // Daylight wafer 1 cutting mat
        break;
    case 8:
        $output = 2880; // Daylight wafer 1 cutting mat
        break;
        
    case 10:
        $output = 2880; // Daylight wafer 1 cutting mat
        break;
        
    case 20:
        $output = 3016; // Brother Dream Machine HAXVBOOK
        break;
        
    case 35:
        $output = 841; // Brother 2500+ Embroidery Design Collection SAEMB2500
        break;
}

return $output;
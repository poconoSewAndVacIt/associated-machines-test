/**
* POCONOSEWANDVAC.COM
* Handle calendar list requests from fullcalendar js frontend
*/
header('Content-Type: application/json');
date_default_timezone_set('America/New_York');

class Event {

	// Tests whether the given ISO8601 string has a time-of-day or not
	const ALL_DAY_REGEX = '/^\d{4}-\d\d-\d\d$/'; // matches strings like "2013-12-29"

	public $title;
	public $allDay; // a boolean
	public $start; // a DateTime
	public $end; // a DateTime, or null
	public $properties = array(); // an array of other misc properties


	// Constructs an Event object from the given array of key=>values.
	// You can optionally force the timezone of the parsed dates.
	public function __construct($array, $timezone=null) {

		$this->title = $array['title'];

		if (isset($array['allDay'])) {
			// allDay has been explicitly specified
			$this->allDay = (bool)$array['allDay'];
		}
		else {
			// Guess allDay based off of ISO8601 date strings
			$this->allDay = preg_match(self::ALL_DAY_REGEX, $array['start']) &&
				(!isset($array['end']) || preg_match(self::ALL_DAY_REGEX, $array['end']));
		}

		if ($this->allDay) {
			// If dates are allDay, we want to parse them in UTC to avoid DST issues.
			$timezone = null;
		}

		// Parse dates
		$this->start = parseDateTime($array['start'], $timezone);
		$this->end = isset($array['end']) ? parseDateTime($array['end'], $timezone) : null;

		// Record misc properties
		foreach ($array as $name => $value) {
			if (!in_array($name, array('title', 'allDay', 'start', 'end'))) {
				$this->properties[$name] = $value;
			}
		}
	}


	// Returns whether the date range of our event intersects with the given all-day range.
	// $rangeStart and $rangeEnd are assumed to be dates in UTC with 00:00:00 time.
	public function isWithinDayRange($rangeStart, $rangeEnd) {

		// Normalize our event's dates for comparison with the all-day range.
		$eventStart = stripTime($this->start);

		if (isset($this->end)) {
			$eventEnd = stripTime($this->end); // normalize
		}
		else {
			$eventEnd = $eventStart; // consider this a zero-duration event
		}

		// Check if the two whole-day ranges intersect.
		return $eventStart < $rangeEnd && $eventEnd >= $rangeStart;
	}


	// Converts this Event object back to a plain data array, to be used for generating JSON
	public function toArray() {

		// Start with the misc properties (don't worry, PHP won't affect the original array)
		$array = $this->properties;

		$array['title'] = $this->title;

		// Figure out the date format. This essentially encodes allDay into the date string.
		if ($this->allDay) {
			$format = 'Y-m-d'; // output like "2013-12-29"
		}
		else {
			$format = 'c'; // full ISO8601 output, like "2013-12-29T09:00:00+08:00"
		}

		// Serialize dates into strings
		$array['start'] = $this->start->format($format);
		if (isset($this->end)) {
			$array['end'] = $this->end->format($format);
		}

		return $array;
	}

}


// Date Utilities
//----------------------------------------------------------------------------------------------


// Parses a string into a DateTime object, optionally forced into the given timezone.
function parseDateTime($string, $timezone=null) {
	$date = new DateTime(
		$string,
		$timezone ? $timezone : new DateTimeZone('UTC')
			// Used only when the string is ambiguous.
			// Ignored if string has a timezone offset in it.
	);
	if ($timezone) {
		// If our timezone was ignored above, force it.
		$date->setTimezone($timezone);
	}
	return $date;
}


// Takes the year/month/date values of the given DateTime and converts them to a new DateTime,
// but in UTC.
function stripTime($datetime) {
	return new DateTime($datetime->format('Y-m-d'));
}
// Short-circuit if the client did not give us a date range.
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    $errormsg = array(array("ERROR"=>"No date range specified."));
    $errormsg = json_encode($errormsg);
    die($errormsg);
}
// Parse the start/end parameters.
// These are assumed to be ISO8601 strings with no time nor timezone, like "2013-12-29".
// Since no timezone will be present, they will parsed as UTC.
$range_start = parseDateTime($_GET['start']);
$range_end = parseDateTime($_GET['end']);

// Parse the timezone parameter if it is present.
$timezone = null;
if (isset($_GET['timezone'])) {
    $timezone = new DateTimeZone($_GET['timezone']);
}

// Read and parse our events JSON file into an array of event data arrays.
//$json = file_get_contents(dirname(__FILE__) . '/../json/events.json');
$input_arrays = json_decode($json, true);

$raw_array = $modx->runSnippet('getAssociatedProducts', array(
    'includeTVs'=>'1',
    'debug'=>'1',
    'parents'=>'40',
    'limit'=>'0',
    'tvPrefix'=>'',
    'tvFilters'=>'Is Event==%Event Selling Page%,Is Event!=%Hide From Calendar%',
    'where'=>$modx->runSnippet('TaggerGetResourcesWhere', array(
        'groups'=>"5",
        'where'=>"{'isfolder': 0}",
    )))
);
$formatted_array = array();
foreach ($raw_array as $value) {
    $times = json_decode($value['Event Times'], true);

    if(!empty($times)) {
        foreach ($times as $val) {
            $color = $val['color'];
            if (strlen($color) > 0 && $color[0] === '#') {
                $color = '#' . ltrim($color, '#');
            } else {
                $color = '#33601c';
            }
            
            $data = array(
                'title'=>$val['timeName'],
                'start'=>date('c', strtotime($val['startTime'])),
                'end'=>date('c', strtotime($val['endTime'])),
                'color'=> $color,
                'url'=>$value['uri']
            );
            $formatted_array[] = $data;
        }
    }

}

// Accumulate an output array of event data arrays.
$output_arrays = array();
foreach ($formatted_array as $array) {
    // Convert the input array into a useful Event object
    $event = new Event($array, $timezone);

    // If the event is in-bounds, add it to the output
    if ($event->isWithinDayRange($range_start, $range_end)) {
        $output_arrays[] = $event->toArray();
    }
}

// Send JSON to the client.
echo json_encode($output_arrays);
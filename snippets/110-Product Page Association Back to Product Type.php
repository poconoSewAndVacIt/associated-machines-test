/**
* POCONOSEWANDVAC.COM
* This snippet outputs the ptref value in the URL for linking back to machine product types.
*/
if (isset($_GET['ptref']) && $_GET['ptref'] != '') {
    $output = htmlentities($_GET['ptref']);
    return $output;
}
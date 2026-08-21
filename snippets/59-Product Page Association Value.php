/**
* POCONOSEWANDVAC.COM
* This snippet outputs a 1 if the associated parameter is set and has a value.
*/
if (isset($_GET['associated']) && $_GET['associated'] != '') {
    return 1;
}
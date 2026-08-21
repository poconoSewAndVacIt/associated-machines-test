if (isset($_GET['associated']) && $_GET['associated'] != '') {
    return '?associated='.htmlentities($_GET['associated']);
} else {
    return;
}
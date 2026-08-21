/**
* POCONOSEWANDVAC.COM
* Returns the search query.
*/
if(isset($_GET['query']) && $_GET['query'] != '') {
    return htmlentities($_GET['query']);
}
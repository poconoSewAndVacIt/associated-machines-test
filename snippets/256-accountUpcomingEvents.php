$tpl = $modx->getOption('tpl', $scriptProperties, 'accountUpcomingEvents');
$wrapTpl = $modx->getOption('tpl', $scriptProperties, 'accountUpcomingEventsWrap');
$user = $modx->getOption('user', $scriptProperties, $modx->user->get('id'));

if ($user === 0) return;

$commercePath = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path').'components/commerce/') . 'model/commerce/';
$commerce = $modx->getService('commerce', 'Commerce', $commercePath, ['mode' => $modx->getOption('commerce.mode')]);

$q = $modx->newQuery('comOrderItem');
$q->select('comOrderItem.*, o.id AS order_id, o.reference AS order_reference, o.received_on AS order_received, p.resource_id, ppi.value AS ppi, ppis.value AS ppis, suplist.value AS suplist, time.value AS time');
$q->innerJoin('ProductVariation', 'p', ['comOrderItem.product = p.id']);
$q->innerJoin('modResource', 'r', ['r.id = p.resource_id']);
$q->innerJoin('comOrder', 'o', ['o.id = comOrderItem.order']);

// Get image TVs
$q->leftJoin('modTemplateVarResource', 'ppi', ['ppi.contentid = r.id AND ppi.tmplvarid = 4']); // primary product image
$q->leftJoin('modTemplateVarResource', 'ppis', ['ppis.contentid = r.id AND ppis.tmplvarid = 35']); // primary product image small
$q->leftJoin('modTemplateVarResource', 'suplist', ['suplist.contentid = r.id AND suplist.tmplvarid = 63']); // supply list
$q->leftJoin('modTemplateVarResource', 'time', ['time.contentid = r.id AND time.tmplvarid = 22']); // event times

$q->where([
    'o.class_key:!=' => 'comCartOrder',
    'o.class_key:!=' => 'comCancelledOrder',
    'o.user' => $user,
    'o.test' => $commerce->getMode() === \Commerce::MODE_TEST ? true : false,
    'p.type' => 'Event',
    'r.deleted' => false,
    'r.published' => true
]);

$q->sortby('r.unpub_date', 'ASC');

$events = $modx->getCollection('comOrderItem', $q);
$eventsCount = $modx->getCount('comOrderItem', $q);
if (!$items) $modx->getChunk($wrapTpl, ['count' => 0]);

$output = '';
foreach ($events as $event) {
    $output .= $modx->getChunk($tpl, $event->toArray());
}

return $modx->getChunk($wrapTpl, ['output' => $output, 'count' => $eventsCount]);
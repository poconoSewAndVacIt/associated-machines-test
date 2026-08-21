/**
* POCONOSEWANDVAC.COM
* This snippet structures the pricing for events.
*
* @author Tony Klapatch <tonyklapatch@hotmail.com>
*/
if(!class_exists('eventStructure')) {    
    class eventStructure {
        public $resourceId;
        public $productInfo;

        public function fetchVariations() {
            global $modx;
            $this->resourceId = $modx->resource->get('id');
            $output = array();
            $query = $modx->query("SELECT * FROM product_variation WHERE resource_id=".$this->resourceId." AND deleted=0 AND published=1 AND type = 'Event'");
            if ($query) {
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    array_push($output, $row);
                }
            }
            $this->productInfo = $output;
            return $this->productInfo;
        }
    }
}


if (!function_exists('formatpricing')) {
    function formatpricing($modx) {
        // Fetch all event variations
        $pS = new eventStructure();
        $variations = $pS->fetchVariations();

        // Output first Result for pricing
        $fretail = $variations[0]['regular_retail'];
        $fsale = $variations[0]['regular_sale'];
        if (!empty($fretail) && $variations[0]['stock'] > '0') {
            if (empty($fsale)) {
                $output = '<p class="prodpricingretail">Price: <span itemprop="price">'.$fretail.'</span></p>';
            }
                if (!empty($fsale)) {
                $output = '<p class="prodpricingretail strike">Price: <span>'.$fretail.'</span></p><p class="prodpricingsale sale">Sale: <span itemprop="price">'.$fsale.'</span></p>';
            }
            echo $output;
        }
    }
}

if (!function_exists('addtocart')) {
    function addtocart($modx) {
        $pS = new eventStructure();
        $variations = $pS->fetchVariations();
        $fretail = $variations[0]['regular_retail'];
        if (!empty($fretail) && $variations[0]['stock'] > '0') {
            echo '[[$Event Pricing Structure Add to Cart]]';
        }
    }
}

if (!function_exists('getvariations')) {
    function getvariations($modx) {
        global $outvarlist;
        // Loop through each value to turn it into a select for multi variation items
        $pS = new eventStructure();
        $variations = $pS->fetchVariations();

        $output = $outvarlist = '';
        $i = 0;
        foreach ($variations as $varinfo) {
            $pushedvalues = array(
                'Regular Retail'=> $varinfo['regular_retail'],
                'Regular Sale'=> $varinfo['regular_sale'],
                'Class Number'=> $varinfo['sku'],
                'Variation Name'=> $varinfo['name'],
                'Variation Description'=> $varinfo['description'],
                'id'=> $varinfo['id']
            );
            if (empty($varinfo["regular_sale"])) {
                $pushedvalues += ['Price' => $varinfo['regular_retail']];
            } else if (!empty($varinfo["regular_sale"])) {
                $pushedvalues += ['Price' => $varinfo['regular_sale']];
            }
            if ($i === 0 && $varinfo['stock'] > '0' && $varinfo['regular_retail'] != '') {
                $output .= $modx->getChunk('Event Variation Output', $pushedvalues);
                $outputlist .= $modx->getChunk('Event Variation Output List First', $pushedvalues);      
            } else if ($varinfo['stock'] > '0' && $varinfo['regular_retail'] != '') {
                $output .= $modx->getChunk('Event Variation Output', $pushedvalues);
                $outputlist .= $modx->getChunk('Event Variation Output List', $pushedvalues);
            }
            $i++;
        }
        if ($i > 1) {
            $outvarlist = '<select class="productvariations">'.$outputlist.'</select>';
        }
        echo '<div class="cartbtns">'.$output.'</div>';
    }
}
if (!function_exists('getvariationlisting')) {
    function getvariationlisting($modx) {
        global $outvarlist;
        echo $outvarlist;        
    }
}
$fn = $modx->getOption('fn', $scriptProperties, '');
if (empty($fn)) {
    return 'No function was specified.';
}
call_user_func($fn, $modx);
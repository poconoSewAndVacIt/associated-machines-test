$type = $modx->getOption('type', $scriptProperties, 'Product');

$title = $modx->resource->get('longtitle') ? $modx->resource->get('longtitle') : $modx->resource->get('pagetitle');
$schema = [
    '@context' => 'http://schema.org',
    '@type' => 'Product',
    'description' => $modx->resource->getTVValue('SEO Description'),
    'name' => $title,
    'image' => $modx->resource->getTVValue('Primary Product Image'),
    'brand' => $modx->runSnippet('TaggerGetTags', ['groups' => 1, 'resources' => $modx->resource->get('id'), 'separator' => ', ', 'rowTpl' => '@INLINE [[+tag]]']),
    'category' => $modx->runSnippet('TaggerGetTags', ['groups' => 2, 'resources' => $modx->resource->get('id'), 'separator' => ', ', 'rowTpl' => '@INLINE [[+tag]]']),
    'offers' => [],
];

$variations = $modx->getCollection('ProductVariation', [
    'resource_id' => $modx->resource->get('id'),
    'published' => true,
    'deleted' => false,
    'nla' => false,
]);


if ($variations) {
    foreach ($variations as $variation) {
        if ($variation->getPrice(false) && $variation->get('covered') != 1) {
            $schema['offers'][] = [
                '@type' => 'Offer',
                'availability' => $variation->getStock() > 0 ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock',
                'price' => $variation->getPrice(false),
                'priceCurrency' => 'USD',
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'returnPolicyCountry' => 'US',
                    'merchantReturnLink' => 'https://www.poconosewandvac.com/contact',
                    'returnFees' => 'http://schema.org/ReturnShippingFees',
                ],
            ];
        }
    }
}

if (empty($schema['offers'])) {
    // Don't output schema if product has no offers
    return;
}

return '<script type="application/ld+json">' . json_encode($schema) . '</script>';
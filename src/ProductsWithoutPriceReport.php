<?php

namespace SilverShop\Reports;

use SilverShop\Page\Product;
use SilverStripe\Reports\Report;

/**
 * Products with a zero base price — usually a data-entry oversight. (A product priced only through its
 * variations will still appear here; treat it as "check me", not "definitely broken".)
 */
class ProductsWithoutPriceReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Products with no price');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 100;
    }

    public function sourceRecords($params = null)
    {
        return Product::get()->filter('BasePrice', 0);
    }

    public function columns()
    {
        return [
            'Title' => ['title' => _t('SilverShop\\Reports.ColProduct', 'Product'), 'link' => true],
            'InternalItemID' => _t('SilverShop\\Reports.ColSKU', 'SKU'),
        ];
    }
}

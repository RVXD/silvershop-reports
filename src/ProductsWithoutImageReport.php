<?php

namespace SilverShop\Reports;

use SilverShop\Page\Product;
use SilverStripe\Reports\Report;

/**
 * Products that have no main image — they render with a placeholder on the storefront and look unfinished.
 */
class ProductsWithoutImageReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Products with no image');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 110;
    }

    public function sourceRecords($params = null)
    {
        return Product::get()->filter('ImageID', 0);
    }

    public function columns()
    {
        return [
            'Title' => ['title' => _t('SilverShop\\Reports.ColProduct', 'Product'), 'link' => true],
            'InternalItemID' => _t('SilverShop\\Reports.ColSKU', 'SKU'),
        ];
    }
}

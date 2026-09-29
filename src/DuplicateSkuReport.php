<?php

namespace SilverShop\Reports;

use SilverShop\Page\Product;
use SilverStripe\Reports\Report;

/**
 * Products that share an SKU (InternalItemID) with another product — usually a copy/paste or import mistake,
 * and a problem for anything that keys on SKU (stock, product feeds, external systems).
 */
class DuplicateSkuReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Products with duplicate SKUs');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 130;
    }

    public function sourceRecords($params = null)
    {
        $skus = array_filter(
            Product::get()->column('InternalItemID'),
            fn($value) => (string) $value !== ''
        );
        $duplicates = array_keys(array_filter(array_count_values($skus), fn($count) => $count > 1));

        if (empty($duplicates)) {
            return Product::get()->filter('ID', 0); // empty list, no "empty set" error
        }

        return Product::get()->filter('InternalItemID', $duplicates)->sort('InternalItemID');
    }

    public function columns()
    {
        return [
            'InternalItemID' => _t('SilverShop\\Reports.ColSKU', 'SKU'),
            'Title' => ['title' => _t('SilverShop\\Reports.ColProduct', 'Product'), 'link' => true],
        ];
    }
}

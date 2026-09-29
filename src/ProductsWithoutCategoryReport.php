<?php

namespace SilverShop\Reports;

use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Reports\Report;

/**
 * Products whose page-tree parent is not a ProductCategory — i.e. orphaned in the catalog. They are hard to
 * find on the storefront and easy to forget about.
 */
class ProductsWithoutCategoryReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Products not under a category');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 120;
    }

    public function sourceRecords($params = null)
    {
        $categoryIds = ProductCategory::get()->column('ID');

        // No categories at all => every product is effectively orphaned.
        if (empty($categoryIds)) {
            return Product::get();
        }

        return Product::get()->exclude('ParentID', $categoryIds);
    }

    public function columns()
    {
        return [
            'Title' => ['title' => _t('SilverShop\\Reports.ColProduct', 'Product'), 'link' => true],
            'InternalItemID' => _t('SilverShop\\Reports.ColSKU', 'SKU'),
        ];
    }
}

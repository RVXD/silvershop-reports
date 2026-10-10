<?php

declare(strict_types=1);

namespace SilverShop\Reports\Tests;

use SilverShop\Page\Product;
use SilverShop\Reports\BestSellersByRevenueReport;
use SilverShop\Reports\DuplicateSkuReport;
use SilverShop\Reports\OrdersByStatusReport;
use SilverShop\Reports\ProductsWithoutCategoryReport;
use SilverShop\Reports\ProductsWithoutPriceReport;
use SilverShop\Reports\TopCustomersBySpendReport;
use SilverStripe\Dev\SapphireTest;

class ReportsTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static bool $use_draft_site = true;

    /**
     * Every report instantiates, has a title, and its sourceRecords() executes cleanly on an empty DB — which
     * exercises the hand-written SQL (the raw SQLSelect reports) against the test database (SQLite in CI).
     */
    public function testAllReportsRunCleanly(): void
    {
        $classes = [
            DuplicateSkuReport::class,
            OrdersByStatusReport::class,
            BestSellersByRevenueReport::class,
            TopCustomersBySpendReport::class,
            ProductsWithoutPriceReport::class,
            ProductsWithoutCategoryReport::class,
        ];

        foreach ($classes as $class) {
            $report = new $class();
            $this->assertNotEmpty((string) $report->title(), "{$class} has a title");
            $this->assertCount(0, $report->sourceRecords(), "{$class} runs cleanly with no data");
        }
    }

    public function testNoPriceReportFlagsPricelessProduct(): void
    {
        $free = Product::create(['Title' => 'Freebie', 'BasePrice' => 0]);
        $free->write();
        $priced = Product::create(['Title' => 'Priced', 'BasePrice' => 9.99]);
        $priced->write();

        $ids = array_map('intval', (new ProductsWithoutPriceReport())->sourceRecords()->column('ID'));
        $this->assertContains((int) $free->ID, $ids, 'priceless product is flagged');
        $this->assertNotContains((int) $priced->ID, $ids, 'priced product is not flagged');
    }
}

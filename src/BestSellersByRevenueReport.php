<?php

namespace SilverShop\Reports;

use SilverShop\Model\Order;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Reports\Report;

/**
 * Top products by revenue (and units) for a period — complements core's ProductReport, which ranks by quantity.
 * Placed orders, product line items only. Limited to the top 100.
 */
class BestSellersByRevenueReport extends Report
{
    private const LIMIT = 100;

    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Best sellers by revenue');
    }

    public function description()
    {
        return _t(__CLASS__ . '.DESC', 'Top products by revenue (and units) for a period. Placed orders, product lines only.');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 210;
    }

    public function parameterFields()
    {
        return FieldList::create(
            DateField::create('StartDate', _t(__CLASS__ . '.Start', 'From (paid date)')),
            DateField::create('EndDate', _t(__CLASS__ . '.End', 'To (paid date)'))
        );
    }

    public function sourceRecords($params = null)
    {
        $statuses = array_values((array) Order::config()->get('placed_status'));
        if (empty($statuses)) {
            return ArrayList::create();
        }

        $query = SQLSelect::create();
        $query->setSelect([
            'Product' => '"st"."Title"',
            'Units' => 'SUM("oi"."Quantity")',
            'Revenue' => 'SUM("oa"."CalculatedTotal")',
        ]);
        $query->setFrom('"SilverShop_OrderAttribute" AS "oa"');
        $query->addInnerJoin('SilverShop_OrderItem', '"oi"."ID" = "oa"."ID"', 'oi');
        $query->addInnerJoin('SilverShop_Product_OrderItem', '"poi"."ID" = "oa"."ID"', 'poi');
        $query->addInnerJoin('SilverShop_Order', '"o"."ID" = "oa"."OrderID"', 'o');
        $query->addInnerJoin('SiteTree', '"st"."ID" = "poi"."ProductID"', 'st');

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $query->addWhere(['"o"."Status" IN (' . $placeholders . ')' => $statuses]);

        if (!empty($params['StartDate'])) {
            $query->addWhere(['"o"."Paid" >= ?' => $params['StartDate'] . ' 00:00:00']);
        }
        if (!empty($params['EndDate'])) {
            $query->addWhere(['"o"."Paid" <= ?' => $params['EndDate'] . ' 23:59:59']);
        }

        $query->setGroupBy(['"poi"."ProductID"', '"st"."Title"']);
        $query->setOrderBy('SUM("oa"."CalculatedTotal")', 'DESC');
        $query->setLimit(self::LIMIT);

        $list = ArrayList::create();
        foreach ($query->execute() as $row) {
            $list->push(ArrayData::create([
                'Product' => $row['Product'],
                'Units' => (int) $row['Units'],
                'Revenue' => number_format((float) $row['Revenue'], 2),
            ]));
        }

        return $list;
    }

    public function columns()
    {
        return [
            'Product' => _t(__CLASS__ . '.ColProduct', 'Product'),
            'Units' => _t(__CLASS__ . '.ColUnits', 'Units sold'),
            'Revenue' => _t(__CLASS__ . '.ColRevenue', 'Revenue'),
        ];
    }
}

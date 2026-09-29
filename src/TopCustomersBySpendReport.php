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
 * Registered customers ranked by total spend across placed orders, for a period. Guests (orders with no linked
 * account) are excluded. Limited to the top 100.
 */
class TopCustomersBySpendReport extends Report
{
    private const LIMIT = 100;

    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Top customers by spend');
    }

    public function description()
    {
        return _t(__CLASS__ . '.DESC', 'Registered customers ranked by total spend (placed orders) for a period. Guests without an account are excluded.');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 220;
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
            'FirstName' => '"m"."FirstName"',
            'Surname' => '"m"."Surname"',
            'Email' => '"m"."Email"',
            'Orders' => 'COUNT("o"."ID")',
            'Spend' => 'SUM("o"."Total")',
        ]);
        $query->setFrom('"SilverShop_Order" AS "o"');
        $query->addInnerJoin('Member', '"m"."ID" = "o"."MemberID"', 'm');

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $query->addWhere(['"o"."Status" IN (' . $placeholders . ')' => $statuses]);

        if (!empty($params['StartDate'])) {
            $query->addWhere(['"o"."Paid" >= ?' => $params['StartDate'] . ' 00:00:00']);
        }
        if (!empty($params['EndDate'])) {
            $query->addWhere(['"o"."Paid" <= ?' => $params['EndDate'] . ' 23:59:59']);
        }

        $query->setGroupBy(['"m"."ID"', '"m"."FirstName"', '"m"."Surname"', '"m"."Email"']);
        $query->setOrderBy('SUM("o"."Total")', 'DESC');
        $query->setLimit(self::LIMIT);

        $list = ArrayList::create();
        foreach ($query->execute() as $row) {
            $list->push(ArrayData::create([
                'Customer' => trim(($row['FirstName'] ?? '') . ' ' . ($row['Surname'] ?? '')),
                'Email' => $row['Email'],
                'Orders' => (int) $row['Orders'],
                'Spend' => number_format((float) $row['Spend'], 2),
            ]));
        }

        return $list;
    }

    public function columns()
    {
        return [
            'Customer' => _t(__CLASS__ . '.ColCustomer', 'Customer'),
            'Email' => _t(__CLASS__ . '.ColEmail', 'Email'),
            'Orders' => _t(__CLASS__ . '.ColOrders', 'Orders'),
            'Spend' => _t(__CLASS__ . '.ColSpend', 'Total spend'),
        ];
    }
}

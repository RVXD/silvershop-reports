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
 * Order count and total value grouped by status (Paid, Processing, Sent, Complete, Cancelled, …), for a period —
 * the operational pipeline view. Excludes hidden (Cart) statuses.
 */
class OrdersByStatusReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Orders by status');
    }

    public function description()
    {
        return _t(__CLASS__ . '.DESC', 'Order count and total value per status, for a period.');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 200;
    }

    public function parameterFields()
    {
        return FieldList::create(
            DateField::create('StartDate', _t(__CLASS__ . '.Start', 'From (placed date)')),
            DateField::create('EndDate', _t(__CLASS__ . '.End', 'To (placed date)'))
        );
    }

    public function sourceRecords($params = null)
    {
        $labels = Order::get_order_status_options();
        $hidden = array_values((array) Order::config()->get('hidden_status'));

        $query = SQLSelect::create();
        $query->setSelect(['Status' => '"Status"', 'Orders' => 'COUNT(*)', 'Revenue' => 'SUM("Total")']);
        $query->setFrom('"SilverShop_Order"');

        if (!empty($hidden)) {
            $placeholders = implode(',', array_fill(0, count($hidden), '?'));
            $query->addWhere(['"Status" NOT IN (' . $placeholders . ')' => $hidden]);
        }
        if (!empty($params['StartDate'])) {
            $query->addWhere(['"Placed" >= ?' => $params['StartDate'] . ' 00:00:00']);
        }
        if (!empty($params['EndDate'])) {
            $query->addWhere(['"Placed" <= ?' => $params['EndDate'] . ' 23:59:59']);
        }

        $query->setGroupBy('"Status"')->setOrderBy('SUM("Total")', 'DESC');

        $list = ArrayList::create();
        foreach ($query->execute() as $row) {
            $list->push(ArrayData::create([
                'StatusLabel' => $labels[$row['Status']] ?? $row['Status'],
                'Orders' => (int) $row['Orders'],
                'Revenue' => number_format((float) $row['Revenue'], 2),
            ]));
        }

        return $list;
    }

    public function columns()
    {
        return [
            'StatusLabel' => _t(__CLASS__ . '.ColStatus', 'Status'),
            'Orders' => _t(__CLASS__ . '.ColOrders', 'Orders'),
            'Revenue' => _t(__CLASS__ . '.ColRevenue', 'Total value'),
        ];
    }
}

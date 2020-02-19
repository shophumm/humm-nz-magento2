<?php
/**
 * Created by PhpStorm.
 * User: dev-mac
 * Date: 19/2/20
 * Time: 11:12 AM
 */

namespace Humm\HummPaymentGateway\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Humm\HummPaymentGateway\Helper\Data as Helper;


class Collection extends OriginalCollection
{
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('sales_order');
        $this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = sales_order.entity_id', ['coupon_code']);
        parent::_renderFiltersBefore();
    }
}
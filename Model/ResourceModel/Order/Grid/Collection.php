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

/**
 * Class Collection
 * @package Humm\HummPaymentGateway\Model\ResourceModel\Order\Grid
 */
class Collection extends OriginalCollection
{
    protected function _renderFiltersBefore()
    {
        $this->setMainTable('sales_order_status_history');
        $joinTable = $this->getTable('sales_order_payment');
        $joinSales= $this->getTable('sales_order');
        $this->getSelect()->joinLeft($joinTable, 'main_table.parent_id = sales_order_payment.entity_id', ['additional_information','amount_paid']);
        parent::_renderFiltersBefore();
    }
}
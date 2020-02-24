<?php
namespace Humm\HummPaymentGateway\Model\ResourceModel\Order;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'sales_order_status_history_collection';
    protected $_eventObject = 'order_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Humm\HummPaymentGateway\Model\Order', 'Humm\HummPaymentGateway\Model\ResourceModel\Order');
    }

}
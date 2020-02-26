<?php

namespace Humm\HummPaymentGateway\Cron;

use Humm\HummPaymentGateway\Helper\HummLogger;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;

class UpdateHummOrder
{
    protected $_hummlogger;
    protected $_orderCollectionFactory;
    protected $_orderManager;
    protected $_collection;
    protected $_timeZone;
    const paymentMethod = 'humm';
    const statuses = ['pending', 'closing'];


    public function __construct(
        \Humm\HummPaymentGateway\Helper\HummLogger $hummLogger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    )
    {
        $this->_hummlogger = $hummLogger;
        $this->_timeZone = $timezone;
        $this->_orderCollectionFactory = $orderCollectionFactory;

    }

    public function execute()
    {
        $time = $this->_timeZone->scopeTimeStamp();
        $dateNow = (new \DateTime())->setTimestamp($time);
        $to = $dateNow->format('Y-m-d H:i:s');
        $this->_hummlogger->log("Start Crontab..time now.." . $to);
        $from = $dateNow->sub(new \DateInterval('P1D'))->format('Y-m-d H:i:s');
        $this->_hummlogger->log(sprintf("from %s to %s", $from, $to));
        $_collection = $this->getOrderCollectionPaymentMethod(self::paymentMethod, $from, $to);
        $this->processCollection($_collection);
        return $this;
    }

    public function getOrderCollectionPaymentMethod($paymentMethod = null, $from, $to)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('created_at',
                ['gteq' => $from]
            )
            ->addFieldToFilter('created_at',
                ['lteq' => $to]
            )
            ->addFieldToFilter('status', ['in' => self::statuses]
            );

        $collection->getSelect()
            ->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                array('method', 'amount_paid', 'amount_ordered')
            )
            ->where('sop.method like "%humm%" and sop.amount_paid is NULL');

        $collection->setOrder(
            'created_at',
            'desc'
        );

        return $collection;

    }

    public function processCollection($collection)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($collection as $key => $item) {
            $this->_hummlogger->log($item->getData('increment_id') . $item->getData('state') . $item->getData('status'), true);
            $hummOrderId = $item->getData('increment_id');
            $this->processHummOrder($hummOrderId, $objectManager);
        }

    }

    public function processHummOrder($hummOrderId, $objectManager)
    {

        $hummOrder = $objectManager->create('\Magento\Sales\Model\Order')->load($hummOrderId);

        if ($hummOrder->getId() && $hummOrder->getState() != Order::STATE_CANCELED) {
            $this->_hummlogger->log($hummOrder->getState() . 'Crontab' . $hummOrderId);
            $hummOrder->registerCancellation('cancelled by customer Cron Humm Payment ')->save();
        }
    }

    public function getOrderCollectionByStatus($statuses = [])
    {
        $collection = $this->_orderCollectionFactory()->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status',
                ['in' => $statuses]
            );

        return $collection;

    }
}


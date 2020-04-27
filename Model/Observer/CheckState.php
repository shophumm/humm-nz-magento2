<?php

namespace Humm\HummPaymentGateway\Model\Observer;

use Humm\HummPaymentGateway\Controller\Checkout\AbstractAction;
use Humm\HummPaymentGateway\Helper\HummLogger;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;


/**
 * @author roger.bi@flexigroup.com.au
 * Class CheckState
 * @package Humm\HummPaymentGateway\Model\Observer
 */
class CheckState implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var HummLogger
     */
    protected $_hummLogger;

    /**
     * CheckState constructor.
     * @param HummLogger $hummLogger
     */
    public function __construct(HummLogger $hummLogger)
    {
        $this->_hummLogger = $hummLogger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getData('order');
            $type = $observer->getData('type');
            $this->_hummLogger->log("Cancel Transaction:log cancel in Observer Section" . $order->getId() . 'type=' . $type);
            if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
                $order->registerCancellation('This order is cancelled by customer Humm Payment')->save();
            }

        } catch (\Exception $e) {
            $this->_hummLogger->log('Cancel error:' . $e->getCode() . '->' . $e->getMessage() . 'type=' . $type);

        }
    }
}

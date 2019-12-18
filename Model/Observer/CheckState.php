<?php

namespace Humm\HummPaymentGateway\Model\Observer;

use Humm\HummPaymentGateway\Controller\Checkout\AbstractAction;
use Humm\HummPaymentGateway\Helper\HummLogger;
use Magento\Framework\Event\Observer;


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
            $type  = $observer->getData('type');
            if($this->_hummLogger) {
                $this->_hummLogger->log("log cancel in Observer Section" . $order->getId() . 'type=' . $type);
            }
            $newState = \Magento\Sales\Model\Order::STATE_COMPLETE;
            $order->setState($newState)->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->addStatusHistoryComment('This order is cancelled by customer in cancel button of Hummpayment');
            $order->save();
        } catch (\Exception $e) {
            if($this->_hummLogger) {
                $this->_hummLogger->log('Cancel error:' . $e->getCode() . '->' . $e->getMessage() . 'type=' . $type);
            }

        }
    }
}

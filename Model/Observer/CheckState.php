<?php

namespace Humm\HummPaymentGateway\Model\Observer;

use Humm\HummPaymentGateway\Controller\Checkout\AbstractAction;
use Humm\HummPaymentGateway\Helper\HummLogger;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;


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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * CheckState constructor.
     * @param HummLogger $hummLogger
     */
    public function __construct(Session $session, HummLogger $hummLogger)
    {
        $this->_hummLogger = $hummLogger;
        $this->_session =    $session;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getData('order');
            $type = $observer->getData('type');
            $this->_hummLogger->log(sprintf("Cancel Order [Order Id:%s] [Type :%s]",$order->getId(),$type));

            if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
                $this->_session->restoreQuote();
                $order->registerCancellation('This order is cancelled by customer Humm Payment')->save();
            }
        } catch (\Exception $e) {
            $this->_hummLogger->log('Cancel Order error:' . $e->getCode() . '->' . $e->getMessage() . 'type=' . $type);

        }
    }
}

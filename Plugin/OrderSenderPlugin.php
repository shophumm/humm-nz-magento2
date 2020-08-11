<?php

namespace Humm\HummPaymentGateway\Plugin;

use Magento\Sales\Model\Order;

/**
 * Class OrderSenderPlugin
 * @package Humm\HummPaymentGateway\Plugin
 */
class OrderSenderPlugin
{

    /**
     * @param Order\Email\Sender\OrderSender $subject
     * @param callable $proceed
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function aroundSend(\Magento\Sales\Model\Order\Email\Sender\OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if ($payment === 'humm_gateway' && $order->getState() !== 'processing') {
            return false;
        }

        return $proceed($order, $forceSyncMode);
    }
}

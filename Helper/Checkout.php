<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Humm\HummPaymentGateway\Helper;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;

/**
 * Checkout workflow helper
 *
 * Class Checkout
 * @package Humm\HummPaymentGateway\Helper
 */
class Checkout {
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * @param $comment
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelCurrentOrder( $comment ) {
        $order = $this->session->getLastRealOrder();
        if ( $order->getId() && $order->getState() != Order::STATE_CANCELED ) {
            $order->registerCancellation( $comment )->save();

            return true;
        }

        return false;
    }

    /**
     * Restores quote (restores cart)
     *
     * @return bool
     */
    public function restoreQuote() {
        return $this->session->restoreQuote();
    }
}

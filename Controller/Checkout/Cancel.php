<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Context;


/**
 * Roger.bi@flexigroup.com.au
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class Cancel extends AbstractAction
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->get('orderId');
        $order = $orderId ? $this->getOrderById($orderId) : false;
        if($this->getHummLogger()) {
            $this->getHummLogger()->log('Requested order cancellation by customer. OrderId&QuoteId: ' . $order->getId() . $order->getQuoteId());
        }
        try {
            $this->_eventManager->dispatch('humm_payment_cancel', ['order' => $order, 'type' => 'button']);
            if($this->getHummLogger()) {
                $this->getHummLogger()->log('humm_payment_cancel' . $orderId);
            }
            $this->getMessageManager()->addWarningMessage(__("You have cancelled your humm payment. Please Check"));
        } catch (\Exception $e) {
            if($this->getHummLogger()) {
                $this->getHummLogger()->log('humm_payment_cancel_error' . $orderId);
            }
            $this->getMessageManager()->addWarningMessage(__("Cancelled order error. Please Check Order"));

        }
        $this->_redirect('humm/checkout/error');
    }
}

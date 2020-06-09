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
        $this->getHummLogger()->log(sprintf(sprintf('Start Cancel[OrderId: %s ]',$order->getIncrementId())));
        try {
            $this->_eventManager->dispatch('humm_payment_cancel', ['order' => $order, 'type' => 'button']);
            if ($order->getAppliedRuleIds()) {
                $this->_eventManager->dispatch('humm_payment_coupon_cancel', ['order' => $order, 'type' => 'coupon']);
            }
            $this->getMessageManager()->addWarningMessage(__("You have cancelled your humm payment. Please Check"));
        } catch (\Exception $e) {
            $this->getHummLogger()->log('End due to Error: humm_payment_cancel_error or humm_payment_coupon_cancel' . $orderId);
            $this->getMessageManager()->addWarningMessage(__("Cancelled order error. Please Check Order"));

        }
        $this->_redirect('humm/checkout/error');
    }
}

<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;

/**
 * roger.bi@flexigroup.cpm.au
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class Success extends AbstractAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $isValid = $this->getCryptoHelper()->isValidSignature($this->getRequest()->getParams(), $this->_encrypted->processValue($this->getGatewayConfig()->getApiKey()));
        $result = $this->getRequest()->get("x_result");
        $orderId = $this->getRequest()->get("x_reference");
        $transactionId = $this->getRequest()->get("x_gateway_reference");

        if (!$isValid) {
            if ($this->getHummLogger()) {
                $this->getHummLogger()->log('Possible site forgery detected: invalid response signature.');
            }
            $this->_redirect('humm/checkout/error');
            return;
        }

        if (!$orderId) {
            if ($this->getHummLogger()) {
                $this->getHummLogger()->log("Humm returned a null order id. This may indicate an issue with the humm payment gateway.");
            }
            $this->_redirect('humm/checkout/error');

            return;
        }

        $order = $this->getOrderById($orderId);
        if (!$order) {
            if ($this->getHummLogger()) {
                $this->getHummLogger()->log("Humm returned an id for an order that could not be retrieved: $orderId");
            }
            $this->_redirect('humm/checkout/error');

            return;
        }

        if ($result == "completed" && $order->getState() === Order::STATE_PROCESSING) {

            $this->_redirect('checkout/onepage/success', array('_secure' => false));

            return;
        }

        if ($result == "failed" && $order->getState() === Order::STATE_CANCELED) {
            $this->_redirect('checkout/onepage/failure', array('_secure' => false));
            return;
        }

        if ($result == "completed") {
            $orderState = Order::STATE_PROCESSING;

            $orderStatus = $this->getGatewayConfig()->getHummApprovedOrderStatus();
            if (!$this->statusExists($orderStatus)) {
                $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
            }

            $emailCustomer = $this->getGatewayConfig()->isEmailCustomer();

            $order->setState($orderState)
                ->setStatus($orderStatus)
                ->addStatusHistoryComment("Humm authorisation success. Transaction #$transactionId")
                ->setIsCustomerNotified($emailCustomer);

            $payment = $order->getPayment();
            $payment->setTransactionId($transactionId);
            $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
            $order->save();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($order);

            $invoiceAutomatically = $this->getGatewayConfig()->isAutomaticInvoice();
            if ($invoiceAutomatically) {
                $this->invoiceOrder($order, $transactionId);
            }
            $this->getMessageManager()->addSuccessMessage(__("Your payment with humm is complete"));
            if ($this->getHummLogger()) {
                $this->getHummLogger()->log("Humm returned successful for orderID: $orderId");
            }
            $this->_redirect('checkout/onepage/success', array('_secure' => false));
        } else {
            $this->_eventManager->dispatch('humm_payment_cancel', ['order' => $order, 'type' => $result]);
            if ($this->getHummLogger()) {
                $this->getHummLogger()->log('humm_payment_cancel' . $orderId);
            }
            $this->getMessageManager()->addWarningMessage(__("humm payment is unsuccessful. Please Check"));
            $this->getMessageManager()->addErrorMessage(__("There was an error in the humm payment"));
            $this->_redirect('checkout/cart', array('_secure' => false));
        }
    }

    /**
     * @param $orderStatus
     * @return bool
     */
    private function statusExists($orderStatus)
    {
        $statuses = $this->getObjectManager()
            ->get('Magento\Sales\Model\Order\Status')
            ->getResourceCollection()
            ->getData();
        foreach ($statuses as $status) {
            if ($orderStatus === $status["status"]) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $order
     * @param $transactionId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function invoiceOrder($order, $transactionId)
    {
        if (!$order->canInvoice()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Cannot create an invoice.')
            );
        }

        $invoice = $this->getObjectManager()
            ->create('Magento\Sales\Model\Service\InvoiceService')
            ->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }

        $invoice->setTransactionId($transactionId);
        $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();

        $transaction = $this->getObjectManager()->create('Magento\Framework\DB\Transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transaction->save();
    }

}

<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
/**
 * roger.bi@flexigroup.cpm.au
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class Success extends AbstractAction implements CsrfAwareActionInterface
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $isAsyncCallback = $_SERVER['REQUEST_METHOD'] === "POST" ? true : false;
        $params = $this->getRequest()->getParams();

        if ($this->getHummLogger()) {
            $this->getHummLogger()->log('Identify CallBack.' . $isAsyncCallback . '|server:' . json_encode($this->getRequest()->getParams()));
        }
        $isValid = $this->getCryptoHelper()->isValidSignature($this->getRequest()->getParams(), $this->_encrypted->processValue($this->getGatewayConfig()->getApiKey()));
        $result = $params['x_result'];
        $orderId = $params['x_reference'];
        $transactionId = $params['x_gateway_reference'];
        if ($this->getHummLogger()) {
            $this->getHummLogger()->log('Identify CallBack [transactionID]' . $transactionId . "[result]" . $result . '[orderID]' . $orderId);
        }

        if (!$isValid) {
            if ($this->getHummLogger()) {
                $this->getHummLogger()->log('Possible site forgery detected: invalid response signature.' . $transactionId);
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
            try {
                $orderStatus = $this->getGatewayConfig()->getHummApprovedOrderStatus();
                if (!$this->statusExists($orderStatus)) {
                    $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
                }
                $emailCustomer = $this->getGatewayConfig()->isEmailCustomer();
                if ($this->getHummLogger()) {
                    $this->getHummLogger()->log("callback successful: state ==" . $orderState . "|" . $orderStatus);
                }
                $order->setState($orderState)
                    ->setStatus($orderStatus)
                    ->addStatusHistoryComment("Humm authorisation success. Transaction #$transactionId")
                    ->setIsCustomerNotified($emailCustomer);

                $payment = $order->getPayment();
                $payment->setTransactionId($transactionId);
                $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
                $payment->setAdditionalInformation(array("HummPayment".$result =>"done"));;
                $order->save();
                $this->logContent("After update state&status".$order->getState()."|".$order->getStatus());
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
                $emailSender->send($order);
                $invoiceAutomatically = $this->getGatewayConfig()->isAutomaticInvoice();
                if ($invoiceAutomatically) {
                    $this->invoiceOrder($order, $transactionId);
                    if ($this->getHummLogger()) {
                        $this->getHummLogger()->log("Humm invoice produced:" . $orderId);
                    }
                }
                if ($this->getHummLogger()) {
                    $this->getHummLogger()->log("Humm returned successful for orderID:" . $orderId);
                }
                $this->getMessageManager()->addSuccessMessage(__("Your payment with humm is complete"));
            } catch (\Exception $e) {
                if ($this->getHummLogger()) {
                    $this->getHummLogger()->log("Successful Update State/Status Error:" . $e->getMessage());
                }
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
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
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

    /**
     * @param $content
     */

    public function logContent($content) {

        if ($this->getHummLogger()) {
            $this->getHummLogger()->log("Trace Content:" . $content);
        }
    }
}

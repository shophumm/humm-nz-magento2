<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * roger.bi@flexigroup.com.au
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class Success extends AbstractAction implements CsrfAwareActionInterface
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        list($order,$transactionId,$result,$orderId) = $this->ValidateCallback();

        if ($result == "completed") {
            $orderState = Order::STATE_PROCESSING;
            try {
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
                $AdditionalNew = array_merge($payment->getAdditionalInformation(),
                    ["result" => sprintf(("Method :[%s] Result :[%s]"), $this->getRequest()->getMethod(), $result)]
                );
                $payment->setAdditionalInformation($AdditionalNew);;
                $order->save();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
                $emailSender->send($order);
                $invoiceAutomatically = $this->getGatewayConfig()->isAutomaticInvoice();
                if ($invoiceAutomatically) {
                    $this->invoiceOrder($order, $transactionId);
                    $this->getHummLogger()->log("Humm invoice produced:" . $orderId);
                }
                $this->getHummLogger()->log(sprintf("END Payment:[OrderID:%s] [State:%s] [Status:%s]",$orderId,$orderState,$orderStatus));
                $this->getMessageManager()->addSuccessMessage(__("Your payment with humm is complete"));
            } catch (\Exception $e) {
                $this->getHummLogger()->log("Successful Update State/Status Error:" . $e->getMessage());
            }
            $this->_redirect('checkout/onepage/success', array('_secure' => false));
        } else {
            $this->_eventManager->dispatch('humm_payment_cancel', ['order' => $order, 'type' => $result]);
            $this->getHummLogger()->log('humm_payment_cancel' . $orderId);
            $this->getMessageManager()->addWarningMessage(__("humm payment is unsuccessful. Please Check"));
            $this->getMessageManager()->addErrorMessage(__("There was an error in the humm payment"));
            $this->_redirect('checkout/cart', array('_secure' => false));
        }
    }

    /**
     * @return mixed
     */
    public function getClientIP()
    {
        $objctManager = \Magento\Framework\App\ObjectManager::getInstance();
        $remote = $objctManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        return $remote->getRemoteAddress();
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
     * @return array|void
     */

    public function ValidateCallback(){

        $params = $this->getRequest()->getParams();
        $isValid = $this->getCryptoHelper()->isValidSignature($this->getRequest()->getParams(), $this->_encrypted->processValue($this->getGatewayConfig()->getApiKey()));
        $result = $params['x_result'];
        list($orderId, $hummProtectCode) = explode("-", $params['x_reference']);
        $transactionId = $params['x_gateway_reference'];
        $merchantNo = $params['x_account_id'];
        $order = $this->getOrderById($orderId);
        $mesg = array();
        $errorMsg = array();
        $redirectErrorURL = '';
        $merchantNumber = $this->getGatewayConfig()->getMerchantNumber();
        $mesg[] = sprintf("CallBack Start: Order ProtectCode [Web:%s] [Humm:%s] | MerchantNo [web:%s] [Humm:%s]|[Response---%s] [method--%s]", $order->getProtectCode(), $hummProtectCode, $merchantNumber, $merchantNo, json_encode($this->getRequest()->getParams()), $this->getRequest()->getMethod());
        $mesg[] = sprintf("Client IP: %s", $this->getClientIP());

        if (($merchantNo != $this->getGatewayConfig()->getMerchantNumber()) || ($hummProtectCode != $order->getProtectCode())) {
            $errorMsg[] = sprintf("ERROR: Order ProtectCode [Web:%s] [Humm:%s] | %s MerchantNo %s |[Response---%s] [method--%s]", $order->getProtectCode(), $hummProtectCode, $merchantNumber, $merchantNo, json_encode($this->getRequest()->getParams()), $this->getRequest()->getMethod());
            $redirectErrorURL = "humm/checkout/error";
        }

        if (!$isValid) {
            $errorMsg[] = sprintf("Possible site forgery detected: invalid response signature transactionId:[%s]", $transactionId);
            if (!empty($redirectErrorURL))
                $redirectErrorURL = "humm/checkout/error";
        }

        if (!$orderId) {
            $errorMsg[] = sprintf("Humm returned a null order id. This may indicate an issue with the humm payment gateway.");
            if (!empty($redirectErrorURL))
                $redirectErrorURL = "humm/checkout/error";
        }
        if (!$order) {
            $errorMsg[] = sprintf("\"Humm returned an id for an order that could not be retrieved", $orderId);
            if (!empty($redirectErrorURL))
                $redirectErrorURL = "humm/checkout/error";
        }
        array_walk($mesg, function($eachMesg){
            $this->getHummLogger()->log($eachMesg,true);
        });

        if ($redirectErrorURL) {
            array_map(function($eachError){
                $this->getHummLogger()->log($eachError);
            },$errorMsg);
            $this->_redirect($redirectErrorURL);
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

        return array($order,$transactionId,$result,$orderId);
    }
}

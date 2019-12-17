<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

use Humm\HummPaymentGateway\Gateway\Config\Config;
use Humm\HummPaymentGateway\Helper\Checkout;
use Humm\HummPaymentGateway\Helper\Crypto;
use Humm\HummPaymentGateway\Helper\Data;
use Humm\HummPaymentGateway\Helper\HummLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;


/**
 * @copyright
 * @author&modify Roger.bi@flexigroup.com.au
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
abstract class AbstractAction extends Action
{


    const HUMM_DEFAULT_CURRENCY_CODE = 'AUD';
    const HUMM_DEFAULT_COUNTRY_CODE = 'AU';
    const HUMM_PAYMENT_ERRROUTE = "humm/checkout/error";


    private $_context;

    protected $_checkoutSession;

    private $_orderFactory;

    private $_cryptoHelper;

    private $_dataHelper;

    protected $_checkoutHelper;

    private $_gatewayConfig;

    private $_messageManager;

    private $_logger;

    protected $ordermanagement;

    protected $_hummLogger;

    protected $_pageFactory;

    protected $_urlBuilder;

    protected $_encrypted;

    protected $_resultJsonFactory;

    /**
     * AbstractAction constructor.
     * @param Config $gatewayConfig
     * @param Session $checkoutSession
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Crypto $cryptoHelper
     * @param Data $dataHelper
     * @param Checkout $checkoutHelper
     * @param LoggerInterface $logger
     * @param HummLogger $hummLogger
     * @param PageFactory $pageFactory
     */

    public function __construct(
        Config $gatewayConfig,
        Session $checkoutSession,
        Context $context,
        OrderFactory $orderFactory,
        Crypto $cryptoHelper,
        Data $dataHelper,
        Checkout $checkoutHelper,
        LoggerInterface $logger,
        HummLogger $hummLogger,
        PageFactory $pageFactory,
        \Magento\Config\Model\Config\Backend\Encrypted $encrypted,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_cryptoHelper = $cryptoHelper;
        $this->_dataHelper = $dataHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_gatewayConfig = $gatewayConfig;
        $this->_messageManager = $context->getMessageManager();
        $this->_logger = $logger;
        $this->_hummLogger = $hummLogger;
        $this->_pageFactory = $pageFactory;
        $this->_urlBuilder = $context->getUrl();
        $this->_encrypted = $encrypted;
        $this->_resultJsonFactory =$resultJsonFactory;
    }

    /**
     * @return mixed
     */
    protected function getContext()
    {
        return $this->_context;
    }

    /**
     * @return Session
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * @return OrderFactory
     */
    protected function getOrderFactory()
    {
        return $this->_orderFactory;
    }

    /**
     * @return Crypto
     */
    protected function getCryptoHelper()
    {
        return $this->_cryptoHelper;
    }

    /**
     * @return Data
     */
    protected function getDataHelper()
    {
        return $this->_dataHelper;
    }

    /**
     * @return Checkout
     */
    protected function getCheckoutHelper()
    {
        return $this->_checkoutHelper;
    }

    /**
     * @return Config
     */
    protected function getGatewayConfig()
    {
        return $this->_gatewayConfig;
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->_messageManager;
    }

    /**
     * @return LoggerInterface
     */

    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Sales\Model\Order|null
     */
    protected function getOrder()
    {
        $orderId = $this->_checkoutSession->getLastRealOrderId();

        if (!isset($orderId)) {
            return null;
        }

        return $this->getOrderById($orderId);
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Model\Order|null
     */
    protected function getOrderById($orderId)
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($orderId);

        if (!$order->getId()) {
            return null;
        }

        return $order;
    }

    /**
     * @return \Magento\Framework\App\ObjectManager
     */

    protected function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @return HummLogger
     */

    protected function getHummLogger()
    {
        if ($this->_gatewayConfig->getDebug()) {
            return $this->_hummLogger;
        }
        else
        {
            return null;
        }
    }


    /**
     * @return mixed
     */
    public function getSuccessUrl()
    {
        $url = $this->_urlBuilder->getUrl('checkout/onepage/success');

        return $url;
    }

    /**
     * @return string
     */
    public function getErrorUrl()
    {
        $url = $this->_urlBuilder->getUrl(self::HUMM_PAYMENT_ERRROUTE);
        return $url;
    }
}

<?php

namespace Humm\HummPaymentGateway\Block;

use Magento\Framework\View\Element\Template;
use Humm\HummPaymentGateway\Gateway\Config\Config;


/**
 * Class Error
 * Roger.bi@flexigroup.com.au
 * @package Humm\HummPaymentGateway\Block
 */
class Error extends Template
{
    /**
     * @const string
     */

    const ERROR_BODY = 'payment/humm_gateway/humm_message/error_body';
    /**
     * @const string
     */
    const ERROR_HEADER = 'payment/humm_gateway/humm_message/error_header';

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    protected $_config;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Config $config,
        array $data = [])
    {
        $this->_messageManager = $messageManager;
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Returns the error body text.
     *
     * @return string
     */
    public function getBodyText()
    {
        $text = null;
        if (!$this->_messageManager->hasMessages()) {

            $text = $this->_config->getValue(self::ERROR_BODY);
            if (!$text) {
                $text = __('There was an error processing your request. Please try again later.');
            }
        }
        return $text;
    }

    /**
     * Returns the error type text.
     *
     * @return string
     */
    public function getErrorTypeText()
    {
        $text = null;
        if (!$this->_messageManager->hasMessages()) {
            try {
                $code = (int)$this->getRequest()->getParam('code');
            } catch (\Exception $e) {
                $code = 0;
            }
            switch ($code) {
                case 200:
                    $text = __('work normal');
                    break;
                case 400:
                    $text = __('400 Bad Request');
                    break;
                case 401:
                    $text = __('401 Unauthorized');
                    break;
                case 403:
                    $text = __('403 Forbidden');
                    break;
                case 404:
                    $text = __('404 Not Found');
                    break;
                case 409:
                    $text = __('409 Conflict');
                    break;
                case 500:
                    $text = __('409 Internal Error');
                    break;
                default:
                    $text = $this->getRequest()->getParam('code') . __(' General Error or Cancel Payment');
                    break;
            }
        }
        return $text;
    }

    /**
     * Prepares the layout.
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $text = $this->_config->getValue(self::ERROR_HEADER);

        if (!$text) {
            $text = "An error occurred";
        }

        $this->pageConfig->getTitle()->set(__($text));

        return parent::_prepareLayout();

    }
}

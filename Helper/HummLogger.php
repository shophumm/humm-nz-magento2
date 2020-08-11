<?php

namespace Humm\HummPaymentGateway\Helper;

Use \Magento\Framework\App\Helper\AbstractHelper;
Use Humm\HummPaymentGateway\Gateway\Config\Config;
use Magento\Framework\App\Helper\Context;

/**
 * @author Roger.bi@flexigroup.com.au
 * Class HummLogger
 * @package Humm\HummPaymentGateway\Helper
 */
class  HummLogger extends AbstractHelper
{
    /**
     * @var
     */
    protected $_hummPaymentLog;
    protected $_hummConfig;

    /**
     * HummLogger constructor.
     * @param Context $context
     * @param \Magento\Framework\Logger\Monolog $hummPaymentLogger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(Context $context,
                                \Magento\Framework\Logger\Monolog $hummPaymentLogger,
                                \Humm\HummPaymentGateway\Gateway\Config\Config $hummConfig,
                                \Magento\Framework\Module\ModuleListInterface $moduleList
    )
    {

        $this->_hummPaymentLog = $hummPaymentLogger;
        $this->_hummConfig = $hummConfig;
        $this->_moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * @param $message
     * @param bool $useSeparator
     */

    public function log($message, $useSeparator = false)
    {

        if ($this->_hummConfig->getDebug()) {
            if ($useSeparator) {
                $this->_hummPaymentLog->addDebug(str_repeat('=', 100));
            }
            $this->_hummPaymentLog->addDebug($message);
        }
    }

}

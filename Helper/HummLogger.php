<?php
namespace Humm\HummPaymentGateway\Helper;

Use \Magento\Framework\App\Helper\AbstractHelper;
Use Humm\HummPaymentGateway\Gateway\Config;
use Magento\Framework\App\Helper\Context;

/**
 * @author Roger.bi@flexigroup.com.au
 * Class HummLogger
 * @package Humm\HummPaymentGateway\Helper
 */
class  HummLogger extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Logger\Monolog|\Psr\Log\LoggerInterface
     */
    protected $_hummPaymentLog;


    /**
     * HummLogger constructor.
     * @param Context $context
     * @param \Magento\Framework\Logger\Monolog $hummPaymentLogger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(Context $context,
                                \Magento\Framework\Logger\Monolog $hummPaymentLogger,
                               \Magento\Framework\Module\ModuleListInterface $moduleList
      )
    {

        $this->_hummPaymentLog = $hummPaymentLogger;
        $this->_moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * @param $message
     * @param bool $useSeparator
     */

    public function log($message,$useSeparator = false) {

        if ($useSeparator) {

            $this->_hummPaymentLog->addDebug(str_repeat('=',100));
        }
        $this->_hummPaymentLog->addDebug($message);
    }


}

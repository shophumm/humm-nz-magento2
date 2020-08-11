<?php
namespace Humm\HummPaymentGateway\Logger;
use Monolog\Logger;

/**
 * @author roger.bi@flexigroup.com.au
 * Class HummPaymentHandler
 * @package Humm\HummPaymentGateway\Logger
 */

class HummPaymentHandler extends  \Magento\Framework\Logger\Handler\Base
{
    /**
     * Filename
     * @var string
     */
    protected $fileName = '/var/log/humm-payment.log';
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}

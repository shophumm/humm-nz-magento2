<?php
namespace Humm\HummPaymentGateway\Plugin;
use Humm\HummPaymentGateway\Helper\HummLogger;
use Humm\HummPaymentGateway\Controller\Checkout\Index;


/**
 * Class Redicall
 * @package Humm\HummPaymentGateway\Plugin
 */
class Redicall
{
    public $logger;
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function afterStatusExists(\Humm\HummPaymentGateway\Controller\Checkout\Success $subject, $result)
    {

    }
}
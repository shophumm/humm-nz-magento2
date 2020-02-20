<?php
/**
 * Created by PhpStorm.
 * User: dev-mac
 * Date: 20/2/20
 * Time: 4:12 PM
 */

namespace Humm\HummPaymentGateway\Plugin;
use Humm\HummPaymentGateway\Helper\HummLogger;
use Humm\HummPaymentGateway\Controller\Checkout\Index;


class Redicall
{
    public $logger;
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function afterStatusExists(\Humm\HummPaymentGateway\Controller\Checkout\Success $subject, $result)
    {

        $this->logger->debug(json_encode($result));
        $this->logger->debug(json_encode($subject));


    }
}
<?php

namespace Humm\HummPaymentGateway\Plugin;

/**
 * Class HummPayment
 * @package Humm\HummPaymentGateway\Plugin
 */
use Psr\Log\LoggerInterface;

class HummPayment
{
    public function beforeSetAdditionalData(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\Quote\Payment $subject,
        $additionalData
    )
    {
        $logger->debug(json_encode("additionalData" . $additionalData));
        $additionalData = $additionalData . "this is an action";
    }

}

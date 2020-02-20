<?php
namespace Humm\HummPaymentGateway\Plugin;
/**
 * Class HummPayment
 * @package Humm\HummPaymentGateway\Plugin
 */

class HummPayment
{
    public function beforeSetAdditionalData(
        \Magento\Quote\Model\Quote\Payment $subject,
        $additionalData
    ) {
        $additionalData[ ] = ["action"=>"hummHumm"];
    }

}

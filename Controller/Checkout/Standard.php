<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;

/**
 * roger.bi@flexigroup.com.au
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class standard extends AbstractAction
{

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        try {
            $quote = $this->_checkoutSession->getQuote();
            $data = $this->getPayload($quote);
            $payload = array(
                'action' => $this->getGatewayConfig()->getGatewayUrl(),
                'fields' => $data
            );

        } catch (Exception $ex) {
            $this->getHummLogger()->log('An exception was encountered in humm/checkout/index: ' . $ex->getMessage());
            $this->getHummLogger()->log($ex->getTraceAsString());
            $this->getMessageManager()->addErrorMessage(__('Unable to start initial humm Checkout.'));
        }
        $result = $this->_resultJsonFactory->create();
        $this->getHummLogger()->log(json_encode($payload));
        return $result->setData($payload);
    }

    /**
     * @param $quote
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPayload($quote)
    {
        if ($quote == null) {
            $this->getHummLogger()->log('Unable to get order from last lodged order id. Possibly related to a failed database call');
            $this->_redirect('checkout/onepage/error', array('_secure' => false));
        }

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();

        $billingAddressParts = preg_split('/\r\n|\r|\n/', $billingAddress->getData('street'));
        $shippingAddressParts = preg_split('/\r\n|\r|\n/', $shippingAddress->getData('street'));

        $quoteId = $quote->getId();
        $magento_version = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        $plugin_version = $this->getGatewayConfig()->getVersion();

        $data = array(
            'x_currency' => $quote->getQuoteCurrencyCode(),
            'x_url_callback' => $this->getDataHelper()->getCompleteUrl(),
            'x_url_complete' => $this->getDataHelper()->getCompleteUrl(),
            'x_url_cancel' => $this->getDataHelper()->getCancelledUrl($quoteId),
            'x_shop_name' => $this->getDataHelper()->getStoreCode(),
            'x_account_id' => $this->getGatewayConfig()->getMerchantNumber(),
            'x_reference' => $quoteId,
            'x_invoice' => $quoteId,
            'x_amount' => $quote->getGrandTotal(),
            'x_customer_first_name' => $quote->getCustomerFirstname(),
            'x_customer_last_name' => $quote->getCustomerLastname(),
            'x_customer_email' => $quote->getData('customer_email'),
            'x_customer_phone' => $billingAddress->getData('telephone'),
            'x_customer_billing_address1' => $billingAddressParts[0],
            'x_customer_billing_address2' => count($billingAddressParts) > 1 ? $billingAddressParts[1] : '',
            'x_customer_billing_city' => $billingAddress->getData('city'),
            'x_customer_billing_state' => $billingAddress->getData('region'),
            'x_customer_billing_zip' => $billingAddress->getData('postcode'),
            'x_customer_shipping_address1' => $shippingAddressParts[0],
            'x_customer_shipping_address2' => count($shippingAddressParts) > 1 ? $shippingAddressParts[1] : '',
            'x_customer_shipping_city' => $shippingAddress->getData('city'),
            'x_customer_shipping_state' => $shippingAddress->getData('region'),
            'x_customer_shipping_zip' => $shippingAddress->getData('postcode'),
            'version_info' => 'Humm_' . $plugin_version . '_on_magento' . substr($magento_version, 0, 3),
            'x_test' => 'false'
        );

        foreach ($data as $key => $value) {
            $data[$key] = preg_replace('/\r\n|\r|\n/', ' ', $value);
        }

        $apiKeyEnc = $this->getGatewayConfig()->getApiKey();
        $apiKey = $this->_encrypted->processValue($apiKeyEnc);
        $signature = $this->getCryptoHelper()->generateSignature($data, $apiKey);
        $data['x_signature'] = $signature;
        $this->getHummLogger()->log('send-data--:' . json_encode($data));
        return $data;
    }

    /**
     * @param $params
     * @param $checkoutUrl
     * @return string
     */
    public function getHummlUrl($params, $checkoutUrl)
    {

        $callUrl = sprintf("%s&%s", $checkoutUrl, $params);
        $this->getHummLogger()->log('url' . $callUrl);
        return sprintf("%s&%s", $checkoutUrl, $params);
    }

}

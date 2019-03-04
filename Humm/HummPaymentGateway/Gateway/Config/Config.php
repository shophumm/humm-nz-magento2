<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Humm\HummPaymentGateway\Gateway\Config;

/**
 * Class Config.
 * Values returned from Magento\Payment\Gateway\Config\Config.getValue()
 * are taken by default from ScopeInterface::SCOPE_STORE
 */
class Config extends \Magento\Payment\Gateway\Config\Config {
    const CODE = 'humm_gateway';

    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_DESCRIPTION = 'description';
    const KEY_GATEWAY_LOGO = 'gateway_logo';
    const KEY_MERCHANT_NUMBER = 'merchant_number';
    const KEY_API_KEY = 'api_key';
    const KEY_GATEWAY_URL = 'gateway_url';
    const KEY_DEBUG = 'debug';
    const KEY_SPECIFIC_COUNTRY = 'specificcountry';
    const KEY_HUMM_APPROVED_ORDER_STATUS = 'humm_approved_order_status';
    const KEY_EMAIL_CUSTOMER = 'email_customer';
    const KEY_AUTOMATIC_INVOICE = 'automatic_invoice';
    const KEY_IS_TESTING = 'is_testing';

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getMerchantNumber() {
        return $this->getValue( self::KEY_MERCHANT_NUMBER );
    }

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getTitle() {
        return $this->getValue( self::KEY_TITLE );
    }

    /**
     * Get Logo
     *
     * @return string
     */
    public function getLogo() {
        return $this->getValue( self::KEY_GATEWAY_LOGO );
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription() {
        return $this->getValue( self::KEY_DESCRIPTION );
    }

    /**
     * Get Main Domain (shophumm or oxiay)
     *
     */
    public function getMainDomain() {
        $launch_time_string = $this->getValue( 'launch_time' );
        $is_after           = ( time() - strtotime( $launch_time_string ) >= 0 );
        $main_domain        = ( $is_after && $this->getSpecificCountry() == 'AU' ) ? 'shophumm' : 'oxipay';

        return $main_domain;
    }

    /**
     * Get Gateway URL
     *
     * @return string
     */
    public function getGatewayUrl() {
        $checkoutUrl = $this->getValue( self::KEY_GATEWAY_URL );
        if ( isset( $checkoutUrl ) and strtolower( substr( $checkoutUrl, 0, 4 ) ) == 'http' ) {
            return $checkoutUrl;
        } else {
            $country_domain = $this->getSpecificCountry() == 'NZ' ? '.co.nz' : '.com.au';  // .com.au is the default value
            $main_domain    = $this->getMainDomain();
            if ( ! $this->isTesting() ) {
                return 'https://secure.' . $main_domain . $country_domain . '/Checkout?platform=Default';
            } else {
                return 'https://securesandbox.' . $main_domain . $country_domain . '/Checkout?platform=Default';
            }
        }
    }

    /**
     * get the Humm refund gateway Url
     * @return string
     */
    public function getRefundUrl() {
        $country_domain = $this->getSpecificCountry() == 'NZ' ? '.co.nz' : '.com.au';  // .com.au is the default value
        $main_domain    = $this->getMainDomain();
        if ( ! $this->isTesting() ) {
            return 'https://portals.' . $main_domain . $country_domain . '/api/ExternalRefund/processrefund';
        } else {
            return 'https://portalssandbox.' . $main_domain . $country_domain . '/api/ExternalRefund/processrefund';
        }
    }

    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey() {
        return $this->getValue( self::KEY_API_KEY );
    }

    /**
     * Get Humm Approved Order Status
     *
     * @return string
     */
    public function getHummApprovedOrderStatus() {
        return $this->getValue( self::KEY_HUMM_APPROVED_ORDER_STATUS );
    }

    /**
     * Check if customer is to be notified
     * @return boolean
     */
    public function isEmailCustomer() {
        return (bool) $this->getValue( self::KEY_EMAIL_CUSTOMER );
    }

    /**
     * Check if customer is to be notified
     * @return boolean
     */
    public function isAutomaticInvoice() {
        return (bool) $this->getValue( self::KEY_AUTOMATIC_INVOICE );
    }

    /**
     * Get Payment configuration status
     * @return bool
     */
    public function isActive() {
        return (bool) $this->getValue( self::KEY_ACTIVE );
    }

    /**
     * Get specific country
     *
     * @return string
     */
    public function getSpecificCountry() {
        return $this->getValue( self::KEY_SPECIFIC_COUNTRY );
    }

    /**
     * Get if doing test transactions (request send to sandbox gateway)
     *
     * @return boolean
     */
    public function isTesting() {
        return (bool) $this->getValue( self::KEY_IS_TESTING );
    }

}

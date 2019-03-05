<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Humm\HummPaymentGateway\Model\Ui;

use Humm\HummPaymentGateway\Gateway\Config\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface {
    const LAUNCH_TIME_URL = 'https://s3-ap-southeast-2.amazonaws.com/widgets.shophumm.com.au/time.txt';
    const LAUNCH_TIME_DEFAULT = "2019-03-31 13:30:00";

    protected $_gatewayConfig;
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_urlBuilder;
    protected $request;
    protected $_assetRepo;
    protected $_resourceConfig;

    public function __construct(
        Config $gatewayConfig,
        Session $customerSession,
        Quote $sessionQuote,
        Context $context,
        Repository $assetRepo,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->_gatewayConfig        = $gatewayConfig;
        $this->_scopeConfigInterface = $context->getScopeConfig();
        $this->customerSession       = $customerSession;
        $this->sessionQuote          = $sessionQuote;
        $this->_urlBuilder           = $context->getUrlBuilder();
        $this->_assetRepo            = $assetRepo;
        $this->_resourceConfig       = $resourceConfig;
    }

    private function updateLaunchDate() {
        $launch_time_string             = $this->_gatewayConfig->getValue( 'payment/humm_gateway/launch_time' );
        $launch_time_update_time_string = $this->_gatewayConfig->getValue( 'payment/humm_gateway/launch_time_updated' );
        if ( empty( $launch_time_string ) || ( time() - $launch_time_update_time_string >= 1 ) ) {
            $remote_launch_time_string = '';
            try {
                $remote_launch_time_string = file_get_contents( self::LAUNCH_TIME_URL );
            } catch ( \Exception $exception ) {
            }
            if ( ! empty( $remote_launch_time_string ) ) {
                $launch_time_string = $remote_launch_time_string;
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time', $launch_time_string, 'default', 0 );
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time_updated', time(), 'default', 0 );
            } elseif ( empty( $launch_time_string ) || ( empty( $launch_time_update_time_string ) && $launch_time_string != self::LAUNCH_TIME_DEFAULT ) ) {
                // this is when $launch_time_string never set (first time run of the plugin), or local const LAUNCH_TIME_DEFAULT changes and and never update from remote.
                // Mainly for development, for changing const LAUNCH_TIME_DEFAULT to take effect.
                $launch_time_string = self::LAUNCH_TIME_DEFAULT;
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time', $launch_time_string, 'default', 0 );
            }
        }
    }

    public function getConfig() {
        $this->updateLaunchDate();
        $logoFile = $this->_gatewayConfig->getLogo();

        /** @var $om \Magento\Framework\ObjectManagerInterface */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $om->get( 'Magento\Framework\App\RequestInterface' );
        $params  = array();
        $params  = array_merge( [ '_secure' => $request->isSecure() ], $params );
        $logo = $this->_assetRepo->getUrlWithParams( 'Humm_HummPaymentGateway::images/' . $logoFile, $params );

        $config = [
            'payment' => [
                Config::CODE => [
                    'title'             => $this->_gatewayConfig->getTitle(),
                    'description'       => $this->_gatewayConfig->getDescription(),
                    'logo'              => $logo,
                    'allowed_countries' => $this->_gatewayConfig->getSpecificCountry(),
                ]
            ]
        ];

        return $config;
    }
}

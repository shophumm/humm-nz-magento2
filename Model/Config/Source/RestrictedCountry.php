<?php

/**
 * Country config field renderer
 */

namespace Humm\HummPaymentGateway\Model\Config\Source;

use Magento\Directory\Model\Config\Source\Country;

/**
 * @author roger.bi@flexigroup.com.au
 * Class RestrictedCountry
 * @package Humm\HummPaymentGateway\Model\Config\Source
 */
class RestrictedCountry extends Country {
    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     */
    public function __construct( \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection ) {
        $countryCollection->addCountryIdFilter( array( 'AU', 'NZ' ) );

        parent::__construct( $countryCollection );
    }
}

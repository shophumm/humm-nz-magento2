<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Humm\HummPaymentGateway\Test\Unit\Gateway\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferInterface;
use Humm\HummPaymentGateway\GateWay\Config\Config;

/**
 * Class TransactionRefundTest
 * @package Humm\HummPaymentGateway\Test\Unit\Gateway\Client
 */

class TransactionRefundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    protected $messageManager;

    /**
     *
     */

    public function setUp()
    {

        $objManager = new ObjectManager($this);

        $config = $this->getMockBuilder(Config::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $config->expects(static::any())->method('getMinTotal')->willReturn(20);
        $this->_clientMock = $objManager->getObject("\Humm\HummPaymentGateway\Model\HummPayment");

    }

    /**
     * @param $expectedRequest
     * @param $expectedResponse
     */
    public function testPlaceRequest( $expectedRequest, $expectedResponse)
    {
        $transferObject = $this->getMockBuilder("\Magento\Payment\Gateway\Http\TransferInterface")->getMock();

        $transferObject->expects(static::any())->method('getBody')->willReturn($expectedRequest);

        static::assertEquals(
            [ 'api_response' => $expectedResponse ],
            $this->_clientMock->placeRequest($transferObject)
        );
    }
}

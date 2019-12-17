<?php

namespace Humm\HummPaymentGateway\Block\System\Config;

use Humm\HummPaymentGateway\Gateway\Config\Config;

/**
 * Class SimplePathAdmin
 * @package Humm\HummPaymentGateway\Block\System\Config
 * @author roger.bi@flexigroup.com.au
 */
class SimplePathAdmin extends \Magento\Framework\View\Element\Template
{

    private $simplePath;

    /**
     * SimplePathAdmin constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Config $simplePath
     * @param array $data
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Humm\HummPaymentGateway\Gateway\Config\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->simplePath = $config;
    }
}

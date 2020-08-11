<?php

namespace Humm\HummPaymentGateway\Block\System\Config\Form;

use Magento\Backend\Block\Template\Context;

/**
 * roger.bi@flexigroup.cxom.au
 * Class SimplepathConfig
 * @package Humm\HummPaymentGateway\Block\System\Config\Form
 */

class SimplepathConfig extends \Magento\Config\Block\System\Config\Form\Field
{

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_layout
            ->createBlock(\Humm\HummPaymentGateway\Block\System\Config\SimplePathAdmin::class)
            ->setTemplate('Humm_HummPaymentGateway::system/config/simplepath_admin.phtml')
            ->setCacheable(false)
            ->toHtml();

        return $html;
    }
}

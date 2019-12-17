<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

/**
 * Class Error
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class Error extends AbstractAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if($this->getHummLogger()) {
            $this->getHummLogger()->log("Error & Cancel  Action...");
        }

        try {
            $page_object = $this->_pageFactory->create();
            $message = __('An error occurred & cancel happen.');
        } catch (\Exception $e) {
            $this->getMessageManager()->addError($this->_helper->__('An error occurred while redirecting to error page.'));
        }
        return $page_object;
    }
}

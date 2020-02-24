<?php
namespace Humm\HummPaymentGateway\Controller\Adminhtml\Order;
/**
 * Class Index
 * @package Humm\HummPaymentGateway\Controller\Adminhtml\Order
 */

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Humm Orders Status')));

        return $resultPage;
    }

}
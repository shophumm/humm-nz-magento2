<?php
namespace Humm\HummPaymentGateWay\Block\Adminhtml;

class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
	/**
	 * constructor
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_controller = 'adminhtml_order';
		$this->_blockGroup = 'Humm_HummPaymentGateway';
		$this->_headerText = __('Humm Orders');
		$this->_addButtonLabel = __('Create New Orders');
		parent::_construct();
	}
}
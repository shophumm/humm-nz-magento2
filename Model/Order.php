<?php
namespace Humm\HummPaymentGateway\Model;
class Order extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'sales_order_status_history';

	protected $_cacheTag = 'sales_order_status_history';

	protected $_eventPrefix = 'sales_order_status_history';

	protected function _construct()
	{
		$this->_init('Humm\HummPaymentGateway\Model\ResourceModel\Order');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
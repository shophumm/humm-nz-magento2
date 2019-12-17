<?php
namespace Humm\HummPaymentGateway\Block\Advert;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use Humm\HummPaymentGateway\Gateway\Config\Config;

/**
 * Class Banner
 * @package Humm\HummPaymentGateway\Block\Advert
 * @author roger.bi@flexigroup.com.au
 * @copyright 
 */


class Slice extends  AbstractAdvert implements CatalogBlock\ShortcutInterface
{

  /**
   * @const string
   */
  const ADVERT_TYPE = "banner";

  /**
   * Render the block if needed
   *
   * @return string
   */

  protected function _toHtml()
  {

    if ($this->_configShow(self::ADVERT_TYPE,$this->getPageType())) {
      return parent::_toHtml();
    }

    return '';
  }

  /**
   * Get shortcut alias
   *
   * @return string
   */
  public function getAlias()
  {
    return $this->_alias;
  }

}

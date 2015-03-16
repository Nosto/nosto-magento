<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about Nosto account billing.
 * This is used during the Nosto account creation.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Account_Billing extends Mage_Core_Model_Abstract implements NostoAccountMetaDataBillingDetailsInterface
{
    /**
     * @var string country ISO (ISO 3166-1 alpha-2) code for billing details.
     */
    protected $_country;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_account_billing');
    }

    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        $this->_country = $store->getConfig('general/country/default');
    }

    /**
     * Sets the account billing details country ISO (ISO 3166-1 alpha-2) code.
     *
     * @param string $country the country ISO code.
     */
    public function setCountry($country)
    {
        $this->_country = $country;
    }

    /**
     * The 2-letter ISO code (ISO 3166-1 alpha-2) for billing details country.
     *
     * @return string the country ISO code.
     */
    public function getCountry()
    {
        return $this->_country;
    }
}

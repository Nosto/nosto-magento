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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Nosto embed script block.
 * Adds JavaScript to the document HEAD that takes care of the meta-data gathering
 * and displaying of recommended products.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Embed extends Mage_Core_Block_Template
{
    const DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';

    /**
     * Render JavaScript that handles the data gathering and displaying of
     * recommended products if the module is enabled for the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()
            || !$helper->existsAndIsConnected()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Gets the account name for the current store scope.
     *
     * @return string the account name or empty string if account is not found.
     */
    public function getAccountName()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        $account = $helper->find();
        if ($account !== null) {
            return $account->name;
        }
        return '';
    }

    /**
     * Gets the Nosto server address.
     * This is either taken from the local environment if exists or else it
     * defaults to "connect.nosto.com".
     *
     * @return string the url.
     */
    public function getServerAddress()
    {
        return Mage::app()->getRequest()->getEnv(
            'NOSTO_SERVER_URL',
            self::DEFAULT_SERVER_ADDRESS
        );
    }
}

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

require_once Mage::getBaseDir('lib') . '/nosto/php-sdk/src/config.inc.php';

/**
 * Helper class for managing Nosto accounts.
 * Includes methods for saving, removing and finding accounts for a specific
 * store.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Account extends Mage_Core_Helper_Abstract
{
    /**
     * Path to store config nosto account name.
     */
    const XML_PATH_ACCOUNT = 'nosto_tagging/settings/account';

    /**
     * Path to store config nosto account tokens.
     */
    const XML_PATH_TOKENS = 'nosto_tagging/settings/tokens';

    /**
     * Saves the account and the associated api tokens for the store view scope.
     *
     * @param NostoAccount               $account the account to save.
     * @param Mage_Core_Model_Store|null $store   the store view to save it for.
     *
     * @return bool true on success, false otherwise.
     */
    public function save(NostoAccount $account, Mage_Core_Model_Store $store = null)
    {
        if ($store === null) {
            $store = Mage::app()->getStore();
        }
        if ((int)$store->getId() < 1) {
            return false;
        }
        /** @var Mage_Core_Model_Config $config */
        $config = Mage::getModel('core/config');
        $config->saveConfig(
            self::XML_PATH_ACCOUNT, $account->getName(), 'stores', $store->getId()
        );
        $tokens = array();
        foreach ($account->getTokens() as $token) {
            $tokens[$token->getName()] = $token->getValue();
        }
        $config->saveConfig(
            self::XML_PATH_TOKENS, json_encode($tokens), 'stores',
            $store->getId()
        );
        Mage::app()->getCacheInstance()->cleanType('config');
        return true;
    }

    /**
     * Removes an account with associated api tokens for the store view scope.
     *
     * @param NostoAccount               $account the account to remove.
     * @param Mage_Core_Model_Store|null $store   the store view to remove it for.
     *
     * @return bool true on success, false otherwise.
     */
    public function remove(NostoAccount $account, Mage_Core_Model_Store $store = null)
    {
        if ($store === null) {
            $store = Mage::app()->getStore();
        }
        if ((int)$store->getId() < 1) {
            return false;
        }
        /** @var Mage_Core_Model_Config $config */
        $config = Mage::getModel('core/config');
        $config->saveConfig(
            self::XML_PATH_ACCOUNT, null, 'stores', $store->getId()
        );
        $config->saveConfig(
            self::XML_PATH_TOKENS, null, 'stores', $store->getId()
        );
        Mage::app()->getCacheInstance()->cleanType('config');

        try {
            // Notify Nosto that the account was deleted.
            $account->delete();
        } catch (NostoException $e) {
            // Failures are logged but not shown to the user.
            Mage::log(
                "\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log'
            );
        }

        return true;
    }

    /**
     * Returns the account with associated api tokens for the store view scope.
     *
     * @param Mage_Core_Model_Store|null $store the account store view.
     *
     * @return NostoAccount|null the account or null if not found.
     */
    public function find(Mage_Core_Model_Store $store = null)
    {
        if ($store === null) {
            $store = Mage::app()->getStore();
        }
        $accountName = $store->getConfig(self::XML_PATH_ACCOUNT);
        if (!empty($accountName)) {
            $account = new NostoAccount($accountName);
            $tokens = json_decode(
                $store->getConfig(self::XML_PATH_TOKENS), true
            );
            if (is_array($tokens) && !empty($tokens)) {
                foreach ($tokens as $name => $value) {
                    $token = new NostoApiToken($name, $value);
                    $account->addApiToken($token);
                }
            }
            return $account;
        }
        return null;
    }

    /**
     * Checks that an account exists for the given store and that it is
     * connected to nosto.
     *
     * @param Mage_Core_Model_Store $store the store to check the account for.
     *
     * @return bool true if exists and is connected, false otherwise.
     */
    public function existsAndIsConnected(Mage_Core_Model_Store $store = null)
    {
        $account = $this->find($store);
        return ($account !== null && $account->isConnectedToNosto());
    }

    /**
     * Returns the meta data model needed for creating a new nosto account
     * using the Nosto SDk.
     *
     * @param Mage_Core_Model_Store $store the store view to get the data for.
     *
     * @return Nosto_Tagging_Model_Meta_Account the meta data instance.
     */
    public function getMetaData(Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Model_Meta_Account $meta */
        $meta = Mage::getModel('nosto_tagging/meta_account');
        $meta->loadData($store);
        return $meta;
    }

    /**
     * Returns the account administration iframe url.
     * If there is no account the "front page" url will be returned where an
     * account can be created from.
     *
     * @param Mage_Core_Model_Store $store the store view to get the url for.
     * @param NostoAccount $account the Nosto account to get the iframe url for.
     * @param array $params optional extra params for the url.
     *
     * @return string the iframe url.
     */
    public function getIframeUrl(Mage_Core_Model_Store $store, NostoAccount $account = null, array $params = array())
    {
        /** @var Nosto_Tagging_Model_Meta_Account_Iframe $meta */
        $meta = Mage::getModel('nosto_tagging/meta_account_iframe');
        $meta->loadData($store);
        return Nosto::helper('iframe')->getUrl($meta, $account, $params);
    }
}

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
 * Nosto iframe block.
 * Adds an iframe for configuring a Nosto account.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_Iframe extends Mage_Adminhtml_Block_Template
{
    const DEFAULT_IFRAME_ORIGIN_REGEXP = '(https:\/\/magento-([a-z0-9]+)\.hub\.nosto\.com)|(https:\/\/my\.nosto\.com)';

    /**
     * @var string the iframe url if SSO to Nosto can be made.
     */
    private $_iframeUrl;

    /**
     * @var Mage_Core_Model_Store the currently selected store view.
     */
    private $_store;

    /**
     * Gets the iframe url for the account settings page from Nosto.
     * This url is only returned if the current admin user can be logged in
     * with SSO to Nosto.
     *
     * @return string the iframe url or empty string if it cannot be created.
     */
    public function getIframeUrl()
    {
        if ($this->_iframeUrl !== null) {
            return $this->_iframeUrl;
        }
        $params = array();
        // Pass any error/success messages we might have to the iframe.
        // These can be available when getting redirect back from the OAuth
        // front controller after connecting a Nosto account to a store.
        $session = Mage::getSingleton('adminhtml/session');
        if ($session !== null) {
            $nostoMessage = $session->getData('nosto_message');
            if (!empty($nostoMessage)) {
                if (isset($nostoMessage['type'], $nostoMessage['code'])) {
                    $params['message_type'] = $nostoMessage['type'];
                    $params['message_code'] = $nostoMessage['code'];
                    if (isset($nostoMessage['text'])) {
                        $params['message_text'] = $nostoMessage['text'];
                    }
                }
                $session->setData('nosto_message', null);
            }
        }
        $store = $this->getSelectedStore();
        $account = Mage::helper('nosto_tagging/account')->find($store);
        return $this->_iframeUrl = Mage::helper('nosto_tagging/account')
            ->getIframeUrl($store, $account, $params);
    }

    /**
     * Returns the currently selected store view.
     *
     * @return Mage_Core_Model_Store the store view model.
     *
     * @throws Exception if store view cannot be found.
     */
    public function getSelectedStore()
    {
        if ($this->_store !== null) {
            return $this->_store;
        }

        if (Mage::app()->isSingleStoreMode()) {
            $store = Mage::app()->getStore(true);
        } elseif (($id = (int)$this->getRequest()->getParam('store')) !== 0) {
            $store = Mage::app()->getStore($id);
        } else {
            throw new Exception('Failed to find currently selected store view.');
        }

        return $this->_store = $store;
    }

    /**
     * Returns the valid origin url from where the iframe should accept
     * postMessage calls.
     * This is configurable to support different origins based on $_ENV.
     *
     * @return string the origin url.
     */
    public function getIframeOrigin()
    {
        return (string)Mage::app()->getRequest()
            ->getEnv('NOSTO_IFRAME_ORIGIN_REGEXP', self::DEFAULT_IFRAME_ORIGIN_REGEXP);
    }
}

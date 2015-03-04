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
    const DEFAULT_ADMIN_IFRAME_ORIGIN = 'https://my.nosto.com';

    /**
     * @var string the iframe url if SSO to Nosto can be made.
     */
    private $_iframeUrl;

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
        // Pass any error/success messages we might have to the iframe.
        // These can be available when getting redirect back from the OAuth
        // front controller after connecting a Nosto account to a store.
        $params = array();
        if (($type = $this->getRequest()->getParam('message_type')) !== null) {
            $params['message_type'] = $type;
        }
        if (($code = $this->getRequest()->getParam('message_code')) !== null) {
            $params['message_code'] = $code;
        }
        return $this->_iframeUrl = Mage::helper('nosto_tagging/account')
            ->getIframeUrl(
                Mage::helper('nosto_tagging/account')->find(),
                $params
            );
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
            ->getEnv('NOSTO_IFRAME_ORIGIN', self::DEFAULT_ADMIN_IFRAME_ORIGIN);
    }
}

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
 * Meta data class which holds information to be sent to the Nosto account
 * configuration iframe.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Account_Iframe extends NostoIframe
{
    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');

        $this->_language = new NostoLanguageCode(
            substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2)
        );
        $this->_shopLanguage = new NostoLanguageCode(
            substr($store->getConfig('general/locale/code'), 0, 2)
        );
        $this->_uniqueId = $dataHelper->getInstallationId();
        $this->_previewUrlProduct = $urlHelper->getPreviewUrlProduct($store);
        $this->_previewUrlCategory = $urlHelper->getPreviewUrlCategory($store);
        $this->_previewUrlSearch = $urlHelper->getPreviewUrlSearch($store);
        $this->_previewUrlCart = $urlHelper->getPreviewUrlCart($store);
        $this->_previewUrlFront = $urlHelper->getPreviewUrlFront($store);
        $this->_shopName = $store->getName();
    }

    /**
     * The version number of the platform the e-commerce installation is
     * running on.
     *
     * @return string the platform version.
     */
    public function getVersionPlatform()
    {
        return Mage::getVersion();
    }

    /**
     * The version number of the Nosto module/extension running on the
     * e-commerce installation.
     *
     * @return string the module version.
     */
    public function getVersionModule()
    {
        // Path is hard-coded to be like in "etc/config.xml".
        return (string)Mage::getConfig()
            ->getNode('modules/Nosto_Tagging/version');
    }
}

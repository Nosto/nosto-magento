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
 * @copyright Copyright (c) 2013-2019 Nosto Solutions Ltd (http://www.nosto.com)
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
class Nosto_Tagging_Model_Meta_Account_Iframe extends Nosto_Object_Iframe
{

    const PLATFORM = 'magento';

    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     * @return bool
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        /** @var Mage_Admin_Model_User $user */
        /** @noinspection PhpUndefinedMethodInspection */
        $user = Mage::getSingleton('admin/session')->getUser();
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');

        $this->setFirstName($user->getFirstname());
        $this->setLastName($user->getLastname());
        $this->setEmail($user->getEmail());
        $this->setPlatform(Nosto_Tagging_Model_Meta_Account_Iframe::PLATFORM);
        $this->setVersionModule((string)Mage::getConfig()->getNode('modules/Nosto_Tagging/version'));
        $this->setVersionPlatform(Mage::getVersion());
        $this->setLanguageIsoCode(substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));
        $this->setLanguageIsoCodeShop(substr($store->getConfig('general/locale/code'), 0, 2));
        $this->setUniqueId($dataHelper->getInstallationId());
        $this->setPreviewUrlProduct($urlHelper->getPreviewUrlProduct($store));
        $this->setPreviewUrlCategory($urlHelper->getPreviewUrlCategory($store));
        $this->setPreviewUrlSearch($urlHelper->getPreviewUrlSearch($store));
        $this->setPreviewUrlCart($urlHelper->getPreviewUrlCart($store));
        $this->setPreviewUrlFront($urlHelper->getPreviewUrlFront($store));
        $this->setShopName($store->getName());
        $this->setModules($moduleHelper->getModulesForIntegration());

        return true;
    }
}

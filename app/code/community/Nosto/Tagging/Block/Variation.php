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
 * Current variation tagging block.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Variation extends Mage_Core_Block_Template
{
    /**
     * Render variation string as hidden meta data if the module is enabled for
     * the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if (!$helper->existsAndIsConnected()
            || !$this->useMultiCurrencyOrPriceVariation()
            || !$moduleHelper->isModuleEnabled()
        ) {
            return '';
        }

        return (new Nosto_Object_MarkupableString(
            $this->getVariationId(),
            'nosto_variation'
        ))->toHtml();
    }

    /**
     * Return the current variation id
     *
     * @return string|null the identifier of the current variation
     */
    public function getVariationId()
    {
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        if ($dataHelper->isMultiCurrencyMethodExchangeRate($dataHelper->getStore())) {
            return $dataHelper->getStore()->getCurrentCurrencyCode();
        } elseif ($dataHelper->isVariationEnabled($dataHelper->getStore())) {
            /** @var Mage_Customer_Model_Session $sessionModel */
            $sessionModel = Mage::getSingleton('customer/session');
            $groupId = $sessionModel->getCustomerGroupId();
            /** @var Mage_Customer_Model_Group $customerGroup */
            $customerGroup = Mage::getModel('customer/group')->load($groupId);
            if ($customerGroup instanceof Mage_Customer_Model_Group) {
                /* @var Nosto_Tagging_Helper_Variation $variationHelper */
                $variationHelper = Mage::helper('nosto_tagging/variation');
                return $variationHelper->generateVariationId($customerGroup);
            }
        }

        return null;
    }

    /**
     * Tells if store uses multiple currencies
     *
     * @return bool
     */
    public function useMultiCurrencyOrPriceVariation()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');

        $enabled = $helper->isMultiCurrencyMethodExchangeRate($helper->getStore());
        if (!$enabled) {
            $enabled = $helper->isVariationEnabled($helper->getStore());
        }

        return $enabled;
    }
}

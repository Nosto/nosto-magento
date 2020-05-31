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

use Nosto_Tagging_Model_Meta_Cart_Builder as CartBuilder;

/**
 * Meta data class which holds information about an cart.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Cart extends Nosto_Object_Cart_Cart
{

    /**
     * Loads the order info from a Magento quote model.
     *
     * @param Mage_Sales_Model_Quote $quote the quote model.
     * @return bool
     */
    public function loadData(Mage_Sales_Model_Quote $quote)
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $store = $helper->getStore();
        $currencyCode = $store->getCurrentCurrencyCode();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $nostoItem = CartBuilder::buildItem($item, $currencyCode);
            if ($nostoItem instanceof Nosto_Types_LineItemInterface) {
                $this->addItem($nostoItem);
            }
        }

        $this->amendRestoreCartUrl($quote, $store);
        $dataHelper = Mage::helper('nosto_tagging/data');
        /* @var Nosto_Tagging_Helper_Data $dataHelper */
        $this->setHcid($dataHelper->getVisitorChecksum());
        Mage::dispatchEvent(
            Nosto_Tagging_Helper_Event::EVENT_NOSTO_CART_LOAD_AFTER,
            array(
                'cart' => $this,
                'magentoQuote' => $quote
            )
        );

        return true;
    }

    /**
     * Populates the restore cart link
     *
     * @param Mage_Sales_Model_Quote $quote the quote model.
     * @param Mage_Core_Model_Store $store
     */
    public function amendRestoreCartUrl(Mage_Sales_Model_Quote $quote, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Model_Customer $nostoCustomer */
        $nostoCustomer = Mage::getModel('nosto_tagging/customer')
            ->getCollection()
            ->addFieldToFilter('quote_id', $quote->getId())
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem(); // @codingStandardsIgnoreLine

        if ($nostoCustomer->getRestoreCartHash()) {
            /* @var Nosto_Tagging_Helper_Url $urlHelper */
            $urlHelper = Mage::helper('nosto_tagging/url');
            $link = $urlHelper->generateRestoreCartUrl(
                $nostoCustomer->getRestoreCartHash(),
                $store
            );
            $this->setRestoreLink($link);
        }
    }
}

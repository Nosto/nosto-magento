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
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handles sending the order confirmations to Nosto via the API.
 *
 * Order confirmations can be sent two different ways:
 * - matched orders; where we know the Nosto customer ID of the user who placed the order
 * - un-matched orders: where we do not know the Nosto customer ID of the user who placed the order
 *
 * The second option is a fallback and should be avoided as much as possible.
 */
class Nosto_Tagging_Model_Service_Order
{

    /**
     * Sends an order confirmation to Nosto and also batch updates all products
     * that were included in the order.
     *
     * @param Mage_Sales_Model_Order $mageOrder
     * @return bool
     * @throws Nosto_NostoException
     * @throws Mage_Core_Exception
     */
    public function confirm(Mage_Sales_Model_Order $mageOrder)
    {
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Class $classHelper */
        $classHelper = Mage::helper('nosto_tagging/class');
        /** @var Nosto_Tagging_Model_Meta_Order $order */
        $order = $classHelper->getOrderClass($mageOrder);
        $order->loadData($mageOrder);
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $store = $mageOrder->getStore();
        $account = $accountHelper->find($store);
        /** @var Nosto_Tagging_Helper_Customer $customerHelper */
        $customerHelper = Mage::helper('nosto_tagging/customer');
        $customerId = $customerHelper->getNostoId($mageOrder);
        if ($account !== null && $account->isConnectedToNosto()) {
            $urlHelper = Mage::helper('nosto_tagging/url');
            $operation = new Nosto_Operation_OrderConfirm($account, $urlHelper->getActiveDomain($store));
            $operation->send($order, $customerId);
            if ($dataHelper->getUseInventoryLevel($store)
                && $dataHelper->getSendInventoryLevelAfterPurchase($store)
            ) {
                $this->syncInventoryLevel($order);
            }
        }

        return true;
    }

    /**
     * Sends product updates to Nosto to keep up with the inventory level
     *
     * @param Nosto_Tagging_Model_Meta_Order $order
     * @throws Mage_Core_Exception
     */
    public function syncInventoryLevel(Nosto_Tagging_Model_Meta_Order $order)
    {
        $purchasedItems = $order->getPurchasedItems();
        $productIds = array();
        /* @var Nosto_Tagging_Model_Meta_Order_Item $item */
        foreach ($purchasedItems as $item) {
            $productId = $item->getProductId();
            if (empty($productId) || $productId < 0) {
                continue;
            }

            $productIds[] = $productId;
        }

        if (!empty($productIds)) {
            /* @var Nosto_Tagging_Model_Resource_Product_Collection $productIds*/
            $products = Mage::getModel('nosto_tagging/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addIdFilter($productIds);
            if ($products instanceof Nosto_Tagging_Model_Resource_Product_Collection
                && !empty($products)
            ) {
                /* @var Nosto_Tagging_Model_Service_Product $productService */
                $productService = Mage::getModel(
                    'nosto_tagging/service_product'
                );
                $productService->updateBatch($products);
            }
        }
    }
}

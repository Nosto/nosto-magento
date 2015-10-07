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
 * Order collection for historical data exports.
 */
class Nosto_Tagging_Model_Export_Collection_Order extends NostoOrderCollection implements NostoExportCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function getJson()
    {
        $array = array();
        /** @var Nosto_Tagging_Model_Meta_Order $item */
        foreach ($this->getArrayCopy() as $item) {
            $data = array(
                'order_number' => $item->getOrderNumber(),
                'external_order_ref' => $item->getExternalOrderRef(),
                'order_status_code' => $item->getOrderStatus()->getCode(),
                'order_status_label' => $item->getOrderStatus()->getLabel(),
                'order_statuses' => array(),
                'created_at' => Nosto::helper('date')->format($item->getCreatedDate()),
                'buyer' => array(
                    'first_name' => $item->getBuyerInfo()->getFirstName(),
                    'last_name' => $item->getBuyerInfo()->getLastName(),
                    'email' => $item->getBuyerInfo()->getEmail(),
                ),
                'payment_provider' => $item->getPaymentProvider(),
                'purchased_items' => array(),
            );
            foreach ($item->getPurchasedItems() as $orderItem) {
                $data['purchased_items'][] = array(
                    'product_id' => $orderItem->getProductId(),
                    'quantity' => (int)$orderItem->getQuantity(),
                    'name' => $orderItem->getName(),
                    'unit_price' => Nosto::helper('price')->format($orderItem->getUnitPrice()),
                    'price_currency_code' => strtoupper($orderItem->getCurrencyCode()),
                );
            }
            foreach ($item->getOrderStatuses() as $status) {
                if (!isset($data['order_statuses'][$status->getCode()])) {
                    $data['order_statuses'][$status->getCode()] = array();
                }
                $data['order_statuses'][$status->getCode()][] =
                    date('Y-m-d\TH:i:s\Z', strtotime($status->getCreatedAt()));
            }
            $array[] = $data;
        }
        return json_encode($array);
    }
}

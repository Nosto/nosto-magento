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

        /** @var NostoHelperDate $dateHelper */
        $dateHelper = Nosto::helper('date');
        /** @var NostoHelperPrice $priceHelper */
        $priceHelper = Nosto::helper('price');

        /** @var Nosto_Tagging_Model_Meta_Order $item */
        foreach ($this->getArrayCopy() as $item) {
            $data = array(
                'order_number' => $item->getOrderNumber(),
                'external_order_ref' => $item->getExternalOrderRef(),
                'order_statuses' => array(),
                'created_at' => $dateHelper->format($item->getCreatedDate()),
                'buyer' => array(),
                'payment_provider' => $item->getPaymentProvider(),
                'purchased_items' => array(),
            );
            if ($item->getOrderStatus()) {
                $data['order_status_code'] = $item->getOrderStatus()->getCode();
                $data['order_status_label'] = $item->getOrderStatus()->getLabel();
            }
            foreach ($item->getPurchasedItems() as $orderItem) {
                $data['purchased_items'][] = array(
                    'product_id' => $orderItem->getProductId(),
                    'quantity' => (int)$orderItem->getQuantity(),
                    'name' => $orderItem->getName(),
                    'unit_price' => $priceHelper->format($orderItem->getUnitPrice()),
                    'price_currency_code' => strtoupper($orderItem->getCurrencyCode()),
                );
            }
            foreach ($item->getOrderStatuses() as $status) {
                if ($status->getCreatedAt()) {
                    if (!isset($data['order_statuses'][$status->getCode()])) {
                        $data['order_statuses'][$status->getCode()] = array();
                    }
                    $data['order_statuses'][$status->getCode()][] =
                        date('Y-m-d\TH:i:s\Z', strtotime($status->getCreatedAt()));
                }
            }
            if ($item->getBuyerInfo()) {
                if ($item->getBuyerInfo()->getFirstName()) {
                    $data['buyer']['first_name'] = $item->getBuyerInfo()->getFirstName();
                }
                if ($item->getBuyerInfo()->getLastName()) {
                    $data['buyer']['last_name'] = $item->getBuyerInfo()->getLastName();
                }
                if ($item->getBuyerInfo()->getEmail()) {
                    $data['buyer']['email'] = $item->getBuyerInfo()->getEmail();
                }
            }

            $array[] = $data;
        }
        return json_encode($array);
    }
}

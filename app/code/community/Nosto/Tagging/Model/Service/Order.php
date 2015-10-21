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
     * Sends an order confirmation to Nosto.
     *
     * @param Nosto_Tagging_Model_Meta_Order $order the order to confirm.
     * @param NostoAccount $account the Nosto account object.
     * @param null $customerId the Nosto customer ID of the user who placed the order.
     * @throws NostoException on failure.
     * @return true on success.
     */
    public function confirm(Nosto_Tagging_Model_Meta_Order $order, NostoAccount $account, $customerId = null)
    {
        $request = $this->initApiRequest($account, $customerId);
        $response = $request->post($this->getOrderAsJson($order));
        if ($response->getCode() !== 200) {
            Nosto::throwHttpException('Failed to send order confirmation to Nosto.', $request, $response);
        }
        return true;
    }

    /**
     * Builds the API request and returns it.
     *
     * @param NostoAccount $account the Nosto account object.
     * @param string|null $customerId the Nosto customer ID of the user who placed the order.
     * @return NostoApiRequest the request object.
     */
    protected function initApiRequest(NostoAccount $account, $customerId)
    {
        $request = new NostoApiRequest();
        $request->setContentType('application/json');
        if (!empty($customerId)) {
            $request->setPath(NostoApiRequest::PATH_ORDER_TAGGING);
            $request->setReplaceParams(array('{m}' => $account->getName(), '{cid}' => $customerId));
        } else {
            $request->setPath(NostoApiRequest::PATH_UNMATCHED_ORDER_TAGGING);
            $request->setReplaceParams(array('{m}' => $account->getName()));
        }
        return $request;
    }

    /**
     * Turns an order object into a JSON structure.
     *
     * @param Nosto_Tagging_Model_Meta_Order $order the order object.
     * @return string the JSON structure.
     */
    protected function getOrderAsJson(Nosto_Tagging_Model_Meta_Order $order)
    {
        $data = array(
            'order_number' => $order->getOrderNumber(),
            'external_order_ref' => $order->getExternalOrderRef(),
            'order_status_code' => $order->getOrderStatus()->getCode(),
            'order_status_label' => $order->getOrderStatus()->getLabel(),
            'buyer' => array(),
            'created_at' => Nosto::helper('date')->format($order->getCreatedDate()),
            'payment_provider' => $order->getPaymentProvider(),
            'purchased_items' => array(),
        );
        foreach ($order->getPurchasedItems() as $item) {
            $data['purchased_items'][] = array(
                'product_id' => $item->getProductId(),
                'quantity' => (int)$item->getQuantity(),
                'name' => $item->getName(),
                'unit_price' => Nosto::helper('price')->format($item->getUnitPrice()),
                'price_currency_code' => strtoupper($item->getCurrencyCode()),
            );
        }
        if ($order->getBuyerInfo()->getFirstName()) {
            $data['buyer']['first_name'] = $order->getBuyerInfo()->getFirstName();
        }
        if ($order->getBuyerInfo()->getLastName()) {
            $data['buyer']['last_name'] = $order->getBuyerInfo()->getLastName();
        }
        if ($order->getBuyerInfo()->getEmail()) {
            $data['buyer']['email'] = $order->getBuyerInfo()->getEmail();
        }
        return json_encode($data);
    }
}

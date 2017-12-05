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
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../bootstrap.php'; // @codingStandardsIgnoreLine
require_once 'Mage/Checkout/controllers/CartController.php';
use Nosto_NostoException as NostoException;

/**
 * Restores an abandoned cart
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_AddToCartController extends Mage_Checkout_CartController
{

    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $cart = $this->_getCart();
        $skuId = $this->getRequest()->getParam('sku');
        try {
            /* @var Mage_Catalog_Model_Product $product */
            $product = $this->_initProduct();
            if (!$product
            || $product->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            || empty($skuId)
            ) {
                return $this->_goBack();
            }
            /** @var Mage_Catalog_Model_Product_Type_Configurable $parentType */
            $parentType = $product->getTypeInstance();
            $attributeOptions = array();
            if ($parentType instanceof Mage_Catalog_Model_Product_Type_Configurable) {
                $skuProduct = Mage::getModel('catalog/product')->load($skuId);
                $configurableAttributes = $parentType->getConfigurableAttributesAsArray($product);
                foreach ($configurableAttributes as $configurableAttribute) {
                    $attributeCode = $configurableAttribute['attribute_code'];
                    $attribute = $skuProduct->getResource()->getAttribute($attributeCode);
                    if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
                        $attributeId = $attribute->getId();
                        $attributeValueId = $skuProduct->getData($attributeCode);

                        if ($attributeId && $attributeValueId) {
                            $attributeOptions[$attributeId] = $attributeValueId;
                        }
                    }
                }
            }

            if (empty($attributeOptions)) {
                $this->_getSession()->addError($this->__('Cannot add the item to shopping cart.'));
                return $this->_goBack();
            }
            $params = array('super_attribute' => $attributeOptions);

            /* Below is cannibalized from parent */
            $cart->addProduct($product, $params);
            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__(
                        '%s was added to your shopping cart.',
                        Mage::helper('core')->escapeHtml($product->getName())
                    );
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
}

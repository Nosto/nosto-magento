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

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

/** @noinspection PhpIncludeInspection */
require_once 'Mage/Checkout/controllers/CartController.php';

/**
 * Restores an abandoned cart
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_AddToCartController extends Mage_Checkout_CartController
{
    /**
     * Add item to cart action
     *
     * @return Mage_Checkout_CartController|Mage_Core_Controller_Varien_Action
     * @throws Mage_Exception
     * @throws Mage_Core_Exception
     */
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            /** @noinspection PhpUnhandledExceptionInspection */
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
                    /** @var \Mage_Catalog_Model_Resource_Product $productResource */
                    $productResource = $skuProduct->getResource();
                    $attribute = $productResource->getAttribute($attributeCode);
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
            $qty = $this->getRequest()->getParam('qty');
            if ($qty !== null) {
                $params['qty'] = $qty;
            }
            /* Below is cannibalized from parent */
            $cart->addProduct($product, $params);
            $cart->save();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            /** @noinspection PhpUndefinedMethodInspection */
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                /** @noinspection PhpUndefinedMethodInspection */
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__(
                        '%s was added to your shopping cart.',
                        Mage::helper('core')->escapeHtml($product->getName())
                    );
                    $this->_getSession()->addSuccess($message);
                }
                return $this->_goBack();
            }
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            return $this->_goBack();
        }
        return $this;
    }

    /**
     * @return Mage_Checkout_CartController|Nosto_Tagging_AddToCartController
     * @throws Mage_Core_Exception
     * @throws Mage_Exception
     */
    public function addMultipleProductsToCartAction()
    {
        if (!$this->_validateFormKey()) {
            /** @noinspection PhpUnhandledExceptionInspection */
            Mage::throwException('Invalid form key');
        }

        $cart = $this->_getCart();

        $products = explode(',', $this->getRequest()->getParam('product'));
        $skus = explode(',', $this->getRequest()->getParam('skus'));

//
$debugger = true;
        foreach ($products as $key => $product) {
            try {
                /* @var Mage_Catalog_Model_Product $product */
                $product = $this->initProductById($product);
                if (!$product) {
                    return $this->_goBack();
                }
                /** @var Mage_Catalog_Model_Product_Type_Configurable $parentType */
                $parentType = $product->getTypeInstance();
                // We need to get the SKU parent, then load the SKU again to get the super attributes
                $attributeOptions = array();
//                $skuProduct = Mage::getModel('catalog/product')->load($skuId);
                if ($parentType instanceof Mage_Catalog_Model_Product_Type_Configurable) {
                    $configurableParent = $product->getTypeInstance()->getParentIdsByChild($product->getId());
                    $configurableAttributes = $parentType->getConfigurableAttributesAsArray($product);
                    foreach ($configurableAttributes as $configurableAttribute) {
                        $attributeCode = $configurableAttribute['attribute_code'];
                        /** @var \Mage_Catalog_Model_Resource_Product $productResource */
                        $productResource = $product->getResource();
                        $attribute = $productResource->getAttribute($attributeCode);
                        if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
                            $attributeId = $attribute->getId();
                            $attributeValueId = $product->getData($attributeCode);
                            if ($attributeId && $attributeValueId) {
                                $attributeOptions[$attributeId] = $attributeValueId;
                            }
                        }
                    }
                    if (empty($attributeOptions)) {
                        $this->_getSession()->addError($this->__('Cannot add the item to shopping cart.'));
                        return $this->_goBack();
                    }
                    $params = array('super_attribute' => $attributeOptions);
                } elseif ($parentType instanceof Mage_Catalog_Model_Product_Type_Simple){

                }

                $qty = $this->getRequest()->getParam('qty');
                if ($qty !== null) {
                    $params['qty'] = $qty;
                }
                /* Below is cannibalized from parent */
                $cart->addProduct($product, $params);

                /** @noinspection PhpUndefinedMethodInspection */
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    if (!$cart->getQuote()->getHasError()) {
                        $message = $this->__(
                            '%s was added to your shopping cart.',
                            Mage::helper('core')->escapeHtml($product->getName())
                        );
                        $this->_getSession()->addSuccess($message);
                    }

                }
            } catch (\Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                Mage::logException($e);
//                return $this->_goBack(); //@todo maybe add notices but keep adding as much products as possible
            }
        }
        $cart->save();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->_getSession()->setCartWasUpdated(true);
//        if (!$product) {
//            $product = $this->_initProduct();
//        }
        Mage::dispatchEvent(
            'checkout_cart_add_product_complete',
            array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
        );
        return $this->_goBack();
//        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @param $productId
     * @return bool|Mage_Catalog_Model_Product
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function initProductById($productId)
    {
        /* @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        if ($product->getId()) {
            return $product;
        }
        return false;
    }

    /**
     * Returns an array with a list of super_attributes for a parent product and his SKU
     *
     * @param Product $product
     * @param Product $skuProduct
     * @param ConfigurableType $configurableType
     * @return array
     */
    private function getAttributeOptions(Product $product, Product $skuProduct, ConfigurableType $configurableType)
    {
        $configurableAttributes = $configurableType->getConfigurableAttributesAsArray($product);
        $attributeOptions = [];
        $skuResource = $this->productResourceModel->load($skuProduct, $skuProduct->getId());
        foreach ($configurableAttributes as $configurableAttribute) {
            $attributeCode = $configurableAttribute['attribute_code'];
            $attribute = $skuResource->getAttribute($attributeCode);
            if ($attribute instanceof MageAttribute) {
                $attributeId = $attribute->getId();
                $attributeValueId = $skuProduct->getData($attributeCode);
                if ($attributeId && $attributeValueId) {
                    $attributeOptions[$attributeId] = $attributeValueId;
                }
            }
        }
        return $attributeOptions;
    }

}

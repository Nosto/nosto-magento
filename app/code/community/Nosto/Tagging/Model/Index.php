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
 * Model for Nosto product index
 *
 * @method void setStoreId($storeId)
 * @method string getStoreId()
 * @method void setProductId($productId)
 * @method int getProductId()
 * @method void setSerializedProduct($serializedObject)
 * @method string getSerializedProduct()
 * @method void setInSync($inSync)
 * @method string getInSync()
 * @method void setUpdatedAt($updatedAt)
 * @method string getUpdatedAt()
 * @method void setCreatedAt($createdAt)
 * @method string getCreatedAt()
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Index extends Mage_Core_Model_Abstract
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/index');
    }

    /**
     * @return Nosto_Tagging_Model_Meta_Product|false
     */
    public function getNostoMetaProduct()
    {
        // @codingStandardsIgnoreLine
        return unserialize($this->getSerializedProduct());
    }

    /**
     * @param Nosto_Tagging_Model_Meta_Product $product
     */
    public function setNostoMetaProduct(Nosto_Tagging_Model_Meta_Product $product)
    {
        // @codingStandardsIgnoreLine
        $this->setSerializedProduct(serialize($product));
    }
}

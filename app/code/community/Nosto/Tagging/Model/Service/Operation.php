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

/**
 * Handles sending product updates to Nosto via the API.
 *
 */
class Nosto_Tagging_Model_Service_Operation extends Nosto_Operation_UpsertProduct
{
    private $added = 0;

    /**
     * @inheritdoc
     */
    public function addProduct(Nosto_Types_Product_ProductInterface $product)
    {
        //Skip the product url has the string '_ignore_category'
        //If the flat catalog is enabled, and a new product is added to the catalog, then the product
        //url contains '_ignore_category' because it fail to build the url properly.
        //Nosto should not recommend this product because it is yet available in the frontend
        if (!is_string($product->getUrl())
            || strpos(
                $product->getUrl(),
                Nosto_Tagging_Helper_Url::MAGENTO_URL_OPTION_IGNORE_CATEGORY
            ) !== false
        ) {
            Nosto_Tagging_Helper_Log::error(
                'Skip product (%s) upsert since the url contains "_ignore_category".',
                array($product->getProductId())
            );
            return;
        }
        parent::addProduct($product);
        $this->added ++;
    }

    /**
     * @inheritdoc
     */
    public function upsert()
    {
        if ($this->added > 0) {
            parent::upsert();
        }
    }
}

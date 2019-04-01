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
 * Extension system setting source model for choosing where visitor should be redirected
 * after the cart has been restored.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_System_Config_Source_Restore_Cart_Location
{
    /**
     * Returns the path options to choose from.
     *
     * @return array the options.
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Nosto_Tagging_Helper_Url::MAGENTO_PATH_CART,
                'label' => 'Cart page',
            ),
            array(
                'value' => Nosto_Tagging_Helper_Url::MAGENTO_PATH_ONEPAGE_CHEKOUT,
                'label' => 'One page checkout',
            )
        );
    }
}

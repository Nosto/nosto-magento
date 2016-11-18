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
 * Helper class for Klarna related operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Klarna extends Mage_Core_Helper_Abstract
{
    const PATH_VAIMO_KLARNA_CREATE_ORDER_ON_SUCCESS = 'create_order_on_success';
    const PATH_PREFIX_VAIMO_KLARNA = 'payment/vaimo_klarna_checkout/';
    const MODULE_NAME = 'Vaimo_Klarna';

    /**
     * Returns if Klarna creates orders for Klarna orders
     *
     * @param Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function getSettingCreateOrderForVaimoKlarna(Mage_Core_Model_Store $store = null)
    {
        return $this->getKlarnaConfig(
            self::PATH_VAIMO_KLARNA_CREATE_ORDER_ON_SUCCESS,
            $store
        );
    }

    public function getVaimoKlarnaConfig($path, Mage_Core_Model_Store $store)
    {
        $fullPath = sprintf(
            '%s%s',
            self::PATH_PREFIX_VAIMO_KLARNA,
            $path
        );

        return Mage::getStoreConfig($fullPath, $store);
    }
}

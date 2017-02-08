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
 * Helper class for loading plugable classes.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Class extends Mage_Core_Helper_Abstract
{
    /*
     * Loads correct / plugable order class based on payment provider
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return NostoOrderInterface
     */
    /**
     * @param Mage_Sales_Model_Order $order
     * @return false|Mage_Core_Model_Abstract|null
     */
    public function getOrderClass(Mage_Sales_Model_Order $order)
    {
        $paymentProvider = '';
        $payment = $order->getPayment();
        if (is_object($payment)) {
            $paymentProvider = $payment->getMethod();
        }
        $classId = sprintf(
            'nosto_tagging/meta_order_%s',
            $paymentProvider
        );
        return $this->getClass(
            $classId,
            'NostoOrderInterface',
            'nosto_tagging/meta_order'
        );
    }

    /*
     * Loads correct / plugable rating class
     *
     *
     * @param Mage_Core_Model_Store $store
     * @return Nosto_Tagging_Model_Meta_Rating|null
     */
    /**
     * @param Mage_Core_Model_Store $store
     * @return false|Mage_Core_Model_Abstract|null
     */
    public function getRatingClass(Mage_Core_Model_Store $store)
    {
        $class = null;
        /* @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');

        if ($provider = $dataHelper->getRatingsAndReviewsProvider($store)) {
            /* @var Nosto_Tagging_Helper_Rating $ratingHelper */
            $ratingHelper = Mage::helper('nosto_tagging/rating');
            $providerName = $ratingHelper->getModuleNameByProvider($provider);

            $classId = self::createClassId('meta_rating_%s', $providerName);
            $class = $this->getClass(
                $classId,
                'Nosto_Tagging_Model_Meta_Rating_Interface'
            );
        }

        return $class;
    }

    /**
     * Creates a class identifier
     *
     * @param $classString
     * @param $identifier
     * @return string
     */
    protected static function createClassId($classString, $identifier)
    {
        $classId = sprintf(
            'nosto_tagging/' . $classString,
            $identifier
        );

        return strtolower($classId);
    }

    /**
     * Tries to find class by given attributes
     *
     * @param string $classId
     * @param string $expected
     * @param bool $fallback
     * @return false|Mage_Core_Model_Abstract|null
     */
    protected function getClass($classId, $expected, $fallback = false) {
        $class = null;
        try {
            if (is_string($classId)) {
                $className = Mage::getConfig()->getModelClassName($classId);
                if (class_exists($className)) {
                    $class = Mage::getModel($classId);
                }
            }
            if ($class instanceof $expected == false && $fallback !== false) {
                $class = Mage::getModel($fallback);
            }
        } catch (Exception $e) {
            if ($fallback !== false) {
                $class = Mage::getModel($fallback);
            }
        }

        return $class;
    }
}

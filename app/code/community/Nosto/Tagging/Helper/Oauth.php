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

require_once Mage::getBaseDir('lib') . '/nosto/php-sdk/src/config.inc.php';

/**
 * Helper class for OAuth2 related tasks.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Oauth extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the meta data model needed for using the OAuth2 client included
     * in the Nosto SDk.
     *
     * @param Mage_Core_Model_Store $store the store to get the oauth meta data for..
     *
     * @return Nosto_Tagging_Model_Meta_Oauth the meta data instance.
     */
    public function getMetaData(Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Model_Meta_Oauth $meta */
        $meta = Mage::getModel('nosto_tagging/meta_oauth');
        $meta->loadData($store);
        return $meta;
    }
}

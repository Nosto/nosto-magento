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
 * @category    Nosto
 * @package     Nosto_Tagging
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for common operations.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to store config nosto module enabled state.
     */
    const XML_PATH_ENABLED = 'nosto_tagging/settings/enabled';

    /**
     * Path to store config nosto service server address.
     */
    const XML_PATH_SERVER = 'nosto_tagging/settings/server';

    /**
     * Path to store config nosto service account name.
     */
    const XML_PATH_ACCOUNT = 'nosto_tagging/settings/account';

    /**
     * Path to the store config collect_email_addresses option.
     */
    const XML_PATH_COLLECT_EMAIL_ADDRESSES = 'nosto_tagging/tagging_options/collect_email_addresses';

    /**
     * Check if module exists and enabled in global config.
     * Also checks if the module is enabled for the current store and if the needed criteria has been provided for the
     * module to work.
     *
     * @param string $moduleName the full module name, example Mage_Core
     *
     * @return boolean
     */
    public function isModuleEnabled($moduleName = null)
    {
        if (!parent::isModuleEnabled($moduleName)
            || !$this->getEnabled()
            || !$this->getServer()
            || !$this->getAccount()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Builds a tagging string of the given category including all its parent categories.
     * The categories are sorted by their position in the category tree path.
     *
     * @param Mage_Catalog_Model_Category $category
     *
     * @return string
     */
    public function buildCategoryString($category)
    {
        $data = array();

        if ($category instanceof Mage_Catalog_Model_Category) {
            /** @var $categories Mage_Catalog_Model_Category[] */
            $categories = $category->getParentCategories();
            $path = $category->getPathInStore();
            $ids = array_reverse(explode(',', $path));
            foreach ($ids as $id) {
                if (isset($categories[$id]) && $categories[$id]->getName()) {
                    $data[] = $categories[$id]->getName();
                }
            }
        }

        if (!empty($data)) {
            return DS . implode(DS, $data);
        } else {
            return '';
        }
    }

    /**
     * Formats date into Nosto format, i.e. Y-m-d.
     *
     * @param string $date
     *
     * @return string
     */
    public function getFormattedDate($date)
    {
        return date('Y-m-d', strtotime($date));
    }

    /**
     * Return if the module is enabled.
     *
     * @param mixed $store
     *
     * @return boolean
     */
    public function getEnabled($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED, $store);
    }

    /**
     * Return the server address to the Nosto service.
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getAccount($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNT, $store);
    }

    /**
     * Return the account name that is used by this store to access the Nosto service.
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getServer($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SERVER, $store);
    }

    /**
     * Return if customer email addresses should be collected.
     *
     * @param mixed $store
     *
     * @return boolean
     */
    public function getCollectEmailAddresses($store = null)
    {
        return (boolean)Mage::getStoreConfig(self::XML_PATH_COLLECT_EMAIL_ADDRESSES, $store);
    }
}

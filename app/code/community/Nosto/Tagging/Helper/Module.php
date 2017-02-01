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

use Nosto_Tagging_Helper_Rating as RatingHelper;
use Nosto_Tagging_Helper_Data as DataHelper;
/**
 * Helper class for common Magento modules.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Module extends Mage_Core_Helper_Abstract
{

    /**
     * Field for config path
     */
    const FIELD_CONFIG_PATH = 'config_path';

    /**
     * Field for expected value
     */
    const FIELD_EXPECTED_VALUE = 'expected_value';

    /**
     * An array of supported / integrated modules
     *
     * @var array
     */
    public static $integratedModules = array(
        RatingHelper::RATING_PROVIDER_YOTPO => array(
            self::FIELD_CONFIG_PATH => DataHelper::XML_PATH_RATING_PROVIDER,
            self::FIELD_EXPECTED_VALUE => RatingHelper::RATING_PROVIDER_YOTPO
        )
    );

    /**
     * Gets a list of all installed modules
     *
     * @return array
     */
    public function getAllModules()
    {
        $configElement = Mage::getConfig()->getNode('modules');
        $modules = get_object_vars($configElement);

        return $modules;
    }

    /**
     * Gets a list of all active modules
     *
     * @return array
     */
    public function getAllActiveModules()
    {
        $active = array();
        $all = $this->getAllModules();
        foreach ($all as $moduleName=>$module) {
            if ($module->active != "false") {
                $active[$moduleName] = $module;
            }
        }

        return $active;
    }

    /**
     * Gets a list of installed modules that integrate with Nosto
     *
     * @return array
     */
    public function getModulesForIntegration()
    {
        /* @var DataHelper $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        $validForIntegration = array();
        $activeModules = $this->getAllActiveModules();

        foreach ($activeModules as $moduleName => $moduleConfig) {
            if (array_key_exists($moduleName, self::$integratedModules)) {
                $integrated = 0;
                $integratedModuleConfig = self::$integratedModules[$moduleName];
                $validForIntegration[$moduleName] = $integrated;
                $path = $integratedModuleConfig[self::FIELD_CONFIG_PATH];
                $configValues = $dataHelper->getConfigInAllStores(
                    $path
                );
                foreach ($configValues as $configValue) {
                    if ($configValue === $integratedModuleConfig[self::FIELD_EXPECTED_VALUE]) {
                        $integrated = 1;
                        break;
                    }
                }
                $validForIntegration[$moduleName] = $integrated;
            }
        }

        return $validForIntegration;
    }
}

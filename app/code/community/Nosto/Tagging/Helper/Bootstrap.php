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

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Helper class for initing user agent and load the .env file
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Bootstrap extends Mage_Core_Helper_Abstract
{
    /**
     * Flushes the Magento caches, not all of them but some of them, normally after creating an
     * account or connecting with nosto.
     */
    public function init()
    {
        static $loaded = false;
        if (!$loaded) {
            /* @var Nosto_Tagging_Helper_Data $nostoHelper */
            $nostoHelper = Mage::helper('nosto_tagging');
            Nosto_Request_Http_HttpRequest::buildUserAgent(
                'Magento', Mage::getVersion(),
                $nostoHelper->getExtensionVersion()
            );
            try {
                $validator = new Zend_Validate_File_Exists();
                $validator->addDirectory(__DIR__ . '/../');
                if ($validator->isValid('.env')) {
                    $dotenv = new Dotenv_Dotenv(
                        $validator->getDirectory()
                    );
                    $dotenv->load();
                }

                $loaded = true;
            } catch (Zend_Validate_Exception $e) {
                NostoLog::exception($e);
            }
        }
    }
}

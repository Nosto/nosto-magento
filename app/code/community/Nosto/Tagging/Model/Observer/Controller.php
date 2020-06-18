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
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

/**
 * Event observer model for controller.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_Observer_Controller
{
    /**
     * Xml layout handle for the default page footer section.
     */
    const XML_LAYOUT_PAGE_DEFAULT_FOOTER_HANDLE = 'nosto_tagging_page_default_footer';

    /**
     * Adds Nosto footer block at the end of the content block.
     *
     * Event 'controller_action_layout_load_before'.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer_Controller
     */
    public function addBlockAfterMainContent(Varien_Event_Observer $observer)
    {
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            /** @var $layout Mage_Core_Model_Layout_Update */
            /** @noinspection PhpUndefinedMethodInspection */
            $layout = $observer->getEvent()->getLayout()->getUpdate();
            $layout->addHandle(self::XML_LAYOUT_PAGE_DEFAULT_FOOTER_HANDLE);
        }

        return $this;
    }
}

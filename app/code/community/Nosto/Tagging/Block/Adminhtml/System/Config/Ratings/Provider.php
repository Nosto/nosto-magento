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
 * Info block to show the current configured currency formats for the viewed
 * store scope on the system config page.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_System_Config_Ratings_Provider
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        $this->setElement($element);
        $data = $this->element->getData();
        $values = $data['values'];
        $html = '';
        if ($values) {
            foreach ($values as $data) {
                $html .= $this->renderOption($data);
            }
        }
        return $html;
    }

    private function renderOption($item) {
        $html = '';
        $currentValue = $this->element->getEscapedValue();
        $selected = ($item['value'] === $currentValue) ? 'checked' : '';
        $optionId = sprintf('nosto_tagging_ratings_and_reviews_provider_%s', $item['value']);
        $optionName = 'groups[ratings_and_reviews][fields][provider][value]';
        if (!empty($item['image_url'])) {
            $imageHtml = sprintf(
                '<img src="%s" style="width: 50px; display: inline;"><br/>',
                $item['image_url']
            );
        } else {
            $imageHtml = null;
        }
        $html .= sprintf('<input id="%s" type="radio" name="%s" value="%s" %s>%s %s</input><br/>',
            $optionId,
            $optionName,
            $item['value'],
            $selected,
            $item['label'],
            $imageHtml
        );

        return $html;
    }
}

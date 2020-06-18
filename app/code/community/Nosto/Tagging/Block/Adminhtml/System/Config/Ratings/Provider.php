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

/**
 * Info block to show the current configured currency formats for the viewed
 * store scope on the system config page.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Block_Adminhtml_System_Config_Ratings_Provider
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inheritdoc
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        /** @noinspection PhpUndefinedMethodInspection */
        $this->setElement($element);
        $data = $element->getData();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedFieldInspection */
        if ($this->element->getInherit()) {
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedFieldInspection */
            $this->element->setDisabled(true);
        }

        $values = $data['values'];
        $html = '<table cellspacing="0" border="0" class="form-list">';
        $html .= '<colgroup class="label"></colgroup>';
        $html .= '<colgroup class="value"></colgroup>';
        $html .= '<colgroup class="scope-label"></colgroup>';
        $html .= '<colgroup class=""></colgroup>';
        $html .= '<tbody>';
        if ($values) {
            foreach ($values as $data) {
                $html .= $this->renderOption($data);
            }
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * @param $item
     * @return string
     */
    protected function renderOption($item)
    {
        $tdCss = 'vertical-align: middle; border-bottom: 1px gainsboro solid; padding: 4px 0 4px 0';
        $html = '';
        /** @noinspection PhpUndefinedFieldInspection */
        $currentValue = $this->element->getEscapedValue();
        $disabled = '';
        /** @noinspection PhpUndefinedFieldInspection */
        if ($this->element->getDisabled()) {
            $disabled = 'disabled="disabled"';
        }

        $selected = ((string)$item['value'] === (string)$currentValue) ? 'checked' : '';
        $optionId = sprintf('nosto_tagging_ratings_and_reviews_provider_%s', $item['value']);
        $optionName = 'groups[ratings_and_reviews][fields][provider][value]';
        if (!empty($item['image_url'])) {
            /** @noinspection HtmlUnknownTarget */
            $imageHtml = sprintf(
                '<img src="%s" style="width: 50px; display: inline;"><br/>',
                $item['image_url']
            );
        } else {
            $imageHtml = '';
        }

        $html .= sprintf('<tr id="row_nosto_tagging_%s">', $optionId);
        /** @noinspection HtmlUnknownAttribute */
        $html .= sprintf(
            '<td style="%s"><input id="%s" type="radio" name="%s" value="%s" %s %s> %s</input></td>',
            $tdCss,
            $optionId,
            $optionName,
            $item['value'],
            $selected,
            $disabled,
            $item['label']
        );
        $html .= sprintf(
            '<td style="%s"> %s</td>',
            $tdCss,
            $imageHtml
        );
        $html .= sprintf('</tr>');

        return $html;
    }
}

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

return [
    'analyze_signature_compatibility' => true,
    'backward_compatibility_checks' => true,
    'exclude_file_regex' => '@^vendor/.*/(tests|test|Tests|Test)/@',
    'directory_list' => [
         'app',
         '.phan/stubs',
         'lib',
         'vendor'
    ],
    'exclude_file_list' => [
        'vendor/openmage/magento-mirror/lib/Zend/Validate/Hostname/Biz.php',
        'vendor/openmage/magento-mirror/lib/Zend/Validate/Hostname/Cn.php',
        'vendor/openmage/magento-mirror/lib/Zend/Validate/Hostname/Com.php',
        'vendor/openmage/magento-mirror/lib/Zend/Validate/Hostname/Jp.php',
    ],
    "exclude_analysis_directory_list" => [
        '.phan/stubs',
        'lib',
        'vendor',
        'app/code/community/Nosto/Tagging/sql/tagging_setup/'
    ],
    // Add any issue types (such as 'PhanUndeclaredMethod')
    // to this black-list to inhibit them from being reported.
    'suppress_issue_types' => [
        'PhanUnreferencedMethod',
        'PhanUndeclaredMethod'
    ],
];

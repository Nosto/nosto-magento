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

//@codingStandardsIgnoreStarts
if (is_file(__DIR__ . '/../../../../../../shell/abstract.php')) {
    require_once __DIR__ . '/../../../../../../shell/abstract.php';
} elseif (is_file(__DIR__ . '/../../../../../../../../shell/abstract.php')) {
    require_once __DIR__ . '/../../../../../../../../shell/abstract.php';
} else {
    echo 'abstract.php not found';
    exit(1);
}

class RemoveCommand extends Mage_Shell_Abstract
{

    const SCOPE_CODE = 'scope-code';

    /*
    * @var NostoAccountHelper
    */
    protected $_accountHelper;

    /**
     * RemoveCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_accountHelper = new Nosto_Tagging_Helper_Account();
    }

    /**
     * Run the command
     */
    public function run()
    {
        $this->checkArgs();
        $storeCode = $this->getArg(self::SCOPE_CODE);
        $this->removeConnectedNostoAccount($storeCode);

    }

    /**
     * @param $storeCode
     * @return int
     */
    protected function removeConnectedNostoAccount($storeCode)
    {
        $store = $this->getStoreWithNostoByCode($storeCode);
        if (!$store) {
            echo sprintf("Nosto account not found for this store view. \n");
            exit(1);
        }
        if ($this->_accountHelper->resetAccountSettings($store)) {
            Mage::app()->getCacheInstance()->flush();
            echo "Account removed from store. \n";
        } else {
            echo sprintf("Operation failed");
            exit(1);
        }

        return 0;

    }

    /**
     * @param $storeCode
     * @return Mage_Core_Model_Store|null
     */
    protected function getStoreWithNostoByCode($storeCode)
    {
        $stores = $this->_accountHelper->getAllStoreViewsWithNostoAccount();
        foreach ($stores as $store) {
            if ($storeCode === $store->getCode()) {
                return $store;
            }
        }
        return null;
    }

    /**
     * Check if all required arguments were given to the function
     *
     * @return bool
     */
    protected function checkArgs()
    {
        if (!$this->getArg(self::SCOPE_CODE)) {
            echo sprintf("Missing %s \n", self::SCOPE_CODE);
            echo $this->usageHelp();
            exit(1);
        }

        return true;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f remove.php -- [options]
  --scope-code        Store view code
  -h                  Short alias for help
  help                This help
USAGE;
    }
}

// Run the command
$shell = new RemoveCommand();
$shell->run();

//@codingStandardsIgnoreEnds
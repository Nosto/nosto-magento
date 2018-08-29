#!/usr/bin/env php
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

use Nosto_Request_Api_Token as Token;
use Nosto_Object_Signup_Account as NostoSignupAccount;

if (is_file(__DIR__ . '/../../../../../../shell/abstract.php')) {
    require_once __DIR__ . '/../../../../../../shell/abstract.php';
} elseif (is_file(__DIR__ . '/../../../../../../../../shell/abstract.php')) {
    require_once __DIR__ . '/../../../../../../../../shell/abstract.php';
} else {
  echo 'abstract.php not found';
  exit(1);
}

/**
 * Class ReconnectCommand
 */
class ReconnectCommand extends Mage_Shell_Abstract
{
    const NOSTO_ACCOUNT_ID = 'account-id';
    const TOKEN_SUFFIX = '_token';
    const SCOPE_CODE = 'scope-code';
    const OVERRIDE = 'override';

    /*
    * @var NostoAccountHelper
    */
    protected $accountHelper;

    /**
     * @var array
     */
    protected $requiredArguments;

    public function __construct() {
        parent::__construct();
        set_time_limit(0);
        $this->accountHelper = new Nosto_Tagging_Helper_Account();
        $this->requiredArguments = [
            self::NOSTO_ACCOUNT_ID,
            Token::API_SSO . self::TOKEN_SUFFIX,
            Token::API_PRODUCTS . self::TOKEN_SUFFIX,
            Token::API_SETTINGS . self::TOKEN_SUFFIX,
            Token::API_EXCHANGE_RATES . self::TOKEN_SUFFIX,
            self::SCOPE_CODE
        ];
    }

    public function run()
    {
        $this->checkArgs();

        $accountId = $this->getArg(self::NOSTO_ACCOUNT_ID);
        $scopeCode = $this->getArg(self::SCOPE_CODE);
        $tokens = $this->generateTokens();
        if ($this->updateNostoTokens($tokens, $accountId, $scopeCode)) {
            echo "Account saved. \n";
        }

        return 0;
    }

    /**
     * Check if all required arguments were given to the function
     *
     * @return bool
     */
    private function checkArgs()
    {
        foreach ($this->requiredArguments as $argument) {
            if (!$this->getArg($argument)) {
                echo sprintf("Missing %s \n", $argument );
                echo $this->usageHelp();
                exit(1);
            }
        }
        return true;
    }

    /**
     * Set or override tokens for the given account id.
     * If a local account is not found, will create a new one.
     *
     * @param array $tokens
     * @param $accountId
     * @return bool
     */
    private function updateNostoTokens(array $tokens, $accountId, $scopeCode)
    {
        $store = $this->getStoreByStoreViewCode($scopeCode);
        if(!$store){
            echo ('Store not found. Check your input.');
            exit(1);
        }
        $storeAccountId = $store->getConfig(Nosto_Tagging_Helper_Account::XML_PATH_ACCOUNT);
        $account = $this->accountHelper->find($store);
        if ($account && $storeAccountId === $accountId) {
            if (!$this->getArg(self::OVERRIDE)) {
                echo "Local account found. To overriding use the '--override' option \n";
                echo $this->usageHelp();
                exit(1);
            }
            echo "Local account found. Overriding Tokens... \n";
            $account->setTokens($tokens);
            return $this->accountHelper->save($account, $store);
        }
        echo "Local account not found. Saving local account...\n";
        $account = new NostoSignupAccount($accountId);
        $account->setTokens($tokens);
        return $this->accountHelper->save($account, $store);
    }

    /**
     * Generate Tokens to connect account
     *
     * @return array of Token objects
     * @throws Nosto_NostoException
     */
    private function generateTokens()
    {
        $tokens = array();

        $ssoToken = $this->getArg(Token::API_SSO . self::TOKEN_SUFFIX);
        $tokens[] = new Token(Token::API_SSO, $ssoToken);

        $productsToken = $this->getArg(Token::API_PRODUCTS . self::TOKEN_SUFFIX);
        $tokens[] = new Token(Token::API_PRODUCTS, $productsToken);

        $ratesToken = $this->getArg(Token::API_EXCHANGE_RATES . self::TOKEN_SUFFIX);
        $tokens[] = new Token(Token::API_EXCHANGE_RATES, $ratesToken);

        $settingsToken = $this->getArg(Token::API_SETTINGS . self::TOKEN_SUFFIX);
        $tokens[] = new Token(Token::API_SETTINGS, $settingsToken);

        $emailToken = $this->getArg(Token::API_EMAIL . self::TOKEN_SUFFIX);
        if ($emailToken) {
            $tokens[] = new Token(Token::API_EMAIL, $emailToken);
        }
        return $tokens;
    }


    /**
     * Return the Store object by the store view code
     *
     * @param $code
     * @return null
     */
    private function getStoreByStoreViewCode($code)
    {
        /** @var \Nosto_Tagging_Helper_Data $nostoDataHelper */
        $nostoDataHelper = Mage::helper('nosto_tagging');
        $stores = $nostoDataHelper->getAllStoreViews();
        foreach ($stores as $store) {
            if ($store->getCode() === $code) {
                return $store;
            }
        }
        return null;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f reconnect.php -- [options]
  --account-id        The Nosto account id to be reconnected
  --products_token    Products token
  --sso_token         SSO token
  --settings_token    Settings token
  --rates_token       API exchange rates token
  --email_token       Email token (optional)
  --scope-code        Store view code
  --override          Force override tokens
  -h                  Short alias for help
  help                This help
USAGE;
    }
}

// Run the command
$shell = new ReconnectCommand();
$shell->run();

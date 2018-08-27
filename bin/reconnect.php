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

require_once __DIR__ . '/../../../shell/abstract.php';

/**
 * Class ReconnectCommand
 */
class ReconnectCommand extends Mage_Shell_Abstract
{
    const NOSTO_ACCOUNT_ID = 'account-id';
    const TOKEN_SUFFIX = '_token';
    const SCOPE_CODE = 'scope-code';

    /*
    * @var NostoAccountHelper
    */
    protected $accountHelper;

    /**
     * @var
     */
    protected $arguments;

    public function __construct() {
        parent::__construct();
        set_time_limit(0);
        $this->accountHelper = new Nosto_Tagging_Helper_Account();
        $this->arguments = [
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
        if(!$this->checkArgs())
        {
            exit(1);
        }
        $accountId = $this->getArg(self::NOSTO_ACCOUNT_ID);
        $scopeCode = $this->getArg(self::SCOPE_CODE);
        $tokens = $this->generateTokens();
        if ($this->updateNostoTokens($tokens, $accountId, $scopeCode)) {
            echo "Ok...";
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
        foreach ($this->arguments as $argument) {
            if (!$this->getArg($argument)) {
                //TODO: Format this error output
                echo 'Missing ' . $argument;
                return false;
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
            return false;
        }
        $storeAccountId = $store->getConfig(Nosto_Tagging_Helper_Account::XML_PATH_ACCOUNT);
        $account = $this->accountHelper->find($store);
        if ($account && $storeAccountId === $accountId) {
            echo('Local account found. Overriding Tokens...');
            $account->setTokens($tokens);
            return $this->accountHelper->save($account, $store);
        } else {
            echo('Local account not found. Saving local account...');
            $account = new NostoSignupAccount($accountId);
            $account->setTokens($tokens);
            return $this->accountHelper->save($account, $store);
        }
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
}

// Run the command
$shell = new ReconnectCommand();
$shell->run();

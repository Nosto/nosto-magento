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
 * OAuth2 controller.
 * Handles the redirect from Nosto OAuth2 authorization server.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_tagging_OauthController extends Mage_Core_Controller_Front_Action
{
    use Nosto_Mixins_OauthTrait;

    /**
     * Handles the redirect from Nosto oauth2 authorization server when an
     * existing account is connected to a store.
     * This is handled in the front end as the oauth2 server validates the
     * "return_url" sent in the first step of the authorization cycle, and
     * requires it to be from the same domain that the account is configured
     * for and only redirects to that domain.
     */
    public function indexAction()
    {
        $this->connect();
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if (($storeCode = $this->getParam('___store')) !== null) {
            $store = $helper->getStore($storeCode);
            if ($store && $store->getId() !== $helper->getStore()->getId()) {
                Mage::app()->setCurrentStore($store->getCode());
            }
        }

        return $helper->getStore();
    }

    /**
     * Implemented trait method that is a utility responsible for fetching a specified query
     * parameter from the GET request.
     *
     * @param string $name the name of the query parameter to fetch
     * @return string the value of the specified query parameter
     */
    public function getParam($name)
    {
        return $this->getRequest()->getParam($name);
    }

    /**
     * Logs an exception to the Magento error log when th
     *
     * @param Exception $e
     */
    public function logError(Exception $e)
    {
        Nosto_Tagging_Helper_Log::exception($e);
    }

    /**
     * Implemented trait method that is responsible for redirecting the user to a 404 page when
     * the authorization code is invalid.
     */
    public function notFound()
    {
        $this->norouteAction();
    }

    /**
     * Implemented trait method that is responsible for fetching the OAuth parameters used for all
     * OAuth operations
     *
     * @return Nosto_Tagging_Model_Meta_Oauth the OAuth parameters for the operations
     * @suppress PhanTypeMismatchReturn
     */
    public function getMeta()
    {
        /** @var Nosto_Tagging_Model_Meta_Oauth $meta */
        $meta = Mage::getModel('nosto_tagging/meta_oauth');
        $meta->loadData($this->getStore());
        return $meta;
    }

    /**
     * Implemented trait method that is responsible for saving an account with the all tokens for
     * the current store view (as defined by the parameter.)
     *
     * @param Nosto_Types_Signup_AccountInterface $account the account to save
     * @return bool a boolean value indicating whether the account was saved
     */
    public function save(Nosto_Types_Signup_AccountInterface $account)
    {
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        if ($accountHelper->save($account, $this->getStore())) {
            //Enable review and rating by default
            /* @var Nosto_Tagging_Helper_Rating $ratingHelper */
            $ratingHelper = Mage::helper('nosto_tagging/rating');
            $ratingHelper->enableReviewAndRating($this->getStore());

            return true;
        }
        return false;
    }

    /**
     * Implemented trait method that redirects the user with the authentication params to the
     * admin controller.
     *
     * @param array $params the parameters to be used when building the redirect
     */
    public function redirect(array $params)
    {
        $params['store'] = (int)$this->getStore()->getId();
        $params['_store'] = Mage_Core_Model_App::ADMIN_STORE_ID;
        $this->_redirect('adminhtml/nosto/redirectProxy', $params);
    }
}

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
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about a new Nosto account.
 * This is used during the Nosto account creation.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Meta_Account extends Mage_Core_Model_Abstract implements NostoAccountMetaDataInterface
{
	/**
	 * @var string the store name.
	 */
	protected $title;

	/**
	 * @var string the account name.
	 */
	protected $name;

	/**
	 * @var string the store front end url.
	 */
	protected $frontPageUrl;

	/**
	 * @var string the store currency ISO (ISO 4217) code.
	 */
	protected $currencyCode;

	/**
	 * @var string the store language ISO (ISO 639-2) code.
	 */
	protected $languageCode;

	/**
	 * @var string the owner language ISO (ISO 639-2) code.
	 */
	protected $ownerLanguageCode;

	/**
	 * @var Nosto_Tagging_Model_Meta_Account_Owner the account owner meta data model.
	 */
	protected $owner;

	/**
	 * @var Nosto_Tagging_Model_Meta_Account_Billing the account billing details meta data model.
	 */
	protected $billing;

	/**
	 * @var string the API token used to identify an account creation.
	 */
	protected $signUpApiToken = 'YBDKYwSqTCzSsU8Bwbg4im2pkHMcgTy9cCX7vevjJwON1UISJIwXOLMM0a8nZY7h';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$store = Mage::app()->getStore();
		$this->title = $store->getWebsite()->getName() . ' - ' . $store->getName();
		$this->name = substr(sha1(rand()), 0, 8);
		$this->frontPageUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$this->currencyCode = $store->getBaseCurrencyCode();
		$this->languageCode = substr($store->getConfig('general/locale/code'), 0, 2);
		$this->ownerLanguageCode = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
		$this->owner = new Nosto_Tagging_Model_Meta_Account_Owner();
		$this->billing = new Nosto_Tagging_Model_Meta_Account_Billing();
	}

	/**
	 * Sets the store title.
	 *
	 * @param string $title the store title.
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets the account name.
	 *
	 * @param string $name the account name.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @inheritdoc
	 */
	public function getPlatform()
	{
		return 'magento';
	}

	/**
	 * Sets the store front page url.
	 *
	 * @param string $url the front page url.
	 */
	public function setFrontPageUrl($url)
	{
		$this->frontPageUrl = $url;
	}

	/**
	 * @inheritdoc
	 */
	public function getFrontPageUrl()
	{
		return $this->frontPageUrl;
	}

	/**
	 * Sets the store currency ISO (ISO 4217) code.
	 *
	 * @param string $code the currency ISO code.
	 */
	public function setCurrencyCode($code)
	{
		$this->currencyCode = $code;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrencyCode()
	{
		return $this->currencyCode;
	}

	/**
	 * Sets the store language ISO (ISO 639-2) code.
	 *
	 * @param string $languageCode the language ISO code.
	 */
	public function setLanguageCode($languageCode)
	{
		$this->languageCode = $languageCode;
	}

	/**
	 * @inheritdoc
	 */
	public function getLanguageCode()
	{
		return $this->languageCode;
	}

	/**
	 * Sets the owner language ISO (ISO 639-2) code.
	 *
	 * @param string $languageCode the language ISO code.
	 */
	public function setOwnerLanguageCode($languageCode)
	{
		$this->ownerLanguageCode = $languageCode;
	}

	/**
	 * @inheritdoc
	 */
	public function getOwnerLanguageCode()
	{
		return $this->ownerLanguageCode;
	}

	/**
	 * @inheritdoc
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * @inheritdoc
	 */
	public function getBillingDetails()
	{
		return $this->billing;
	}

	/**
	 * @inheritdoc
	 */
	public function getSignUpApiToken()
	{
		return $this->signUpApiToken;
	}
}

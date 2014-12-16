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
 * Meta data class which holds information to be sent to the Nosto account configuration iframe.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Meta_Account_Iframe extends Mage_Core_Model_Abstract implements NostoAccountMetaDataIframeInterface
{
	/**
	 * @var string the admin user first name.
	 */
	protected $firstName;

	/**
	 * @var string the admin user last name.
	 */
	protected $lastName;

	/**
	 * @var	string the admin user email address.
	 */
	protected $email;

	/**
	 * @var string the language ISO (ISO 639-2) code for localization on oauth2 server.
	 */
	protected $languageIsoCode;

	/**
	 * @var string the language ISO (ISO 639-2) for the store view scope.
	 */
	protected $languageIsoCodeShop;

	/**
	 * @var string unique ID that identifies the Magento installation and all the accounts within.
	 */
	protected $uniqueId;

	/**
	 * @var string preview url for the product page in the active store view scope.
	 */
	protected $previewUrlProduct;

	/**
	 * @var string preview url for the category page in the active store view scope.
	 */
	protected $previewUrlCategory;

	/**
	 * @var string preview url for the search page in the active store view scope.
	 */
	protected $previewUrlSearch;

	/**
	 * @var string preview url for the cart page in the active store view scope.
	 */
	protected $previewUrlCart;

	/**
	 * @var string preview url for the front page in the active store view scope.
	 */
	protected $previewUrlFront;

	/**
	 * Constructor.
	 * Sets initial values.
	 */
	public function __construct()
	{
		parent::__construct();

		/** @var Mage_Admin_Model_User $user */
		$user = Mage::getSingleton('admin/session')->getUser();
		/** @var Nosto_Tagging_Helper_Url $urlHelper */
		$urlHelper = Mage::helper('nosto_tagging/url');
		/** @var Nosto_Tagging_Helper_Data $dataHelper */
		$dataHelper = Mage::helper('nosto_tagging/data');

		$this->firstName = $user->getFirstname();
		$this->lastName = $user->getLastname();
		$this->email = $user->getEmail();
		$this->languageIsoCode = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
		$this->languageIsoCodeShop = substr(Mage::app()->getStore()->getConfig('general/locale/code'), 0, 2);
		$this->uniqueId = $dataHelper->getInstallationId();
		$this->previewUrlProduct = $urlHelper->getPreviewUrlProduct();
		$this->previewUrlCategory = $urlHelper->getPreviewUrlCategory();
		$this->previewUrlSearch = $urlHelper->getPreviewUrlSearch();
		$this->previewUrlCart = $urlHelper->getPreviewUrlCart();
		$this->previewUrlFront = $urlHelper->getPreviewUrlFront();
	}

	/**
	 * Sets the first name of the admin user.
	 *
	 * @param string $firstName the first name.
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	/**
	 * @inheritdoc
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * Sets the last name of the admin user.
	 *
	 * @param string $lastName the last name.
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	/**
	 * @inheritdoc
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * Sets the email address of the admin user.
	 *
	 * @param string $email the email address.
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @inheritdoc
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Sets the language ISO code.
	 *
	 * @param string $code the ISO code.
	 */
	public function setLanguageIsoCode($code)
	{
		$this->languageIsoCode = $code;
	}

	/**
	 * @inheritdoc
	 */
	public function getLanguageIsoCode()
	{
		return $this->languageIsoCode;
	}

	/**
	 * @inheritdoc
	 */
	public function getLanguageIsoCodeShop()
	{
		return $this->languageIsoCodeShop;
	}

	/**
	 * @inheritdoc
	 */
	public function getUniqueId()
	{
		return $this->uniqueId;
	}

	/**
	 * @inheritdoc
	 */
	public function getVersionPlatform()
	{
		return Mage::getVersion();
	}

	/**
	 * @inheritdoc
	 */
	public function getVersionModule()
	{
		// Path is hard-coded to be like in "etc/config.xml".
		return Mage::getConfig()->getNode()->modules->Nosto_Tagging->version;
	}

	/**
	 * @inheritdoc
	 */
	public function getPreviewUrlProduct()
	{
		return $this->previewUrlProduct;
	}

	/**
	 * @inheritdoc
	 */
	public function getPreviewUrlCategory()
	{
		return $this->previewUrlCategory;
	}

	/**
	 * @inheritdoc
	 */
	public function getPreviewUrlSearch()
	{
		return $this->previewUrlSearch;
	}

	/**
	 * @inheritdoc
	 */
	public function getPreviewUrlCart()
	{
		return $this->previewUrlCart;
	}

	/**
	 * @inheritdoc
	 */
	public function getPreviewUrlFront()
	{
		return $this->previewUrlFront;
	}
}

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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about the Nosto account owner.
 * This is used during the Nosto account creation.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Account_Owner extends Mage_Core_Model_Abstract implements NostoAccountMetaDataOwnerInterface
{
    /**
     * @var string the account owner first name.
     */
    protected $firstName;

    /**
     * @var string the account owner last name.
     */
    protected $lastName;

    /**
     * @var    string the account owner email address.
     */
    protected $email;

    /**
     * Constructor.
     * Sets initial values of the account owner.
     */
    public function __construct()
    {
        parent::__construct();

        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/session')->getUser();
        $this->firstName = $user->getFirstname();
        $this->lastName = $user->getLastname();
        $this->email = $user->getEmail();
    }

    /**
     * Internal Magento constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_account_owner');
    }

    /**
     * Sets the first name of the account owner.
     *
     * @param string $firstName the first name.
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * The first name of the account owner.
     *
     * @return string the first name.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the last name of the account owner.
     *
     * @param string $lastName the last name.
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * The last name of the account owner.
     *
     * @return string the last name.
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the email address of the account owner.
     *
     * @param string $email the email address.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * The email address of the account owner.
     *
     * @return string the email address.
     */
    public function getEmail()
    {
        return $this->email;
    }
}

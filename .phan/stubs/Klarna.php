<?php /** @noinspection ALL */

/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Klarna
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
abstract class Vaimo_Klarna_Model_Transport_Abstract extends Varien_Object
{
    /**
     * The following variables are set depending on what child it is
     * Setting payment will automatically also set the order, as it is known in the payment object
     */
    protected $_quote = null;
    protected $_order = null;
    protected $_invoice = null;
    protected $_payment = null;
    protected $_creditmemo = null;

    /**
     * Info instance is set for example when doing a refund, it also tries to set the credit memo and order, if they are known
     */
    protected $_info_instance = null;

    /**
     * The current payment method
     */
    protected $_method = null;

    /**
     * Store id that should be used while loading settings etc
     * It's set to Mage::app()->getStore()->getId() initially
     * But is then changed as soon as one of the record variables above is set
     */
    protected $_storeId = null;

    /**
     * Country, Language and Currency code of the current store or of the current record
     */
    protected $_countryCode = '';
    protected $_languageCode = null;
    protected $_currencyCode = null;

    /**
     * Both addresses are set when the records above are set
     */
    protected $_shippingAddress = null;
    protected $_billingAddress = null;

    /**
     * Languages supported by Klarna
     */
    protected $_supportedLanguages = array(
        'da', // Danish
        'de', // German
        'en', // English
        'fi', // Finnish
        'nb', // Norwegian
        'nl', // Dutch
        'sv', // Swedish
    );

    /**
     * Countries supported by Klarna (array not used)
     */
    protected $_supportedCountries = array(
        'AT', // Austria
        'DK', // Danmark
        'DE', // Germany
        'FI', // Finland
        'NL', // Netherlands
        'NO', // Norway
        'SE', // Sweden
    );


    protected $_moduleHelper = null;

    /**
     * Constructor
     * setStoreInfo parameter added for Unittesting, never set otherwise
     *
     * @param bool $setStoreInfo
     */
    public function __construct($setStoreInfo = true, $moduleHelper = null)
    {
    }

    /**
     * @return Vaimo_Klarna_Helper_Data
     */
    protected function _getHelper()
    {
    }

    /**
     * Will call normal Mage::getStoreConfig
     * It's in it's own function, so it can be mocked in tests
     *
     * @param string $field
     * @param string $storeId
     *
     * @return string
     */
    protected function _getConfigDataCall($field, $storeId)
    {
    }

    /**
     * Will set current store language, but there is a language override in the Klarna payment setting.
     * Language is sent to the Klarna API
     * The reason for the override is for example if you use the New Norwegian language in the site (nn as code),
     * Klarna will not allow that code, so we have the override
     *
     * @return void
     */
    protected function _setDefaultLanguageCode()
    {
    }

    /**
     * Gets the Default country of the store
     *
     * @return string
     */
    protected function _getDefaultCountry()
    {
    }

    /**
     * Sets the default currency to that of this store id
     *
     * @return void
     */
    protected function _setDefaultCurrencyCode()
    {
    }

    /**
     * Sets the default country to that of this store id
     *
     * @return void
     */
    protected function _setDefaultCountry()
    {
    }

    protected function _getMageStore()
    {
    }

    /**
     * Sets the store of this class and then updates the default values
     *
     * If no record is set, like setQuote or setOrder, the code that
     * gets this model MUST call setStoreInformation. It used to be part
     * of construct, but it was removed from there because of unit tests
     *
     * @return void
     */
    public function setStoreInformation($storeId = null)
    {
    }

    /**
     * Parse a locale code into a language code Klarna can use.
     *
     * @param string $localeCode The Magento locale code to parse
     *
     * @return string
     */
    protected function _getLocaleLanguageCode($localeCode)
    {
    }

    /**
     * This function is only called if multiple countries are allowed
     * And one chooses one of the countries that aren't the default one
     * It then changes the language, to match with the country.
     *
     * @return void
     */
    protected function _updateNonDefaultCountryLanguage()
    {
    }

    /**
     * Once we have a record in one of the record variables, we update the addresses and then we set the country to
     * that of the shipping address or billing address, if shipping is empty
     *
     * @return void
     */
    protected function _updateCountry()
    {
    }

    /**
     * Set current shipping address
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     *
     * @return void
     */
    protected function _setShippingAddress($address)
    {
    }

    /**
     * Set current billing address
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     *
     * @return void
     */
    protected function _setBillingAddress($address)
    {
    }

    /**
     * Set current addresses from quote and updates this class currency
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return void
     */
    protected function _setAddressesFromQuote($quote)
    {
    }

    /**
     * Set current addresses from order and updates this class currency
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    protected function _setAddressesFromOrder($order)
    {
    }

    /**
     * Sets the payment method, either directly from the top class or when the appropriate record object is set
     *
     * @param string
     *
     * @return void
     */
    public function setMethod($method)
    {
    }

    /**
     * Sets the order of this class plus updates what is known on the order, such as payment method, store and address
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    public function setOrder($order)
    {
    }

    /**
     * Sets the quote of this class plus updates what is known on the quote, store and address
     * Method can also be set by this function, if it is known
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string Payment method
     *
     * @return void
     */
    public function setQuote($quote, $method = null)
    {
    }

    /**
     * Sets the invoice of this class plus updates current store
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return void
     */
    public function setInvoice($invoice)
    {
    }

    /**
     * Sets the creditmemo of this classplus updates what is known of other varibles, such as order, creditmemo and invoice
     *
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return void
     */
    public function setCreditmemo($creditmemo)
    {
    }

    /**
     * Sets the payment of this class
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return void
     */
    public function setPayment($payment)
    {
    }

    /**
     * Sets the info instance of this class plus updates what is known of other varibles, such as order and creditmemo
     *
     * @param Mage_Payment_Model_Info $info
     *
     * @return void
     */
    public function setInfoInstance($info)
    {
    }

    /**
     * Check if consent is needed
     *
     * @return boolean
     */
    public function needConsent()
    {
    }

    /**
     * Check if an asterisk is needed
     *
     * @return boolean
     */
    public function needAsterisk()
    {
    }

    /**
     * Check if gender is needed
     *
     * @return boolean
     */
    public function needGender()
    {
    }

    /**
     * Check if date of birth is needed
     *
     * @return boolean
     */
    public function needDateOfBirth()
    {
    }

    /**
     * Some countries supports to get more details to create request
     *
     * @return boolean
     */
    public function moreDetailsToKCORequest()
    {
    }

    /**
     * Norway has special rules regarding the details of the payment plan you are selecting
     *
     * @return boolean
     */
    public function needExtraPaymentPlanInformaton()
    {
    }

    /**
     * Return the fields a street should be split into.
     *
     * @return array
     */
    public function getSplit()
    {
    }

    /**
     * Is the sum below the limit allowed for the given _country?
     * This contains a hardcoded value for NL.
     * Meaning, if a customer shops for over 250 EUR, it won't be allowed to use any part payment option...
     * I'm leaving this as hardcoded... But should get a better solution...
     *
     * @param float $sum Sum to check
     * @param string $method payment method
     *
     * @return boolean
     */
    public function isBelowAllowedHardcodedLimit($sum)
    {
    }

    /**
     * Do we need to call getAddresses
     *
     * @return boolean
     */
    public function useGetAddresses()
    {
    }

    /**
     * Are Company Purchases supported?
     *
     * @return boolean
     */
    public function isCompanyAllowed()
    {
    }

    /**
     * Do we need to display the autofill warning label
     *
     * @return boolean
     */
    public function AllowSeparateAddress()
    {
    }

    /**
     * Do we need to display the autofill warning label
     *
     * @return boolean
     */
    public function shouldDisplayAutofillWarning()
    {
    }

    public function getAvailableMethods()
    {
    }

    /**
     * Check if shipping and billing should be the same
     *
     * @return boolean
     */
    public function shippingSameAsBilling()
    {
    }

    /**
     * Check if current country is allowed
     *
     * @return boolean
     */
    public function isCountryAllowed()
    {
    }

    /**
     * Check if method should be disabled if company field is filled in
     * See isCompanyAllowed, perhaps we should merge functions...
     *
     * @return boolean
     */
    public function showMethodForCompanyPurchases()
    {
    }

    /**
     * Function to read correct payment method setting
     *
     * @param string $field
     *
     * @return string
     */
    public function getConfigData($field)
    {
    }

    /**
     * Returns this class country code
     *
     * @return string
     */
    protected function _getCountryCode()
    {
    }

    /**
     * Returns this class language code
     *
     * @return string
     */
    protected function _getLanguageCode()
    {
    }

    /**
     * Returns this locale string
     *
     * @return string
     */
    protected function _getLocale()
    {
    }

    /**
     * Returns this class currency code
     *
     * @return string
     */
    protected function _getCurrencyCode()
    {
    }

    /**
     * Returns this class store id
     *
     * @return int
     */
    protected function _getStoreId()
    {
    }

    /**
     * Returns this class payment method
     *
     * @return string
     */
    public function getMethod()
    {
    }

    /**
     * Returns the order set in this class
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
    }

    /**
     * Returns the creditmemo set in this class
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
    }

    /**
     * Returns the invoice set in this class
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getInvoice()
    {
    }

    /**
     * Returns the payment set in this class
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function getPayment()
    {
    }

    /**
     * Returns the info instance set in this class
     *
     * @return Mage_Payment_Model_Info
     */
    public function getInfoInstance()
    {
    }

    /**
     * Returns the quote set in this class
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
    }

    /**
     * Returns the billing address set in this class
     *
     * @return Mage_Customer_Model_Address_Abstract
     */
    public function getBillingAddress()
    {
    }

    /**
     * Returns the shipping address set in this class
     *
     * @return Mage_Customer_Model_Address_Abstract
     */
    public function getShippingAddress()
    {
    }

    protected function _formatPrice($price)
    {
    }

    /**
     * Returns the current payment methods title, as set in Klarna Payment settings
     *
     * @return string
     */
    public function getMethodTitleWithFee($fee = null, $presetTitle = null)
    {
    }

    /**
     * A function that returns a few setup values unique to the current active session
     * If currently selected method is not setup it will default to Invoice method and try again
     * It uses recursion, but can only call itself once
     *
     * Klarna checkout is different, if one places an order with KCO and then disables the option, it should
     * still be possible to create the setup of it.
     *
     * @return Varien_Object
     */
    public function getKlarnaSetup()
    {
    }

    /**
     * Creates the path to the Klarna logotype, it depends on payment method, intended placemen and your merchant id
     *
     * @param $width the width of the logotype
     * @param $position const defined in Klarna Helper (checkout, product or frontpage)
     * @param $type optional const defined in Klarna Helper (invoice, account, both) if not provided, it will look at current payment method to figure it out
     *
     * @return string containing the full path to image
     */
    public function getKlarnaLogotype($width, $position, $type = null)
    {
    }

}

abstract class Vaimo_Klarna_Model_Klarnacheckout_Abstract extends Vaimo_Klarna_Model_Transport_Abstract
{
    protected $_coreHttpHelper = null;
    protected $_coreUrlHelper = null;
    protected $_customerHelper = null;

    public function __construct(
        $setStoreInfo = true,
        $moduleHelper = null,
        $coreHttpHelper = null,
        $coreUrlHelper = null,
        $customerHelper = null
    ) {
    }

    protected function _getCoreHttpHelper()
    {
    }

    protected function _getCoreUrlHelper()
    {
    }

    protected function _getCustomerHelper()
    {
    }

    protected function _loadQuoteByKey($id, $key)
    {
    }

    protected function _loadOrderByKey($id, $key = 'quote_id')
    {
    }

    protected function _findAlreadyCreatedOrder($id)
    {
    }

    protected function _loadCustomer($id)
    {
    }

    protected function _getCustomerSession()
    {
    }

    protected function _loadCustomerByEmail($email, $store)
    {
    }

    protected function _loadCustomerAddress($id)
    {
    }

    protected function _getServiceQuote($quote)
    {
    }

    protected function _addToSubscription($email)
    {
    }

    protected function _addressIsSame($billingAddress, $shippingAddress)
    {
    }

    protected function _customerHasAddress($customer, $address)
    {
    }

    protected function _prepareGuestCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
    }

    protected function _prepareNewCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
    }

    protected function _prepareCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
    }

    protected function _involveNewCustomer(Mage_Sales_Model_Quote $quote)
    {
    }

}

class Vaimo_Klarna_Model_Klarnacheckout extends Vaimo_Klarna_Model_Klarnacheckout_Abstract
{
    protected $_api = null;

    public function __construct(
        $setStoreInfo = true,
        $moduleHelper = null,
        $coreHttpHelper = null,
        $coreUrlHelper = null,
        $customerHelper = null
    ) {
    }

    /**
     * Function added for Unit testing
     *
     * @param $apiObject
     */
    public function setApi($apiObject)
    {
    }

    /**
     * Will return the API object, it set, otherwise null
     */
    public function getApi()
    {
    }

    /**
     * Could have been added to getApi, but I made it separate for Unit testing
     *
     * @param $storeId
     * @param $method
     * @param $functionName
     */
    protected function _initApi($storeId, $method, $functionName)
    {
    }

    /**
     * Init funcition
     *
     * @todo If storeid is null, we need to find first store where Klarna is active, not just trust that default store has it active...
     */
    protected function _init($functionName)
    {
    }


    public function getKlarnaOrderHtml(
        $checkoutId = null,
        $createIfNotExists = false,
        $updateItems = false
    ) {
    }

    /**
     * When we call this function, order is already done and complete. We can then cache
     * the information we get from Klarna so when we call initKlarnaOrder again (from
     * phtml files) we can use the cached order instead of fetching it again.
     *
     * @param string $checkoutId
     *
     * @return string
     */
    public function getCheckoutStatus($checkoutId = null, $useCurrentSession = true)
    {
    }

    /*
     * Not happy with this, but I guess we can't solve it in other ways.
     *
     */
    public function getActualKlarnaOrder()
    {
    }

    public function getActualKlarnaOrderArray()
    {
    }

    /**
     * Will return the klarna order or null, if it doesn't find it
     * Not used by this module, but as a service for others.
     * @return array
     */
    public function getKlarnaOrderRaw($checkoutId)
    {
    }

    protected function _reduceParentItem($quote, $id, $qty)
    {
    }

    protected function _checkItems($quote, $adjustFlag = false)
    {
    }

    public function validateQuote(
        $checkoutId,
        $createOrderOnValidate = null,
        $createdKlarnaOrder = null,
        $logInfo = 'validate'
    ) {
    }

    public function successQuote($checkoutId, $createOrderOnSuccess, $createdKlarnaOrder)
    {
    }

    /**
     * This function checks valid shippingMethod
     *
     * There must be a better way...
     *
     * @return $this
     *
     */
    public function checkShippingMethod()
    {
    }

    protected function _checkQuote($quote, $createdKlarnaOrder)
    {
    }

    protected function _updateKlarnaOrderAddress($createdKlarnaOrder)
    {
    }

    protected function _createTheOrder(
        $quote,
        $createdKlarnaOrder,
        $updatef,
        $pushf,
        $noticeTextArr = null
    ) {
    }

    protected function _createValidateOrder($checkoutId, $quote, $createdKlarnaOrder, $logInfo)
    {
    }

    public function createOrder($checkoutId = null, $force = true)
    {
    }

    public function getKlarnaCheckoutEnabled()
    {
    }

    public function updateTaxAndShipping($quote, $data)
    {
    }

    public function checkNewsletter()
    {
    }

    protected function _getTransport()
    {
    }

}

class Vaimo_Klarna_Helper_Data extends Mage_Core_Helper_Abstract
{
    const KLARNA_METHOD_INVOICE = 'vaimo_klarna_invoice';
    const KLARNA_METHOD_ACCOUNT = 'vaimo_klarna_account';
    const KLARNA_METHOD_SPECIAL = 'vaimo_klarna_special';
    const KLARNA_METHOD_CHECKOUT = 'vaimo_klarna_checkout';

    const KLARNA_API_CALL_RESERVE = 'reserve';
    const KLARNA_API_CALL_CAPTURE = 'capture';
    const KLARNA_API_CALL_REFUND = 'refund';
    const KLARNA_API_CALL_CANCEL = 'cancel';
    const KLARNA_API_CALL_CHECKSTATUS = 'check_status';
    const KLARNA_API_CALL_ADDRESSES = 'addresses';
    const KLARNA_API_CALL_PCLASSES = 'pclasses';
    const KLARNA_API_CALL_CHECKOUTSERVICES = 'checkout_services';

    const KLARNA_API_CALL_KCODISPLAY_ORDER = 'kco_display_order';
    const KLARNA_API_CALL_KCOCREATE_ORDER = 'kco_create_order';
    const KLARNA_API_CALL_KCOVALIDATE_ORDER = 'kco_validate_order';

    const KLARNA_KCO_QUEUE_RETRY_ATTEMPTS = 10;

    const KLARNA_STATUS_ACCEPTED = 'accepted';
    const KLARNA_STATUS_PENDING = 'pending';
    const KLARNA_STATUS_DENIED = 'denied';

    const KLARNA_INFO_FIELD_FEE = 'vaimo_klarna_fee';
    const KLARNA_INFO_FIELD_FEE_TAX = 'vaimo_klarna_fee_tax';
    const KLARNA_INFO_FIELD_BASE_FEE = 'vaimo_klarna_base_fee';
    const KLARNA_INFO_FIELD_BASE_FEE_TAX = 'vaimo_klarna_base_fee_tax';
    const KLARNA_INFO_FIELD_FEE_CAPTURED_TRANSACTION_ID = 'klarna_fee_captured_transaction_id';
    const KLARNA_INFO_FIELD_FEE_REFUNDED = 'klarna_fee_refunded';

    const KLARNA_INFO_FIELD_RESERVATION_STATUS = 'klarna_reservation_status';
    const KLARNA_INFO_FIELD_RESERVATION_ID = 'klarna_reservation_id';
    const KLARNA_INFO_FIELD_CANCELED_DATE = 'klarna_reservation_canceled_date';
    const KLARNA_INFO_FIELD_REFERENCE = 'klarna_reservation_reference';
    const KLARNA_INFO_FIELD_ORDER_ID = 'klarna_reservation_order_id';
    const KLARNA_INFO_FIELD_INVOICE_LIST = 'klarna_invoice_list';
    const KLARNA_INFO_FIELD_INVOICE_LIST_STATUS = 'invoice_status';
    const KLARNA_INFO_FIELD_INVOICE_LIST_ID = 'invoice_id';
    const KLARNA_INFO_FIELD_INVOICE_LIST_KCO_ID = 'invoice_kco_id';
    const KLARNA_INFO_FIELD_HOST = 'klarna_reservation_host';
    const KLARNA_INFO_FIELD_MERCHANT_ID = 'merchant_id';
    const KLARNA_INFO_FIELD_NOTICE = 'klarna_notice';

    const KLARNA_INFO_FIELD_PAYMENT_PLAN = 'payment_plan';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_TYPE = 'payment_plan_type';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHS = 'payment_plan_months';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_START_FEE = 'payment_plan_start_fee';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_INVOICE_FEE = 'payment_plan_invoice_fee';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_TOTAL_COST = 'payment_plan_total_cost';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHLY_COST = 'payment_plan_monthly_cost';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_DESCRIPTION = 'payment_plan_description';

    const KLARNA_FORM_FIELD_PHONENUMBER = 'phonenumber';
    const KLARNA_FORM_FIELD_PNO = 'pno';
    const KLARNA_FORM_FIELD_ADDRESS_ID = 'address_id';
    const KLARNA_FORM_FIELD_DOB_YEAR = 'dob_year';
    const KLARNA_FORM_FIELD_DOB_MONTH = 'dob_month';
    const KLARNA_FORM_FIELD_DOB_DAY = 'dob_day';
    const KLARNA_FORM_FIELD_CONSENT = 'consent';
    const KLARNA_FORM_FIELD_GENDER = 'gender';
    const KLARNA_FORM_FIELD_EMAIL = 'email';

    const KLARNA_API_RESPONSE_STATUS = 'response_status';
    const KLARNA_API_RESPONSE_TRANSACTION_ID = 'response_transaction_id';
    const KLARNA_API_RESPONSE_FEE_REFUNDED = 'response_fee_refunded';
    const KLARNA_API_RESPONSE_FEE_CAPTURED = 'response_fee_captured';
    const KLARNA_API_RESPONSE_KCO_CAPTURE_ID = 'response_kco_capture_id';
    const KLARNA_API_RESPONSE_KCO_LOCATION = 'response_kco_location';

    const KLARNA_LOGOTYPE_TYPE_INVOICE = 'invoice';
    const KLARNA_LOGOTYPE_TYPE_ACCOUNT = 'account';
    const KLARNA_LOGOTYPE_TYPE_CHECKOUT = 'checkout';
    const KLARNA_LOGOTYPE_TYPE_BOTH = 'unified';
    const KLARNA_LOGOTYPE_TYPE_BASIC = 'basic';

    const KLARNA_FLAG_ITEM_NORMAL = "normal";
    const KLARNA_FLAG_ITEM_SHIPPING_FEE = "shipping";
    const KLARNA_FLAG_ITEM_HANDLING_FEE = "handling";

    const KLARNA_REFUND_METHOD_FULL = "full";
    const KLARNA_REFUND_METHOD_PART = "part";
    const KLARNA_REFUND_METHOD_AMOUNT = "amount";

    const KLARNA_LOGOTYPE_POSITION_FRONTEND = 'frontend';
    const KLARNA_LOGOTYPE_POSITION_PRODUCT = 'product';
    const KLARNA_LOGOTYPE_POSITION_CHECKOUT = 'checkout';

    const KLARNA_DISPATCH_RESERVED = 'vaimo_paymentmethod_order_reserved';
    const KLARNA_DISPATCH_CAPTURED = 'vaimo_paymentmethod_order_captured';
    const KLARNA_DISPATCH_REFUNDED = 'vaimo_paymentmethod_order_refunded';
    const KLARNA_DISPATCH_CANCELED = 'vaimo_paymentmethod_order_canceled';

    const KLARNA_LOG_START_TAG = '---------------START---------------';
    const KLARNA_LOG_END_TAG = '----------------END----------------';

    const KLARNA_EXTRA_VARIABLES_GUI_OPTIONS = 0;
    const KLARNA_EXTRA_VARIABLES_GUI_LAYOUT = 1;
    const KLARNA_EXTRA_VARIABLES_OPTIONS = 2;

    const KLARNA_KCO_API_VERSION_STD = 2;
    const KLARNA_KCO_API_VERSION_UK = 3;
    const KLARNA_KCO_API_VERSION_USA = 4;

    public static $isEnterprise;


    protected $_supportedMethods = array(
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_INVOICE,
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_ACCOUNT,
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_SPECIAL,
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT
    );

    protected $_klarnaFields = array(
        self::KLARNA_INFO_FIELD_FEE,
        self::KLARNA_INFO_FIELD_FEE_TAX,
        self::KLARNA_INFO_FIELD_BASE_FEE,
        self::KLARNA_INFO_FIELD_BASE_FEE_TAX,
        self::KLARNA_INFO_FIELD_FEE_CAPTURED_TRANSACTION_ID,
        self::KLARNA_INFO_FIELD_FEE_REFUNDED,

        self::KLARNA_INFO_FIELD_RESERVATION_STATUS,
        self::KLARNA_INFO_FIELD_RESERVATION_ID,
        self::KLARNA_INFO_FIELD_CANCELED_DATE,
        self::KLARNA_INFO_FIELD_REFERENCE,
        self::KLARNA_INFO_FIELD_ORDER_ID,
        self::KLARNA_INFO_FIELD_INVOICE_LIST,
        self::KLARNA_INFO_FIELD_INVOICE_LIST_STATUS,
        self::KLARNA_INFO_FIELD_INVOICE_LIST_ID,
        self::KLARNA_INFO_FIELD_HOST,
        self::KLARNA_INFO_FIELD_MERCHANT_ID,

        self::KLARNA_INFO_FIELD_PAYMENT_PLAN,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_TYPE,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHS,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_START_FEE,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_INVOICE_FEE,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_TOTAL_COST,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHLY_COST,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_DESCRIPTION,

        self::KLARNA_FORM_FIELD_PHONENUMBER,
        self::KLARNA_FORM_FIELD_PNO,
        self::KLARNA_FORM_FIELD_ADDRESS_ID,
        self::KLARNA_FORM_FIELD_DOB_YEAR,
        self::KLARNA_FORM_FIELD_DOB_MONTH,
        self::KLARNA_FORM_FIELD_DOB_DAY,
        self::KLARNA_FORM_FIELD_CONSENT,
        self::KLARNA_FORM_FIELD_GENDER,
        self::KLARNA_FORM_FIELD_EMAIL,

    );

    const KLARNA_CHECKOUT_ENABLE_NEWSLETTER = 'payment/vaimo_klarna_checkout/enable_newsletter';
    const KLARNA_CHECKOUT_EXTRA_ORDER_ATTRIBUTE = 'payment/vaimo_klarna_checkout/extra_order_attribute';
    const KLARNA_CHECKOUT_ENABLE_CART_ABOVE_KCO = 'payment/vaimo_klarna_checkout/enable_cart_above_kco';

    const KLARNA_CHECKOUT_NEWSLETTER_DISABLED = 0;
    const KLARNA_CHECKOUT_NEWSLETTER_SUBSCRIBE = 1;
    const KLARNA_CHECKOUT_NEWSLETTER_DONT_SUBSCRIBE = 2;

    const KLARNA_CHECKOUT_ALLOW_ALL_GROUP_ID = 99;

    const KLARNA_DISPATCH_VALIDATE = 'vaimo_klarna_validate_cart';
    const KLARNA_VALIDATE_ERRORS = 'klarna_validate_errors';

    const ENCODING_MAGENTO = 'UTF-8';
    const ENCODING_KLARNA = 'ISO-8859-1';

    /**
     * The name in SESSION variable of the function currently executing, only used for logs
     */
    const LOG_FUNCTION_SESSION_NAME = 'klarna_log_function_name';

    /**
     * Encode the string to klarna encoding
     *
     * @param string $str string to encode
     * @param string $from from encoding
     * @param string $to target encoding
     *
     * @return string
     */
    public function encode($str, $from = null, $to = null)
    {
    }

    /**
     * Decode the string to the Magento encoding
     *
     * @param string $str string to decode
     * @param string $from from encoding
     * @param string $to target encoding
     *
     * @return string
     */
    public function decode($str, $from = null, $to = null)
    {
    }

    public function getSupportedMethods()
    {
    }

    public function isKlarnaField($key)
    {
    }

    public function isMethodKlarna($method)
    {
    }

    public function getInvoiceLink($order, $transactionId)
    {
    }

    public function shouldItemBeIncluded($item)
    {
    }

    public function isShippingInclTax($storeId)
    {
    }

    /**
     * Check if OneStepCheckout is activated or not
     * It also checks if OneStepCheckout is activated, but it's currently using
     * standard checkout
     *
     * @return bool
     */
    public function isOneStepCheckout($store = null)
    {
    }

    /**
     * Returns checkout/cart unless specific redirect specified
     *
     */
    public function getKCORedirectToCartUrl($store = null)
    {
    }

    /**
     * Check if FireCheckout is activated or not
     *
     * @return bool
     */
    public function isFireCheckout($store = null)
    {
    }

    /**
     * Check if VaimoCheckout is activated or not
     *
     * @return bool
     */
    public function isVaimoCheckout($store = null)
    {
    }

    /**
     * Check if Vaimo_QuickCheckout is activated or not
     *
     * @return bool
     */
    public function isQuickCheckout($store = null)
    {
    }

    /*
     * Last minute change. We were showing logotype instead of title, but the implementation was not
     * as good as we wanted, so we reverted it and will make it a setting. This function will be the
     * base of that setting. If it returns false, we should show the logotype together with the title
     * otherwise just show the title.
     */
    public function showTitleAsTextOnly()
    {
    }

    /**
     * Check if OneStepCheckout displays their prises with the tax included
     *
     * @return bool
     */
    public function isOneStepCheckoutTaxIncluded()
    {
    }

    protected function _feePriceIncludesTax($store = null)
    {
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param null $store
     * @return mixed
     */
    protected function _getVaimoKlarnaFeeForMethod($quote, $store, $force = false)
    {
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param $store
     * @return int
     */
    protected function _getVaimoKlarnaFee($quote, $store, $force = false, $inBaseCurrency = true)
    {
    }

    /**
     * Returns the label set for fee
     *
     * @param $store
     * @return string
     */
    public function getKlarnaFeeLabel($store = null)
    {
    }

    /**
     * Returns the tax class for invoice fee
     *
     * @param $store
     * @return string
     */
    public function getTaxClass($store)
    {
    }

    /**
     * Returns the payment fee excluding VAT
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return float
     */
    public function getVaimoKlarnaFeeExclVat($shippingAddress)
    {
    }

    /**
     * Returns the payment fee tax for the payment fee
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return float
     */
    public function getVaimoKlarnaFeeVat($shippingAddress)
    {
    }

    /**
     * Returns the payment fee tax rate
     *
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getVaimoKlarnaFeeVatRate($order)
    {
    }

    /**
     * Returns the payment fee including VAT, this function doesn't care about method or shipping address country
     * It's striclty for informational purpouses
     *
     * @return float
     */
    public function getVaimoKlarnaFeeInclVat($quote, $inBaseCurrency = true)
    {
    }

    /*
     * The following functions shouldn't really need to exist...
     * Either I have done something wrong or the versions have changed how they work...
     *
     */

    /*
     * Add tax to grand total on invoice collect or not
     */
    public function collectInvoiceAddTaxToInvoice()
    {
    }

    /*
     * Call parent of quote collect or not
     */
    public function collectQuoteRunParentFunction()
    {
    }

    /*
     * Use extra tax in quote instead of adding to Tax, I don't know why this has to be
     * different in EE, but it clearly seems to be...
     */
    public function collectQuoteUseExtraTaxInCheckout()
    {
    }


// KLARNA CHECKOUT FROM NOW

    protected function _addressMatch(array $address1, array $address2)
    {
    }

    public function getCustomerAddressId($customer, $addressData)
    {
    }

    public function getExtraOrderAttributeCode()
    {
    }

    public function excludeCartInKlarnaCheckout()
    {
    }

    /*
     *
     *
     */
    public function dispatchReserveInfo($order, $pno)
    {
    }

    /*
     * Whenever a refund, capture, reserve or cancel is performed, we send out an event
     * This can be listened to for financial reconciliation
     *
     * @return void
     */
    public function dispatchMethodEvent($order, $eventcode, $amount, $method)
    {
    }

    public function SplitJsonStrings($json)
    {
    }

    public function JsonDecode($json)
    {
    }

    public function getTermsUrlLink($url)
    {
    }

    public function getTermsUrl($url)
    {
    }

    /**
     * Sets the function name, which is used in logs. This is set in each class construct
     *
     * @param string $functionName
     *
     * @return void
     */
    public function setFunctionNameForLog($functionName)
    {
    }

    /**
     * Returns the function name set by the constructors in each class
     *
     * @return string
     */
    public function getFunctionNameForLog()
    {
    }

    /**
     * Log function that does the writing to log file
     *
     * @param string $filename What file to write to, will be placed in site/var/klarna/ folder
     * @param string $msg Text to log
     *
     * @return void
     */
    protected function _log($filename, $msg)
    {
    }

    /**
     * Log function that does the writing to log file
     *
     * @param string $filename What file to write to, will be placed in site/var/klarna/ folder
     * @param string $msg Text to log
     *
     * @return void
     */
    protected function _logAlways($filename, $msg)
    {
    }

    /**
     * Log function that logs all Klarna API calls and replies, this to see what functions are called and what reply they get
     *
     * @param string $comment Text to log
     *
     * @return void
     */
    public function logKlarnaApi($comment)
    {
    }

    /**
     * Log function used for various debug log information, array is optional
     *
     * @param string $info Header of what is being logged
     * @param array $arr The array to be logged
     *
     * @return void
     */
    public function logDebugInfo($info, $arr = null)
    {
    }

    protected function _logMagentoException($e)
    {
    }

    /**
     * If there is an exception, this log function should be used
     * This is mainly meant for exceptions concerning klarna API calls, but can be used for any exception
     *
     * @param Exception $e
     *
     * @return void
     */
    public function logKlarnaException($e)
    {
    }

    public function getDefaultCountry($store = null)
    {
    }

    public function isEnterpriseAndHasClass($class = null)
    {
    }

    /**
     * Escape quotes inside html attributes
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     */
    public function quoteEscape($data, $addSlashes = false)
    {
    }

    public function findQuote($klarna_id)
    {
    }

    /**
     * Check if a product is a dynamic bundle product and reset a price.
     *
     * @param $item // Might not be Mage_Sales_Model_Quote_Item...
     * @param  Mage_Catalog_Model_Product|null $product
     * @return bool
     */
    public function checkBundles(&$item, $product = null)
    {
    }

    public static function isEnterprise()
    {
    }

    protected function _isAdminUserLoggedIn()
    {
    }

    public function prepareVaimoKlarnaFeeRefund(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
    }

    public function checkPaymentMethod($quote, $logf = false)
    {
    }

    /**
     * Check if exception is triggered by local XML-RPC library
     *
     * @param  Exception $e
     * @return boolean
     */
    public function isXmlRpcException(Exception $e)
    {
    }
}

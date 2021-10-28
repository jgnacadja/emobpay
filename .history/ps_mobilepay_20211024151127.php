<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_MobilePay extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        parent::__construct();
        $this->name = 'ps_mobilepay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'RINTIO Company';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        $this->displayName = $this->l('Prestahop Mobile Payment gateway');
        $this->description = $this->l(
            'Acceptez des paiements par Mobile Money via la plateforme E-Mobpay'.
            "<a href='https://emobpay.rintio.com' target='_blank'>En savoir plus </a>"
        );

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn')) {
            return false;
        }
        return true;
    }
    public function uninstall()
    {
        return (!parent::uninstall() || !Configuration::deleteByName('ps_mobilepay')) ? false : true;
    }
    public function hookPaymentOptions($params)
    {
        /*
        * 2021 PrestaShop Mobi
        * --------------------------------
        * --------------------------------
        *   
        */
        if ($this->active && $this->checkCurrency($params['cart'])) {
            return [ $this->getMomoPaymentOption()];
        }
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }


    public function getMomoPaymentOption()
    {
        $MomoOption = new PaymentOption();
        $MomoOption->setCallToActionText($this->l('Paiement Mobile '))
                     ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                     ->setAdditionalInformation($this->context->smarty->fetch('module:ps_mobilepay/views/templates/front/payment_infos.tpl'));
        // ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.jpg'));
        // ->setLogo(_MODULE_DIR_.'paymentexample/views/img/logo.png');

        return [$MomoOption];
    }
}
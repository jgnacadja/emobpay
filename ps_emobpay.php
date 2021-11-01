<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_EmobPay extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'ps_emobpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Jacques GNACADJA';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Passerelle E-MobPay');
        $this->description = $this->l(
            'Acceptez des paiements par Mobile Money via la plateforme E-Mobpay'
        );
    }

    /**
     * Returns a string containing the HTML necessary to
     * generate a configuration screen on the admin
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_html;
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
        return (parent::uninstall()
               && Configuration::deleteByName($this->name))?
               true : false;
    }
    public function hookPaymentOptions($params)
    {
        /*
        * 2021 PrestaShop Mobile money payment options
        * --------------------------------
        * --------------------------------
        * Setting new payment options for administration purposes
        */
        
        if (!$this->active) {
            return;
        }

        // if (!$this->checkCurrency($params['cart'])) {
        //     return;
        // }

        $payment_options = [
            $this->getMomoPaymentOption()
            
        ];

        return $payment_options;
    }
    
    public function hookPaymentReturn($params)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active) {
            return;
        }
 
        return $this->fetch(
            'module:ps_emobpay/views/templates/front/payment_return.tpl'
        );
    }
    
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        $isdefined = true;
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                $isdefined &= ($currency_order->id == $currency_module['id_currency']);
            }
        }
        return $isdefined;
    }


    public function getMomoPaymentOption()
    {
        $MomoOption = new PaymentOption();
        $MomoOption->setCallToActionText($this->l('Paiement Mobile'))
                     ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                     ->setAdditionalInformation($this->context->smarty->fetch('module:ps_emobpay/views/templates/front/payment_infos.tpl'))
                     ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/e.png'));
        return $MomoOption;
    }
}
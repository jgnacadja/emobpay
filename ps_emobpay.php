<?php
/**
 * MyClass Class Doc Comment
 *
 * @category Class
 * @package  Emobpay
 * @author   jgnacadja <unis.gnacadja@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/jgnacadja
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_EmobPay extends PaymentModule
{
    protected $html = '';
    protected $postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    /**
     * Modudule EmobPay identifier process class.
     * Send data to external  payement UI
     *
     * @return
     **/
    public function __construct()
    {
        $this->name = 'ps_emobpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Jacques GNACADJA';
        $this->controllers = array('momo', 'validationapi');
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
        return $this->html;
    }
    
    /**
     * Modudule installer .
     *
     * @return Boolean
     **/
    public function install()
    {
        if (!$this->setConfig()) {
            return false;
        }

        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn');
    }

    /**
     * DataBAse config setting
     *
     *
     * **/
    protected function setConfig()
    {
        $newOrderState = array(
                'invoice' => 0,
                'send_email' => 0,
                'module_name' => $this->name,
                'color' => '#E6661E',
                'unremovable' => 0,
                'hidden' => 0,
                'logable' => 0,
                'delivery' => 0,
                'shipped' => 0,
                'paid' => 0,
                'pdf_invoice'=>0,
                'pdf_delivery'=>0,
                'deleted' => 0);

        $db = \Db::getInstance();

        if (!$db->insert('order_state', $newOrderState)) {
            return false;
        }

        $id_order_state = (int)$db->Insert_ID();

        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $db->insert(
                'order_state_lang',
                array(
                    'id_order_state'=>$id_order_state,
                    'id_lang'=>$language['id_lang'],
                    'name'=>'En attente de paiement (Emobpay)',
                    'template'=>''
                )
            );
        }

        if (!@copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'state.gif', _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'os'.DIRECTORY_SEPARATOR.$id_order_state.'.gif')) {
            return false;
        }

        Configuration::updateValue('PS_OS_WAITING', $id_order_state);

        unset($id_order_state);

        return true;
    }

    /**
     * Modudule Confiuration unset  .
     *
     * @return Boolean
    **/
    protected function unSetConfig()
    {
        $db = \Db::getInstance();
        $success = true;
        $request = 'SELECT `id_order_state`,`module_name`   FROM `' . _DB_PREFIX_ . 'order_state` ';

        $result = $db->executeS($request);

        foreach ($result as &$row) {
            if ($row['module_name']===$this->name) {
                $idOrderState = $row['id_order_state'];
                $success = $success && $db->delete('order_state_lang', 'id_order_state ='.$idOrderState);
                $success = $success && $db->delete('order_state', 'id_order_state ='.$idOrderState);
            }
        }
   
        unset($db);
        return $success;
    }


    /**
     * Modudule Uninstaller .
     *
     * @return Boolean
     **/
    public function uninstall()
    {
        if (!$this->unSetConfig()) {
            return false;
        }

        return (parent::uninstall()
               && Configuration::deleteByName($this->name)

               )?
               true : false;
    }

    /**
     * Hook Payment . identifier
     *
     * @return $payment_Option
     **/
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
        $payment_options = [
            $this->getMomoPaymentOption()
            
        ];

        return $payment_options;
    }
    

    /**
     * Hired function for payment return process .
     *
     * @param $params used in payment process
     *
     * @return Boolean
     **/
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
                     ->setAction($this->context->link->getModuleLink($this->name, 'momo', array(), true))
                     ->setAdditionalInformation(
                         $this->context->smarty->fetch(
                             'module:ps_emobpay/views/templates/front/payment_infos.tpl'
                         )
                     )
                     ->setLogo(
                         Media::getMediaPath(
                             _PS_MODULE_DIR_.$this->name.'/views/img/e.png'
                         )
                     );
        return $MomoOption;
    }
}

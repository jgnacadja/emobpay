<?php
session_start();
class MobilePayMomoModuleFrontController extends ModuleFrontController
{
    /*
     * payement process class.
     * Send data to external  payement UI
     */
            

    public function initContent()
    {
        parent::initContent();
        
            
        $cart = $this->context->cart;
        $total = (int)$cart->getOrderTotal(true, Cart::BOTH);
        $url = Context::getContext()->link->getModuleLink('mobilepay', 'validationAPI', array());
        
        $this->context->smarty->assign([
            
            'total' => $total,
            'validationAPI' => $url,
            
        ]);

        $encrypt = md5($total);
        $url .= $_SERVER['REQUEST_URI'];
        $techno = 'Prestashop' ;
        

        $token= Tools::getToken(false);
        Tools::redirect("https://emobpay.rintio.com/?".'data='.$encrypt.'&token='.$token.'&path='.$url.'&techno='.$techno);
    }
}
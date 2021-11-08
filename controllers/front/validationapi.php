<?php



class ps_emobpayvalidationapiModuleFrontController extends ModuleFrontController
{
    public function initcontent()
    {
        parent::initContent();


        if (!(isset($_GET['status'])&& isset($_GET['orderID']))) {
            return ;
        }
        $order = new Order((int)$_GET['orderID']);
        if ($_GET['status'] == '500') {
            $order->setCurrentState((int)Configuration::get('PS_OS_ERROR'));
        } else {
            $order->setCurrentState((int)Configuration::get('PS_OS_PAYMENT'));
            $order->setCurrentState((int)Configuration::get('PS_OS_PREPARATION'));
        }
        $this->context->smarty->assign('paymentError', ($order->current_state===(int)Configuration::get('PS_OS_ERROR')));
        $this->setTemplate('module:ps_emobpay/views/templates/front/payment_return.tpl');
    }
}

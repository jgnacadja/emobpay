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

        $history = new OrderHistory();
        $history->id_order = (int)$order->id;
        
        try {
            if ($_GET['status'] == '500') {
                $order->setCurrentState((int)Configuration::get('PS_OS_ERROR'));

            // $history->changeIdOrderState((int) Configuration::get('PS_OS_ERROR'), (int)$order->id); //order status=3
            } else {
                $order->setCurrentState((int)Configuration::get('PS_OS_PAYMENT'));
                //$history->changeIdOrderState((int) Configuration::get('PS_OS_PAYMENT'), (int)$order->id);


                $order->setCurrentState((int)Configuration::get('PS_OS_PREPARATION'));
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        

        $this->context->smarty->assign(
            'paymentSuccess',
            ($order->current_state!=(int)Configuration::get('PS_OS_ERROR'))
        );
        $this->setTemplate('module:ps_emobpay/views/templates/front/payment_return.tpl');
    }
}

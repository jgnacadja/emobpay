<?php



class ps_emobpayvalidationapiModuleFrontController extends ModuleFrontController
{
    public function initcontent()
    {

        if (!(isset($_GET['status'])&& isset($_GET['orderID']))) {
            return ;
        }

        

        $history = new OrderHistory();
        $history->id_order = (int) $_GET['orderID'];
        $order = new Order((int)$_GET['orderID']);
        if ($_GET['status'] == '500') {
            $order->setCurrentState((int)Configuration::get('PS_OS_ERROR'));
           
        } else {
            $history->changeIdOrderState(
                8,                  // payment rejected 
                $history->id_order
            );
            $history->changeIdOrderState(
                2,                           // payment accepted 
                $history->id_order
            );
            $order->setCurrentState((int)Configuration::get('PS_OS_PAYMENT'));
            $history->changeIdOrderState(
                3,                           // processing in progress
                $history->id_order
            ); 
            $order->setCurrentState((int)Configuration::get('PS_OS_PREPARATION'));
        }
        

        Tools::redirect(
            'index.php?controller=history'
        );
    }
}
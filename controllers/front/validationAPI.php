<?php



class MobilePayValidationAPIModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'mobilepay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $this->context->smarty->assign([
            'params' => $_REQUEST,
        ]);
        
       
       if(isset($_GET['status']))
       {
            $status = $_GET['status'];

            $order_status = Configuration::get('PS_OS_ERROR');
            
            if($status == '202')
                $order_status = Configuration::get('PS_OS_PAYMENT');
            
            $this->module->validateOrder(
                    (int) $this->context->cart->id,
                    $order_status,
                    (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
                    $this->module->displayName,
                    null,
                    null,
                    (int) $this->context->currency->id,
                    false
                    
            );   
            Tools::redirect('index.php?controller=order-detail&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder);
        
        }
       
        
        
    }



} 


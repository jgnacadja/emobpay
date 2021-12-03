<?php



class ps_emobpayvalidationapiModuleFrontController extends ModuleFrontController
{
    public function initcontent()
    {
        parent::initContent();

        // define payment succes condition
        $hasPayementSuccess = true;
        $hasPayementSuccess &= isset($_GET['status'])&& isset($_GET['card']);
        $hasPayementSuccess &= ($_GET['status'] != '500');

        $idCard = $_GET['card'];
        $cart = $this->context->cart;
        $hasPayementSuccess &= $idCard == $cart->id;  //context cardID must be returned card ID

        $customer = new Customer($cart->id_customer);
        $total = (int)$cart->getOrderTotal(true, Cart::BOTH);
        
        // returned amount paid must be the context amount to pay

        $hasPayementSuccess &= isset($_GET['amount']) && ($_GET['amount']==$total); 

       

        if (!$hasPayementSuccess) {
            $this->module->validateOrder(
                (int) $idCard,
                Configuration::get('PS_OS_ERROR'), 
                $total, // get card amount
                $this->module->displayName,
                null,
                null,
                (int) $this->context->currency->id,
                false,
                $customer->secure_key
            );
        } else {
            $this->module->validateOrder(
                (int) $idCard,
                Configuration::get('PS_OS_PAYMENT'), // En attente de paiemnt
                $total, // get card amount
                $this->module->displayName,
                null,
                null,
                (int) $this->context->currency->id,
                false,
                $customer->secure_key
            );

            // automatically setting new state for order status
            $order = new Order((int)Order::getOrderByCartId($idCard));
            $order->setCurrentState((int)Configuration::get('PS_OS_PREPARATION'));
            unset($order);
        }

        $order = new Order((int)Order::getOrderByCartId($idCard));
        unset($order);

        $this->context->smarty->assign(
            'paymentSuccess', $hasPayementSuccess
        );
        $this->setTemplate(
            'module:ps_emobpay/views/templates/front/payment_return.tpl'
        );
    }
}

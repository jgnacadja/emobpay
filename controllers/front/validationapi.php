<?php



class ps_emobpayvalidationapiModuleFrontController extends ModuleFrontController
{
    public function initcontent()
    {
        parent::initContent();


        if (!(isset($_GET['status'])&& isset($_GET['card']))) {
            return ;
        }
        //;

        $idCard = $_GET['card'];
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $total = (int)$cart->getOrderTotal(true, Cart::BOTH);

       

        if ($_GET['status'] == '500') {
            $this->module->validateOrder(
                (int) $idCard,
                Configuration::get('PS_OS_ERROR'), // En attente de paiemnt
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
        }

        $order = new Order((int)Order::getOrderByCartId($idCard));

        $this->context->smarty->assign(
            'paymentSuccess',
            ($order->current_state!=(int)Configuration::get('PS_OS_ERROR'))
        );
        $this->setTemplate('module:ps_emobpay/views/templates/front/payment_return.tpl');
    }
}

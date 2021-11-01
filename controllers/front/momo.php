<?php
/**
 * MyClass Class Doc Comment
 *
 * @category Class
 * @package  MobileMoney
 * @author   jgnacadja <unis.gnacadja@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/jgnacadja
 */
class ps_emobpaymomoModuleFrontController extends ModuleFrontController
{
    /**
     * Payement process class.
     * Send data to external  payement UI
     * 
     * @return string
     **/       
    public function initContent()
    {
        parent::initContent();
        
            
        $cart = $this->context->cart;
        $total = (int)$cart->getOrderTotal(true, Cart::BOTH);
        $url = Context::getContext()->link->getModuleLink(
            'mobilepay', 'validationAPI', array()
        );
        
        $this->context->smarty->assign(
            [
            'total' => $total,
            'validationAPI' => $url]
        );

        $encrypt = md5($total);
        $url .= $_SERVER['REQUEST_URI'];
        $techno = 'Prestashop' ;
        

        $token= Tools::getToken(false);
        Tools::redirect(
            "https://emobpay.rintio.com/?".
            'data='.$encrypt.'&token='.
            $token.'&path='.$url.'&techno='.$techno
        );
    }
}
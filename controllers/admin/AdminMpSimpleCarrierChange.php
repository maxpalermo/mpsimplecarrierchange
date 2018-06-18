<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Massimiliano Palermo <info@mpsoft.it>
*  @copyright 2017 mpSoft Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AdminMpSimpleCarrierChangeController extends ModuleAdminController
{
    public function __construct()
    {
            $this->bootstrap = true;
            $this->name = 'AdminMpSimpleCarrierChange';
            $this->errors = array();
            $this->default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            parent::__construct();

            $this->context = Context::getContext();
            $this->smarty = Context::getContext()->smarty;
            $this->id_lang = (int)ContextCore::getContext()->language->id;
            $this->payments = PaymentModuleCore::getInstalledPaymentModules();
            $this->carriers = CarrierCore::getCarriers($this->id_lang);
            $this->order_states = OrderStateCore::getOrderStates($this->id_lang);
    }
    
    public function initContent()
    {
        $awesome = "https://maxcdn.bootstrapcdn.com/".
            "font-awesome/4.7.0/css/font-awesome.min.css";
        $this->context->controller->addCSS($awesome);
        $this->smarty->assign(
            array(
                'carriers' => $this->carriers,
                'payments' => $this->payments,
                'order_states' => $this->order_states,
            )
        );
        $this->content = $this->smarty->fetch($this->module->getAdminTemplatePath().'admin_form.tpl');
        parent::initContent();
    }

    public function ajaxProcessGetOrderList()
    {
        $params = Tools::getValue('params');
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('o.*')
            ->select('c.name as carrier')
            ->select('cus.company')
            ->select('cus.firstname')
            ->select('cus.lastname')
            ->from('orders', 'o')
            ->innerJoin('carrier', 'c', 'c.id_carrier=o.id_carrier')
            ->innerJoin('customer', 'cus', 'cus.id_customer=o.id_customer');
        if ($params['carriers']) {
            foreach ($params['carriers'] as $carrier) {
                $sql->where('c.id_carrier='.(int)$carrier);
            }
        }
        if ($params['order_states']) {
            foreach ($params['order_states'] as $order_state) {
                $sql->where('o.current_state='.(int)$order_state);
            }   
        }
        $sql->orderBy('o.date_add DESC')
            ->orderBy('o.id_order DESC');

        $result = $db->executeS($sql);
        $pages = ceil(count($result) / 50);
        $this->smarty->assign(
            array(
                'result' => array_slice($result, 0, 50),
                'pages' => $pages,
                'total' => count($result),
                'current_page' => 1,
            )
        );
        $html = $this->smarty->fetch($this->module->getAdminTemplatePath().'admin_list.tpl');
        print Tools::jsonEncode(
            array(
                'html' => $html,
            )
        );

        exit();
    }

    private function getOrders()
    {
        $this->error = false;
        $carrier_find = Tools::getValue('input_select_carriers_find', array());
        $this->carrier_find_list = implode(',', $carrier_find);
        $id_carrier_change = (int)Tools::getValue('input_select_carriers_change', 0);
        $payment_module = Tools::getValue('input_select_payment_module', array());
        foreach ($payment_module as &$module) {
            $module = "'".$module."'";
        }
        $this->payment_module_list = implode(',', $payment_module);
        $order_states_list = Tools::getValue('input_select_order_state', array());
        $this->order_states_list = implode(',', $order_states_list);
        
        if ($id_carrier_change == 0) {
            $this->error = true;
            return $this->module->displayError($this->module->l('Error. Invalid carrier to change'));
        }
        
        $this->saveConfiguration();
        return $this->getTableList();
    }
    
    private function getTableList()
    {
        $date_start = Tools::getValue('input_date_start', '');
        $date_end = Tools::getValue('input_date_end', '');
        $this->loadConfiguration();
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('o.id_order')
                ->select('o.date_add')
                ->select('car.name as carrier_name')
                ->select('o.module as module_payment')
                ->select('o.total_paid_tax_incl as total')
                ->select('c.firstname')
                ->select('c.lastname')
                ->select('c.company')
                ->select('c.email')
                ->from('orders', 'o')
                ->innerJoin('carrier', 'car', 'car.id_carrier=o.id_carrier')
                ->innerJoin('customer', 'c', 'c.id_customer=o.id_customer');
        if ($this->carrier_find_list) {
            $sql->where('o.id_carrier in (' . pSQL($this->carriers_find_list) . ')');
        }
        if ($this->payment_module_list) {
            $sql->where('o.module in (' . str_replace("\'", "'", pSQL($this->payment_module_list)) . ')');
        }
        if ($this->order_states_list) {
            $sql->where('o.current_state in (' . pSQL($this->order_states_list) . ')');
        }
        if (!empty($date_start)) {
            $sql->where('o.date_add >= \'' . pSQL($date_start) . '\'');
        }
        if (!empty($date_end)) {
            $end_date = date('Y-m-d H:i:s', strtotime($date_end . ' +1 day'));
            $sql->where('o.date_add < \'' . pSQL($end_date) . '\'');
        }
        PrestaShopLoggerCore::addLog('[MPSCC] query: ' . $sql->__toString());
        $this->result = $db->executeS($sql);
        foreach ($this->result as &$result) {
            $date = Tools::dateFormat(array('date' => $result['date_add']), ContextCore::getContext()->smarty);
            $result['date_add'] = $date;
            $result['email'] = '<a href=\'mailto:'. $result['email'] . '\'>' . $result['email'] . '</a>';
        }
        
        return $this->renderHelperList();
    }
    
    private function loadConfiguration()
    {
        $this->carriers_find_list = ConfigurationCore::get('MP_SCC_SELECT_CARRIERS_FIND');
        $this->select_carriers_change = ConfigurationCore::get('MP_SCC_SELECT_CARRIERS_CHANGE');
        $this->payment_module_list = ConfigurationCore::get('MP_SCC_SELECT_PAYMENT_MODULE');
        $this->order_states_list = ConfigurationCore::get('MP_SCC_SELECT_ORDER_STATES');
    }
    
    private function saveConfiguration()
    {
        ConfigurationCore::updateValue(
            'MP_SCC_SELECT_CARRIERS_FIND',
            $this->carrier_find_list
        );
        ConfigurationCore::updateValue(
            'MP_SCC_SELECT_CARRIERS_CHANGE',
            Tools::getValue('input_select_carriers_change', 0)
        );
        ConfigurationCore::updateValue(
            'MP_SCC_SELECT_PAYMENT_MODULE',
            $this->payment_module_list
        );
        ConfigurationCore::updateValue(
            'MP_SCC_SELECT_ORDER_STATES',
            $this->order_states_list
        );
    }
    
    public function getIdOrderByReference($reference)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_order')
                ->from('orders')
                ->where('reference = \'' . pSQL($reference) . '\'');
        return (int)$db->getValue($sql);
    }
}

<?php
/**
* 2007-2017 PrestaShop
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
*  @author    Massimimiliano Palermo <maxx.palermo@gmail.com>
*  @copyright 2007-2018 Digital Solutions
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
 
class MpSimpleCarrierChange extends Module
{
    public function __construct()
    {
        $this->name = 'mpsimplecarrierchange';
        $this->tab = 'administration';
        $this->version = '1.3.0';
        $this->author = 'Digital Solutions®';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->module_key = '9767027f8d2cb53b86bf4da3f0fb02f9';

        parent::__construct();

        $this->displayName = $this->l('Simple Carrier Change');
        $this->description = $this->l('With this module, you are able to change carrier in a order.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->id_lang = ContextCore::getContext()->language->id;
        $this->adminClassName = 'AdminMpSimpleCarrierChange';
        $this->smarty = ContextCore::getContext()->smarty;

        if (Tools::isSubmit('ajax') && Tools::isSubmit('action')) {
            $action = 'ajaxProcess'.Tools::ucfirst(Tools::getValue('action'));
            if (method_exists($this, $action)) {
                $this->$action();
                exit();
            }
        }
    }
    
    public function getAdminTemplatePath()
    {
        return $this->local_path.'views/templates/admin/';
    }

    public function getHookTemplatePath()
    {
        return $this->local_path.'views/templates/hook/';
    }

    public function getPath()
    {
        return $this->local_path;
    }

    public function getUrl()
    {
        return $this->_path;
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        
        if (!parent::install()) {
            $this->_errors[] = $this->l('Error during main installation.');
            return false;
        }
        
        if (!$this->registerHook('displayBackOfficeHeader')) {
            $this->_errors[] = $this->l('Error during hook displayBackOfficeHeader installation.');
            return false;
        }
        
        if (!$this->registerHook('displayAdminOrder')) {
            $this->_errors[] = $this->l('Error during hook displayAdminOrder installation.');
            return false;
        }
        
        if (!$this->installTab('MpModules', $this->adminClassName, $this->l('MP Simple Carrier change'))) {
            $this->_errors[] = $this->l('Error during Tab installation.');
            return false;
        }
        
        return true;
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        
        if (!$this->uninstallTab($this->adminClassName)) {
            $this->_errors[] = $this->l('Error during tab uninstallation.');
            return false;
        }
        return true;
    }
    
    /**
     * Install Main Menu
     * @return int Main menu id
     */
    public function installMainMenu()
    {
        $id_mp_menu = (int) TabCore::getIdFromClassName('MpModules');
        if ($id_mp_menu == 0) {
            $tab = new TabCore();
            $tab->active = 1;
            $tab->class_name = 'MpModules';
            $tab->id_parent = 0;
            $tab->module = null;
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->l('MP Modules');
            }
            $id_mp_menu = $tab->add();
            if ($id_mp_menu) {
                PrestaShopLoggerCore::addLog('id main menu: '.(int)$id_mp_menu);
                return (int)$tab->id;
            } else {
                PrestaShopLoggerCore::addLog('id main menu error');
                return false;
            }
        }
    }

    /**
     *
     * @param string $parent Parent tab name
     * @param type $class_name Class name of the module
     * @param type $name Display name of the module
     * @param type $active If true, Tab menu will be shown
     * @return boolean True if successfull, False otherwise
     */
    public function installTab($parent, $class_name, $name, $active = 1)
    {
        // Create new admin tab
        $tab = new Tab();
        $id_parent = (int)Tab::getIdFromClassName($parent);
        PrestaShopLoggerCore::addLog('Install main menu: id=' . (int)$id_parent);
        if (!$id_parent) {
            $id_parent = $this->installMainMenu();
            if (!$id_parent) {
                $this->_errors[] = $this->l('Unable to install main module menu tab.');
                return false;
            }
            PrestaShopLoggerCore::addLog('Created main menu: id=' . (int)$id_parent);
        }
        $tab->id_parent = (int)$id_parent;
        $tab->name      = array();
        
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        
        $tab->class_name = $class_name;
        $tab->module     = $this->name;
        $tab->active     = $active;
        
        if (!$tab->add()) {
            $this->_errors[] = $this->l('Error during Tab install.');
            return false;
        }
        return true;
    }

    /**
     * Uninstall a menu
     * @param string pe $class_name Class name of the module
     * @return boolean True if successfull, False otherwise
     */
    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab((int)$id_tab);
            return $tab->delete();
        }
    }
    
    public function hookDisplayAdminOrder($params)
    {
        if (empty($params)) {
            $params = array();
        }
        $id_order = (int)$params['id_order'];
        $this->smarty->assign(
            array(
                'carriers' => CarrierCore::getCarriers($this->id_lang),
                'id_order' => $id_order,
                'token' => Tools::getAdminTokenLite('AdminOrders'),
            )
        );
        return $this->smarty->fetch($this->getHookTemplatePath().'carriers.tpl');
    }
    
    public function updateOrder($id_order, $id_carrier)
    {
        $order = new OrderCore($id_order);
        $order->id_carrier = $id_carrier;
        return  $order->update();
    }
    
    public function updateOrderCarrier($id_order, $id_carrier)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_order_carrier')
                ->from('order_carrier')
                ->where('id_order = ' . (int)$id_order);
        $id_order_carrier = (int)$db->getValue($sql);
        if ($id_order_carrier) {
            $order_carrier = new OrderCarrierCore($id_order_carrier);
            $order_carrier->id_carrier = $id_carrier;
            return $order_carrier->update();
        } else {
            return false;
        }
    }
    
    public function getContent()
    {
        $link = new LinkCore();
        $url = $link->getAdminLink('AdminMpSimpleCarrierChange');
        Tools::redirectAdmin($url);
    }

    public function ajaxProcessChangeCarrier()
    {
        PrestaShopLoggerCore::addLog('ajax call: chenge carrier');
        $id_order = (int)Tools::getValue('id_order', 0);
        $id_carrier = (int)Tools::getValue('id_carrier', 0);
        
        if ($id_order && $id_carrier) {
            $result_update_order = $this->updateOrder($id_order, $id_carrier);
            $result_update_order_carrier = $this->updateOrderCarrier($id_order, $id_carrier);
        }
        print "----SPLIT----";
        print Tools::jsonEncode(
            array(
                'result' => $result_update_order && $result_update_order_carrier,
            )
        );
        print "----SPLIT----";
        exit();
    }
}

<?php
/**
 * Easypay
 *
 * @copyright Direitos autorais (c) 2023 Trigenius
 * 
 * @author Trigenius
 * 
 * Todos os direitos reservados.
 * 
 * É concedida permissão para utilizar este software de forma gratuita. No entanto, não é permitido
 * modificar, derivar obras de, distribuir, sublicenciar e/ou vender cópias do software.
 * 
 * O SOFTWARE É FORNECIDO "COMO ESTÁ", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU IMPLÍCITA,
 * INCLUINDO MAS NÃO SE LIMITANDO A GARANTIAS DE COMERCIALIZAÇÃO, ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO
 * E NÃO VIOLAÇÃO. EM NENHUM CASO OS AUTORES OU TITULARES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS
 * POR QUALQUER RECLAMAÇÃO, DANOS OU OUTRAS RESPONSABILIDADES, SEJA EM UMA AÇÃO DE CONTRATO, DELITO
 * OU QUALQUER OUTRO MOTIVO, QUE SURJA DE, FORA DE OU EM RELAÇÃO COM O SOFTWARE OU O USO OU OUTRAS
 * NEGOCIAÇÕES NO SOFTWARE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class easypay extends PaymentModule
{


    public function __construct()
    {
        $this->name = 'easypay';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.8';
        $this->author = 'trigenius';
        $this->controllers = array('payment', 'validation');
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        $this->displayName = $this->l('EasyPay');
        $this->description = $this->l('Módulo para ligar ao Gateway de Pagamentos EasyPay');
        $this->confirmUninstall = $this->l('¿Estás seguro de que quieres desinstalar el módulo?');
        $this->ps_versions_compliancy = array('min' => '1.7.8.6', 'max' => _PS_VERSION_);
        parent::__construct();
        $this->context = Context::getContext();
    }


    public function install()
    {

        Configuration::updateValue('EASYPAY_API_ID', '');
        Configuration::updateValue('EASYPAY_API_KEY', '');
        Configuration::updateValue('EASYPAY_VISA', '1');
        Configuration::updateValue('EASYPAY_MULTIBANCO', '1');
        Configuration::updateValue('EASYPAY_MBW', '1');
        Configuration::updateValue('EASYPAY_DD', '1');
        Configuration::updateValue('EASYPAY_BOLETO', '1');
        Configuration::updateValue('EASYPAY_TESTES', '1');
        Configuration::updateValue('EASYPAY_MAX_DD', '500');
        Configuration::updateValue('EASYPAY_MIN_DD', '1');
        Configuration::updateValue('EASYPAY_MAX_VISA', '500');
        Configuration::updateValue('EASYPAY_MIN_VISA', '1');
        Configuration::updateValue('EASYPAY_MAX_MB', '500');
        Configuration::updateValue('EASYPAY_MIN_MB', '1');
        Configuration::updateValue('EASYPAY_MAX_MBWAY', '500');
        Configuration::updateValue('EASYPAY_MIN_MBWAY', '1');
        Configuration::updateValue('EASYPAY_AUTORIZAR_PAGOS', '1');
        Configuration::updateValue('EASYPAY_DIAS_ESQUECER', '365');
        Configuration::updateValue('EASYPAY_DIAS_ESQUECER_MB', '0');
        Configuration::updateValue('EASYPAY_API_IP', '');



        return (parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->_installDb()
            && $this->addOrderStates()
            && $this->addCategory()
            && $this->addFeatures()
            && $this->create_backofficeTab()
            && $this->traduzir_features()
        );
    }

    public function create_backofficeTab()
    {

        $tab = new Tab();
        $tab->class_name = 'AdminEasyPay';
        $tab->id_parent = Tab::getIdFromClassName('AdminTools');
        $tab->module = $this->name;
        $languages = Language::getLanguages();
        foreach ($languages as $language)
            if ($language['iso_code'] == "en") {
                $tab->name[$language['id_lang']] = 'Active Subscriptions - Easypay';
            } else {
                $tab->name[$language['id_lang']] = 'Subscrições Ativas - Easypay';
            }

        $tab->add();


        $tab2 = new Tab();
        $tab2->class_name = 'AdminAuthep';
        $tab2->id_parent = Tab::getIdFromClassName('AdminTools');
        $tab2->module = $this->name;
        $languages = Language::getLanguages();

        foreach ($languages as $language)
            if ($language['iso_code'] == "en") {
                $tab2->name[$language['id_lang']] = 'Authorize Payments - Easypay';
            } else {
                $tab2->name[$language['id_lang']] = 'Autorizar Pagamentos - Easypay';
            }
        $tab2->add();


        return true;
    }


    public function hookDisplayProductAdditionalInfo($params)
    {

        $id_cart = $this->context->cart->id;
        $cart = new Cart($id_cart);
        $products_in_cart = $cart->getProducts();

        $have_others = 0;
        $have_subs = 0;
        $actual_product = 0; //0 no Subsc - 1 Subsc
        $have_products_in_cart = 0;

        if (count($products_in_cart) > 0) {
            $have_products_in_cart = 1;
        }

        foreach (Product::getProductCategoriesFull($params['product']['id_product']) as $categoria) {

            if ($categoria['id_category'] == Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
                $actual_product = 1;
            }
        }


        foreach ($products_in_cart as $product) {

            $activador = 0;
            foreach (Product::getProductCategoriesFull($product['id_product']) as $categoria) {

                if ($categoria['id_category'] == Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
                    $have_subs = 1;
                    $activador = 1;
                }
            }
            if ($activador == 0) {
                $have_other = 1;
            }
        }

        $this->context->smarty->assign([
            'have_subs' => $have_subs,
            'have_others' => $have_others,
            'producto' => $params['product'],
            'actual' => $actual_product,
            'have_products_in_cart' => $have_products_in_cart,
            'qty_products' => count($products_in_cart),
            'is_ssl' => array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ? 1 : 0
        ]);

        return $this->fetch('module:easypay/views/templates/hook/product-info.tpl');
    }

    public function hookDisplayProductPriceBlock($params)
    {



        if (isset($params['type'])) {
            $tipo = $params['type'];
        } else {
            $tipo = '';
        }

        if (isset($params['type'])) {
            $product = $params['product'];
        } else {
            $product = '';
        }

        if (isset($params['type']) && isset($params['hook_origin'])) {
            $hook_origin = $params['hook_origin'];
        } else {
            $hook_origin = '';
        }

        $this->context->smarty->assign([
            'type' => $tipo,
            'product' => $product,
            'hook_origin' => $hook_origin,
            'is_ssl' => array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ? 1 : 0
        ]);

        if (($tipo == 'unit_price' || $hook_origin == 'product_sheet') && isset($product['id_category_default']) && $product['id_category_default'] == Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
            return $this->fetch('module:easypay/views/templates/hook/priceFreq.tpl');
        }
    }

    public function traduzir_features()
    {
        $id_en = 0;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "lang WHERE iso_code = 'en'";
        $lingua = Db::getInstance()->executeS($sql);

        if (count($lingua) > 0) {
            $id_en = $lingua[0]['id_lang'];

            $upd = 'update ' . _DB_PREFIX_ . 'feature_lang SET name="Frequency" WHERE id_feature =  ' . (int)Configuration::get('EASYPAY_FREQUENCY') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 day" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL1') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 week" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL2') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="2 weeks" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL3') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 month" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL4') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL5') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="3 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL6') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="4 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL7') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="6 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL8') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 year" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_FREQUENCY_VAL9') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_lang SET name="Subscription time" WHERE id_feature =  ' . (int)Configuration::get('EASYPAY_EXP_TIME') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 month" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_EXP_TIME10') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="2 months" WHERE id_feature_value =  ' . (int)Configuration::get('EEASYPAY_EXP_TIME11') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="3 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_EXP_TIME12') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="4 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_EXP_TIME13') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="6 months" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_EXP_TIME14') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 year" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_EXP_TIME15') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="Until Cancel Subscription" WHERE id_feature_value =  ' . (int)Configuration::get('EASYPAY_EXP_TIME16') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'category_lang SET name="Subscriptions" WHERE id_category =  ' . (int)Configuration::get('EASYPAY_CATEGORY_SUSCP') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment from ATM" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_MB_WAIT') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Authorizing payment" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_PAGO_NAO_AUT') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment Visa / Mastercard" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_CC_WAIT') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Payment accepted" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_APROVED') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Payment accept" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_APROVED_SUBS') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Payment Error" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_FAILED') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment MBWAY" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_BMWAY_WAIT') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for Direct Debit payment" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_DD_WAIT') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment with Boleto Bancario" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_BB_WAIT') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="ACTIVE SUBSCRIPTION" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_SUBSCRICAO_PAID') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="INACTIVE SUBSCRIPTION - PAYMENT ERROR" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_SUBSCRICAO_ERRO') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="CANCELED SUBSCRIPTION" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_SUBSCRICAO_CANCEL') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="CANCELED PAYMENT" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_PAYMENT_CANCEL') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);


            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="PROCESSING PAYMENT" WHERE id_order_state =  ' . (int)Configuration::get('EASYPAY_PROCESSING') . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        return true;
    }

    public function addFeatures()
    {

        $id_en = 0;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "lang WHERE iso_code = 'en'";
        $lingua = Db::getInstance()->executeS($sql);

        if (count($lingua) > 0) {
            $id_en = $lingua[0]['id_lang'];
        }







        $fid = Configuration::get('EASYPAY_FREQUENCY');
        $fid2 = Configuration::get('EASYPAY_EXP_TIME');


        if (!(Configuration::get('EASYPAY_FREQUENCY') > 0)) {
            $feature = new Feature;
            $feature->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Frequência');
            $feature->position = 0;
            $feature->add();
            Configuration::updateValue('EASYPAY_FREQUENCY', $feature->id);
            $fid = $feature->id;

            $upd = 'update ' . _DB_PREFIX_ . 'feature_lang SET name="Frequency" WHERE id_feature =  ' . $feature->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }




        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL1') > 0)) {
            $val1 = new FeatureValue;
            $val1->id_feature = $fid;
            $val1->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '1 dia');
            $val1->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL1', $val1->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 day" WHERE id_feature_value =  ' . $val1->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL2') > 0)) {
            $val2 = new FeatureValue;
            $val2->id_feature = $fid;
            $val2->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '1 semana');
            $val2->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL2', $val2->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 week" WHERE id_feature_value =  ' . $val2->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL3') > 0)) {
            $val3 = new FeatureValue;
            $val3->id_feature = $fid;
            $val3->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '2 semanas');
            $val3->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL3', $val3->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="2 weeks" WHERE id_feature_value =  ' . $val3->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL4') > 0)) {
            $val4 = new FeatureValue;
            $val4->id_feature = $fid;
            $val4->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '1 mês');
            $val4->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL4', $val4->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 month" WHERE id_feature_value =  ' . $val4->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }


        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL5') > 0)) {
            $val5 = new FeatureValue;
            $val5->id_feature = $fid;
            $val5->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '2 meses');
            $val5->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL5', $val5->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 months" WHERE id_feature_value =  ' . $val5->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL6') > 0)) {
            $val6 = new FeatureValue;
            $val6->id_feature = $fid;
            $val6->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '3 meses');
            $val6->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL6', $val6->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="3 months" WHERE id_feature_value =  ' . $val6->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL7') > 0)) {
            $val7 = new FeatureValue;
            $val7->id_feature = $fid;
            $val7->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '4 meses');
            $val7->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL7', $val7->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="4 months" WHERE id_feature_value =  ' . $val7->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL8') > 0)) {
            $val8 = new FeatureValue;
            $val8->id_feature = $fid;
            $val8->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '6 meses');
            $val8->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL8', $val8->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="6 months" WHERE id_feature_value =  ' . $val8->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FREQUENCY_VAL9') > 0)) {
            $val9 = new FeatureValue;
            $val9->id_feature = $fid;
            $val9->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '1 ano');
            $val9->add();
            Configuration::updateValue('EASYPAY_FREQUENCY_VAL9', $val9->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 year" WHERE id_feature_value =  ' . $val9->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME') > 0)) {
            $feature2 = new Feature;
            $feature2->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Tempo de Subscrição');
            $feature2->position = 0;
            $feature2->add();
            Configuration::updateValue('EASYPAY_EXP_TIME', $feature2->id);
            $fid2 = $feature2->id;

            $upd = 'update ' . _DB_PREFIX_ . 'feature_lang SET name="Subscription time" WHERE id_feature =  ' . $feature2->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME10') > 0)) {
            $val1 = new FeatureValue;
            $val1->id_feature = $fid2;
            $val1->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '1 mês');
            $val1->add();
            Configuration::updateValue('EASYPAY_EXP_TIME10', $val1->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 month" WHERE id_feature_value =  ' . $val1->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }


        if (!(Configuration::get('EASYPAY_EXP_TIME11') > 0)) {
            $val2 = new FeatureValue;
            $val2->id_feature = $fid2;
            $val2->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '2 meses');
            $val2->add();
            Configuration::updateValue('EEASYPAY_EXP_TIME11', $val2->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="2 months" WHERE id_feature_value =  ' . $val2->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME12') > 0)) {
            $val3 = new FeatureValue;
            $val3->id_feature = $fid2;
            $val3->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '3 meses');
            $val3->add();
            Configuration::updateValue('EASYPAY_EXP_TIME12', $val3->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="3 months" WHERE id_feature_value =  ' . $val3->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME13') > 0)) {
            $val4 = new FeatureValue;
            $val4->id_feature = $fid2;
            $val4->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '4 meses');
            $val4->add();
            Configuration::updateValue('EASYPAY_EXP_TIME13', $val4->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="4 months" WHERE id_feature_value =  ' . $val4->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME14') > 0)) {
            $val5 = new FeatureValue;
            $val5->id_feature = $fid2;
            $val5->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '6 meses');
            $val5->add();
            Configuration::updateValue('EASYPAY_EXP_TIME14', $val5->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="6 months" WHERE id_feature_value =  ' . $val5->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME15') > 0)) {
            $val6 = new FeatureValue;
            $val6->id_feature = $fid2;
            $val6->value = array((int)Configuration::get('PS_LANG_DEFAULT') => '1 ano');
            $val6->add();
            Configuration::updateValue('EASYPAY_EXP_TIME15', $val6->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="1 year" WHERE id_feature_value =  ' . $val6->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_EXP_TIME16') > 0)) {
            $val7 = new FeatureValue;
            $val7->id_feature = $fid2;
            $val7->value = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Até Cancelar Subscrição');
            $val7->add();
            Configuration::updateValue('EASYPAY_EXP_TIME16', $val7->id);

            $upd = 'update ' . _DB_PREFIX_ . 'feature_value_lang SET value="Until Cancel Subscription" WHERE id_feature_value =  ' . $val7->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }



        return true;
    }

    public function addCategory()
    {

        $id_en = 0;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "lang WHERE iso_code = 'en'";
        $lingua = Db::getInstance()->executeS($sql);

        if (count($lingua) > 0) {
            $id_en = $lingua[0]['id_lang'];
        }

        if (!(Configuration::get('EASYPAY_CATEGORY_SUSCP') > 0)) {
            $object = new Category();
            $object->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Subscrições');
            $object->id_parent = Configuration::get('PS_HOME_CATEGORY');
            $object->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') =>  'suscricoes-products');
            $object->add();
            Configuration::updateValue('EASYPAY_CATEGORY_SUSCP', $object->id);

            $upd = 'update ' . _DB_PREFIX_ . 'category_lang SET name="Subscriptions" WHERE id_category =  ' . $object->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        return true;
    }


    public function _installDb()
    {

        $sql = "CREATE TABLE  " . _DB_PREFIX_ . "ep_requests (id_request int NOT NULL AUTO_INCREMENT,status varchar(50), id_ep_request varchar(250), method_type varchar(255), method_status varchar(255), method_entity varchar(255), method_reference varchar(255), customer_easypay varchar(255), id_cart int, first_date datetime, updated datetime, modo_de_pago varchar(30), nombre_de_pago varchar(30), id_user int, active int DEFAULT 1,entidade varchar(255), referencia varchar(255), PRIMARY KEY (id_request)) ENGINE=" . _MYSQL_ENGINE_ . ";CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "ep_payments (id_payment int NOT NULL AUTO_INCREMENT PRIMARY KEY,id_payment_easypay varchar(250), value decimal(20,10), currency varchar(250), id_cart int, expiration_time datetime, method varchar(255), customer_easypay_id varchar(255), customer_easypay_name varchar(255), customer_easypay_email int, customer_easypay_phone varchar(255), customer_easypay_indicative varchar(5), customer_easypay_fiscal_number varchar(255), customer_easypay_key varchar(255), account_id varchar(255), date datetime) ENGINE=" . _MYSQL_ENGINE_ . ";CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "ep_orders (id_order int NOT NULL AUTO_INCREMENT,method varchar(50), id_cart INT, link varchar(255), title varchar(255), messagem varchar(255), entidade varchar(255), montante decimal(20,10), referencia varchar(255), PRIMARY KEY (id_order)) ENGINE=" . _MYSQL_ENGINE_ . ";CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "subscrip ( id_susc int NOT NULL AUTO_INCREMENT, id_cart int, id_order int, dt_init datetime, dt_fin datetime, freq varchar(5), n_cob_ef int, n_cob_eftd int, val_subs decimal(10,5), val_cobrado decimal(10,5), dt_ult_cob datetime, estado_act varchar(20), respuesta TEXT, id_ep varchar(255), PRIMARY KEY (id_susc)) ENGINE=" . _MYSQL_ENGINE_ . ";CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "ep_tokenization (id_token int NOT NULL AUTO_INCREMENT, id_user int NOT NULL, nome_pagamneto varchar(40), tipo_pagamento varchar(30), id_pagamento varchar(255), expiration datetime NOT NULL default NOW(),active int NOT NULL DEFAULT 0, PRIMARY KEY (id_token)) ENGINE=" . _MYSQL_ENGINE_ . ";CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "ep_frequent_transactions (id_trans int NOT NULL AUTO_INCREMENT, id_user int NOT NULL, id_pagamento varchar(100), autorization int, metodo_pagamento varchar(100), tipo_pagamento varchar(30), autorizado int NOT NULL DEFAULT 0, valor decimal(10,5), info varchar(255),info2 varchar(250), ativado int DEFAULT 0, id_cart int, created datetime, capturar int DEFAULT 0, PRIMARY KEY (id_trans)) ENGINE=" . _MYSQL_ENGINE_ . "; 
    
        CREATE TABLE  " . _DB_PREFIX_ . "ep_last_mb (id int NOT NULL AUTO_INCREMENT, cart int, id_pagamento varchar(100), PRIMARY KEY (id)) ENGINE=" . _MYSQL_ENGINE_ . ";
        ";

        Db::getInstance()->execute($sql);


        return true;
    }

    public function addOrderStates()
    {
        $id_en = 0;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "lang WHERE iso_code = 'en'";
        $lingua = Db::getInstance()->executeS($sql);

        if (count($lingua) > 0) {
            $id_en = $lingua[0]['id_lang'];
        }

        if (!(Configuration::get('EASYPAY_MB_WAIT') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento MB';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_MB_WAIT', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment from ATM" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }



        if (!(Configuration::get('EASYPAY_PAGO_NAO_AUT') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A autorizar pagamento';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#67a150';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = true;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_PAGO_NAO_AUT', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Authorizing payment" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_CC_WAIT') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento Cartões Visa / Mastercard';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_CC_WAIT', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment Visa / Mastercard" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_APROVED') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'Pagamento Aprovado';
            $OrderState->invoice = true;
            $OrderState->send_email = 1;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = 1;
            $OrderState->deleted = false;
            $OrderState->pdf_invoice = true;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_APROVED', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Payment accepted" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_APROVED_SUBS') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'Subscrito';
            $OrderState->invoice = true;
            $OrderState->send_email = 1;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = 1;
            $OrderState->deleted = false;
            $OrderState->pdf_invoice = true;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_APROVED_SUBS', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Payment accept" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_FAILED') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'Erro de pagamento';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_FAILED', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Payment Error" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_BMWAY_WAIT') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento MBWAY';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_BMWAY_WAIT', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment MBWAY" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_DD_WAIT') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento Debito Direto';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_DD_WAIT', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for Direct Debit payment" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_BB_WAIT') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento com Boleto Bancario';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_BB_WAIT', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="Waiting for payment with Boleto Bancario" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_SUBSCRICAO_PAID') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'SUBSCRIÇÃO ATIVA';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_SUBSCRICAO_PAID', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="ACTIVE SUBSCRIPTION" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_SUBSCRICAO_ERRO') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'SUBSCRIÇÂO INATIVA - ERRO DE PAGAMENTO';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_SUBSCRICAO_ERRO', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="INACTIVE SUBSCRIPTION - PAYMENT ERROR" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_SUBSCRICAO_CANCEL') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'SUBSCRIÇÂO CANCELADA';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_SUBSCRICAO_CANCEL', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="CANCELED SUBSCRIPTION" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        if (!(Configuration::get('EASYPAY_PAYMENT_CANCEL') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'PAGAMENTO CANCELADO';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_PAYMENT_CANCEL', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="CANCELED PAYMENT" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }


        if (!(Configuration::get('EASYPAY_PROCESSING') > 0)) {

            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'EM PROCESSAMENTO';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_PROCESSING', $OrderState->id);

            $upd = 'update ' . _DB_PREFIX_ . 'order_state_lang SET name="PROCESSING PAYMENT" WHERE id_order_state =  ' . $OrderState->id . ' AND id_lang= ' . $id_en . '';
            Db::getInstance()->execute($upd);
        }

        return true;
    }

    public function fowardNAceite()
    {
        $palv = $this->l('O pagamento não foi aceite, verique o seus dados e pode tentar novamente desde "A minha conta->Encomendas->Details"');
        return $palv;
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->registerStylesheet('modules-easypay', 'modules/' . $this->name . '/views/css/style.css', ['media' => 'all', 'priority' => 150]);

        $this->context->controller->registerJavascript('modules-script', 'modules/' . $this->name . '/views/js/script.js', ['position' => 'bottom', 'priority' => 0]);
    }

    public function hookDisplayOrderDetail($params)
    {

        $sql = "SELECT id_cart FROM " . _DB_PREFIX_ . "orders WHERE id_order = " . Tools::getValue('id_order') . " LIMIT 1";
        $id_cart = Db::getInstance()->executeS($sql);

        $sql2 = "SELECT * FROM " . _DB_PREFIX_ . "ep_orders WHERE id_cart=" . $id_cart[0]['id_cart'];
        $payment_info = Db::getInstance()->executeS($sql2);


        $sql3 = "SELECT * FROM " . _DB_PREFIX_ . "subscrip where id_order=" . Tools::getValue('id_order') . " ORDER BY id_susc DESC LIMIT 1";
        $pagamentos = Db::getInstance()->executeS($sql3);

        if (isset($pagamentos[0])) {
            $pagamentos_respuesta = json_decode($pagamentos[0]['respuesta']);
            $pagamentos_status = $pagamentos[0]['estado_act'];
        } else {
            $pagamentos_respuesta = array();
            $pagamentos_status = '';
        }

        $this->context->smarty->assign([
            'pagamentos' => $pagamentos_respuesta,
            'linki' => _PS_BASE_URL_ . __PS_BASE_URI__,
            'status' => $pagamentos_status,
            'metodo' => $payment_info[0]['method'],
            'entidade' => '',
            'referencia' => '',
            'montante' => '',
            'url_l' => urldecode($payment_info[0]['link']),
            'is_ssl' => array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ? 1 : 0
        ]);


        return $this->fetch('module:easypay/views/templates/hook/orderDetails.tpl');
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        if (!parent::uninstall() || !$this->unregisterHook('displayNav2'))
            return false;

        return true;
    }



    public function hookPaymentOptions($params)
    {
        /*
     * Verify if this module is active
     */
        if (!$this->active) {
            return;
        }

        /**
         * Form action URL. The form data will be sent to the
         * validation controller when the user finishes
         * the order process.
         */
        $formVisa = $this->context->link->getModuleLink($this->name, 'visa', array(), true);

        $formAction = $this->context->link->getModuleLink($this->name, 'validation', array(), true);

        $customer = $this->context->customer;



        //Validar si todos los articulos son de suscripcion    
        $productos_actuales = Context::getContext()->cart->getProducts();
        $cat_valido = 1;
        $productos_in = 0;

        $nosub = 0;
        $sisub = 0;
        foreach ($productos_actuales as $product_act) {
            if ((int)$product_act['id_category_default'] == (int)Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
                $sisub = $sisub + 1;
                $cat_valido = 0;
            } else {
                $nosub = $nosub + 1;
            }
            $productos_in = $productos_in + 1;
        }



        /**
         * Create a PaymentOption object containing the necessary data
         * to display this module in the checkout
         */
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption->setModuleName($this->displayName)
            ->setCallToActionText($this->l('Cartões Visa / Mastercard '))
            ->setAction($this->context->link->getModuleLink($this->name, 'visa', array(), true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/visa30.png'))
            ->setForm($this->generateFormCC());



        /**
         * Create Multibanco option
         */

        $newOption2 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption2->setModuleName($this->displayName)
            ->setCallToActionText($this->l('Multibanco - EasyPay'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/multibanco30.png'))
            ->setForm($this->generateFormMB());



        $newOption3 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption3->setModuleName($this->displayName)
            ->setCallToActionText($this->l('MBWay - EasyPay'))
            ->setAction($this->context->link->getModuleLink($this->name, 'mbway', array(), true))
            ->setInputs([
                'label' => [
                    'name' => 'phonenumber',
                    'type' => 'text',
                    'label' => '',
                    'value' => '',
                    'required' => true,

                ],
            ])
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/mbway30.png'))
            //->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_mbway.tpl'))
            ->setForm($this->generateFormMBWAY());



        $newOption4 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption4->setModuleName($this->displayName)
            ->setCallToActionText($this->l('Debito Direto - EasyPay'))
            ->setAction($this->context->link->getModuleLink($this->name, 'dd', array(), true))
            ->setInputs([
                'account_holder' => [
                    'name' => 'account_holder',
                    'type' => 'text',
                    'label' => 'Nome do titular da conta',
                    'value' => $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
                    'required' => 1,

                ],
                'label' => [
                    'name' => 'iban',
                    'type' => 'text',
                    'label' => 'Nome',
                    'value' => 'IBAN',
                    'required' => 1,

                ],
                'telemovel' => [
                    'name' => 'telephone',
                    'type' => 'text',
                    'label' => 'Telemovel',
                    'value' => 'Telemovel',
                    'required' => 1,

                ],
            ])
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/debitodirecto30.png'))
            ->setForm($this->generateFormDD());




        $productos_actuales = Context::getContext()->cart->getProducts();
        $cat_valido = 1;
        $num_produtos = 0;


        foreach ($productos_actuales as $product_act) {
            $num_produtos = $num_produtos + 1;
            if ((int)$product_act['id_category_default'] != (int)Configuration::get('EASYPAY_CATEGORY_SUSCP') or (int)$product_act['cart_quantity'] > 1) {

                $cat_valido = 0;
            }
        }



        $newOption6 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption6->setModuleName($this->displayName)
            ->setCallToActionText($this->l('Debito Direto (Suscrição) - EasyPay'))
            ->setAction($this->context->link->getModuleLink($this->name, 'dds', array(), true))
            ->setInputs([
                'account_holder' => [
                    'name' => 'account_holder',
                    'type' => 'text',
                    'label' => 'Nome do titular da conta',
                    'value' => $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
                    'required' => 1,

                ],
                'label' => [
                    'name' => 'iban',
                    'type' => 'text',
                    'label' => 'Nome',
                    'value' => 'IBAN',
                    'required' => 1,

                ],
                'telemovel' => [
                    'name' => 'telephone',
                    'type' => 'text',
                    'label' => 'Telemovel',
                    'value' => 'Telemovel',
                    'required' => 1,

                ],
            ])
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/debitodirecto30.png'))
            ->setForm($this->generateFormDDS());




        $newOption5 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption5->setModuleName($this->displayName)
            ->setCallToActionText('Boleto - EasyPay')
            ->setAction($this->context->link->getModuleLink($this->name, 'boleto', array(), true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/boleto30.png'));
        //->setForm($paymentForm);

        /**
         *  Load form template to be displayed in the checkout step
         */





        $total_cart = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        //AGREGAR FECHA DE EXPIRACIÓN
        $buscar_registro = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_user = " . $this->context->customer->id . ' AND method_status="acepted" AND modo_de_pago="frequent" AND active=1';
        $retornar = Db::getInstance()->executeS($buscar_registro);

        $metodos = array();
        $contador = 0;



        foreach ($retornar as $metodo) {

            $actionn = '';
            $logo = '';

            if ($metodo['method_type'] == 'cc' && (float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_VISA') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_VISA')) {
                $actionn = 'frequentvisa';
                $logo = 'visa30';

                $metodos[$contador] = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
                $metodos[$contador]->setModuleName($this->displayName)
                    ->setCallToActionText($metodo['nombre_de_pago'])
                    ->setAction($this->context->link->getModuleLink($this->name, $actionn, array(), true))
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $logo . '.png'))
                    ->setForm($this->generateFormFrequent($metodo['id_ep_request']));
                $metodos[$contador]->metodo = 'visa';
            } else if ($metodo['method_type'] == 'mb' && (float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_MB') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_MB')) {
                $actionn = 'frequentmb';
                $logo = 'multibanco30';

                $metodos[$contador] = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
                $metodos[$contador]->setModuleName($this->displayName)
                    ->setCallToActionText($metodo['nombre_de_pago'])
                    ->setAction($this->context->link->getModuleLink($this->name, $actionn, array(), true))
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $logo . '.png'))
                    ->setForm($this->generateFormFrequentMB($metodo['id_ep_request']));
                $metodos[$contador]->metodo = 'mb';
            } else if ($metodo['method_type'] == 'dd' && $total_cart >= Configuration::get('EASYPAY_MIN_DD') && $total_cart <= Configuration::get('EASYPAY_MAX_DD')) {
                $actionn = 'frequentdd';
                $logo = 'debitodirecto30';

                $metodos[$contador] = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
                $metodos[$contador]->setModuleName($this->displayName)
                    ->setCallToActionText($metodo['nombre_de_pago'])
                    ->setAction($this->context->link->getModuleLink($this->name, $actionn, array(), true))
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $logo . '.png'))
                    ->setForm($this->generateFormFrequentDD($metodo['id_ep_request']));
                $metodos[$contador]->metodo = 'dd';
            } else if ($metodo['method_type'] == 'mbw' && (float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_MBWAY') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_DD')) {

                $actionn = 'frequentmbway';
                $logo = 'mbway30';

                $metodos[$contador] = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
                $metodos[$contador]->setModuleName($this->displayName)
                    ->setCallToActionText($metodo['nombre_de_pago'])
                    ->setAction($this->context->link->getModuleLink($this->name, $actionn, array(), true))
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $logo . '.png'))
                    ->setForm($this->generateFormFrequentMBWAY($metodo['id_ep_request']));
                $metodos[$contador]->metodo = 'mbw';
            }



            $contador = $contador + 1;
        }


        $paymentForm = $this->fetch('module:easypay/views/templates/hook/payment_options.tpl');




        if ($nosub > 0 && $sisub > 0) {
            $newOption->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
            $newOption2->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
            $newOption3->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
            $newOption4->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
            $newOption5->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
        }


        $opciones = array();


        if (Configuration::get('EASYPAY_VISA') == 1 && $cat_valido < 1) {
            array_push($opciones, $newOption);
            foreach ($metodos as $metodo) {
                if ($metodo->metodo == 'visa') {
                    array_push($opciones, $metodo);
                }
            }
        }
        // if(Configuration::get('EASYPAY_VISA')==1){
        //     foreach($metodos as $metodo){
        //         if($metodo->metodo == 'visa'){
        //             array_push($opciones, $metodo);
        //         }
        //     }
        // }

        if (Configuration::get('EASYPAY_MULTIBANCO') == 1 && $cat_valido < 1) {
            array_push($opciones, $newOption2);
            foreach ($metodos as $metodo) {
                if ($metodo->metodo == 'mb') {
                    array_push($opciones, $metodo);
                }
            }
        }


        if (Configuration::get('EASYPAY_MBW') == 1 && $cat_valido < 1) {
            array_push($opciones, $newOption3);
            foreach ($metodos as $metodo) {
                if ($metodo->metodo == 'mbw') {
                    array_push($opciones, $metodo);
                }
            }
        }



        if (Configuration::get('EASYPAY_DD') == 1 && $cat_valido < 1) {
            array_push($opciones, $newOption4);

            foreach ($metodos as $metodo) {
                if ($metodo->metodo == 'dd') {
                    array_push($opciones, $metodo);
                }
            }
        }


        if (Configuration::get('EASYPAY_CATEGORY_SUSCP') > 0 && $cat_valido > 0 && $num_produtos < 2) {
            array_push($opciones, $newOption6);
        }


        if (Configuration::get('EASYPAY_BB') == 1 && $cat_valido < 1) {
            array_push($opciones, $newOption5);
        }

        $payment_options = $opciones;

        return $payment_options;
    }

    protected function generateFormMBWAY()
    {
        $total_cart = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        if ($total_cart >= Configuration::get('EASYPAY_MIN_MBWAY') && $total_cart <= Configuration::get('EASYPAY_MAX_MBWAY')) {
            $frequente = 1;
        } else {
            $frequente = 0;
        }


        $this->context->smarty->assign([
            'frequente' => $frequente,
            'action' => $this->context->link->getModuleLink($this->name, 'mbway', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_mbway.tpl');
    }

    protected function generateFormCC()
    {
        $total_cart = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        if ((float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_VISA') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_VISA')) {
            $frequente = 1;
        } else {
            $frequente = 0;
        }

        $this->context->smarty->assign([
            'frequente' => $frequente,
            'action' => $this->context->link->getModuleLink($this->name, 'visa', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_visa.tpl');
    }

    protected function generateFormMB()
    {
        $total_cart = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        if ((float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_MB') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_MB')) {
            $frequente = 1;
        } else {
            $frequente = 0;
        }


        $this->context->smarty->assign([
            'frequente' => $frequente,
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_mb.tpl');
    }

    protected function generateFormDD()
    {
        $total_cart = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        if ((float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_DD') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_DD')) {
            $frequente = 1;
        } else {
            $frequente = 0;
        }


        $this->context->smarty->assign([
            'frequente' => $frequente,
            'action' => $this->context->link->getModuleLink($this->name, 'dd', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_dd.tpl');
    }

    protected function generateFormDDS()
    {

        $total_cart = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        if ((float) $total_cart >= (float) Configuration::get('EASYPAY_MIN_DD') && (float) $total_cart <= (float) Configuration::get('EASYPAY_MAX_DD')) {
            $frequente = 1;
        } else {
            $frequente = 0;
        }


        $this->context->smarty->assign([
            'frequente' => $frequente,
            'action' => $this->context->link->getModuleLink($this->name, 'dds', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_dds.tpl');
    }

    protected function generateFormFrequent($id_payment)
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'frequentvisa', array(), true),
            'id_payment' => $id_payment
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_frequent.tpl');
    }

    protected function generateFormFrequentMB($id_payment)
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'frequentmb', array(), true),
            'id_payment' => $id_payment
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_frequent.tpl');
    }


    protected function generateFormFrequentDD($id_payment)
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'frequentdd', array(), true),
            'id_payment' => $id_payment
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_frequent.tpl');
    }

    protected function generateFormFrequentMBWAY($id_payment)
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'frequentmbway', array(), true),
            'id_payment' => $id_payment
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_frequent.tpl');
    }



    public function hookPaymentReturn($params)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active) {
            return;
        }

        return $this->fetch('module:easypay/views/templates/hook/payment_return.tpl');
    }





    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $api_id = (string)Tools::getValue('EASYPAY_API_ID');
            $api_key = (string)Tools::getValue('EASYPAY_API_KEY');
            $api_visa = (string)Tools::getValue('PRESTASHOP_INPUT_SWITCH');
            $api_multibanco = (string)Tools::getValue('activar_multibanco');
            $api_mbw = (string)Tools::getValue('activar_mbw');
            $api_ip = (string)Tools::getValue('EASYPAY_IP');
            $api_dd = (string)Tools::getValue('activar_dd');
            $api_bb = (string)Tools::getValue('activar_bb');
            $api_testes = (string)Tools::getValue('activar_testes');
            $autorizar_pagos = (string)Tools::getValue('autorizar_pagos');

            $api_visa_min = (float)Tools::getValue('EASYPAY_MIN_VISA');
            $api_visa_max = (float)Tools::getValue('EASYPAY_MAX_VISA');

            $api_mb_min = (float)Tools::getValue('EASYPAY_MIN_MB');
            $api_mb_max = (float)Tools::getValue('EASYPAY_MAX_MB');

            $api_mbway_min = (float)Tools::getValue('EASYPAY_MIN_MBWAY');
            $api_mbway_max = (float)Tools::getValue('EASYPAY_MAX_MBWAY');

            $api_dd_min = (float)Tools::getValue('EASYPAY_MIN_DD');
            $api_dd_max = (float)Tools::getValue('EASYPAY_MAX_DD');
            $api_dias_esquecer = (float)Tools::getValue('EASYPAY_DIAS_ESQUECER');
            $api_dias_esquecer_mb = (float)Tools::getValue('EASYPAY_DIAS_ESQUECER_MB');



            Configuration::updateValue('EASYPAY_MAX_DD', $api_dd_max);
            Configuration::updateValue('EASYPAY_AUTORIZAR_PAGOS', $autorizar_pagos);
            Configuration::updateValue('EASYPAY_DIAS_ESQUECER', $api_dias_esquecer);
            Configuration::updateValue('EASYPAY_DIAS_ESQUECER_MB', $api_dias_esquecer_mb);
            Configuration::updateValue('EASYPAY_MIN_DD', $api_dd_min);
            Configuration::updateValue('EASYPAY_MAX_VISA', $api_visa_max);
            Configuration::updateValue('EASYPAY_MIN_VISA', $api_visa_min);
            Configuration::updateValue('EASYPAY_MAX_MB', $api_mb_max);
            Configuration::updateValue('EASYPAY_MIN_MB', $api_mb_min);
            Configuration::updateValue('EASYPAY_MAX_MBWAY', $api_mbway_max);
            Configuration::updateValue('EASYPAY_MIN_MBWAY', $api_mbway_min);

            Configuration::updateValue('EASYPAY_API_ID', $api_id);
            Configuration::updateValue('EASYPAY_API_IP', $api_ip);
            Configuration::updateValue('EASYPAY_API_KEY', $api_key);
            Configuration::updateValue('EASYPAY_VISA', $api_visa);
            Configuration::updateValue('EASYPAY_MULTIBANCO', $api_multibanco);
            Configuration::updateValue('EASYPAY_MBW', $api_mbw);
            Configuration::updateValue('EASYPAY_DD', $api_dd);
            Configuration::updateValue('EASYPAY_BB', $api_bb);
            Configuration::updateValue('EASYPAY_TESTES', $api_testes);



            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }


    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $picking_history = array(array('id' => 'actvisa', 'name' => ''));
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Ativar ambiente de testes'),
                    'name' => 'activar_testes',
                    'is_bool' => true,
                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],


                [
                    'type' => 'switch',
                    'label' => $this->l('Autorização e Captura Automática'),
                    'name' => 'autorizar_pagos',
                    'hint' => $this->l('Quando esta opção está ativa, os pagamentos são autorizados automaticamente, senão terão que ser validados manualmente.'),
                    'is_bool' => true,
                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],


                [
                    'type' => 'text',
                    'hint' => $this->l('Depois de ultrapassado o período escolhido, o método de pagamento será removido'),
                    'label' => $this->l('Número de dias que cada método de pagamento deve ficar guardado - Pagamento Frequente'),
                    'name' => 'EASYPAY_DIAS_ESQUECER',
                    'size' => 20,
                    'required' => true
                ],




                [
                    'type' => 'text',
                    'hint' => $this->l('Esta configuração está disponível no backoffice do EasyPay em: [Web Services->Configuração API->Chaves->ID]'),
                    'label' => $this->l('API ID'),
                    'name' => 'EASYPAY_API_ID',
                    'size' => 20,
                    'required' => true
                ],

                [
                    'type' => 'text',
                    'hint' => $this->l('Esta configuração está disponível no backoffice do EasyPay em: [Web Services->Configuração API->Chaves->Chave]'),
                    'label' => $this->l('API KEY'),
                    'name' => 'EASYPAY_API_KEY',
                    'class'    => 'panel-group',
                    'size' => 20,
                    'required' => true
                ],

                [
                    'type' => 'switch',
                    'label' => $this->l('Ativar CARTÕES VISA / MASTERCARD'),
                    'name' => 'PRESTASHOP_INPUT_SWITCH',
                    'is_bool' => true,
                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante Mínimo para pagamentos frequentes com cartões Visa / Mastercard'),
                    'name' => 'EASYPAY_MIN_VISA',
                    'suffix' => '€',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante máximo para pagamentos frequentes com cartões Visa / Mastercard'),
                    'name' => 'EASYPAY_MAX_VISA',
                    'class'    => 'panel-group',
                    'suffix' => '€',
                    'size' => 20,
                    'required' => true,
                    'attr' => array('class' => 'form-group')
                ],

                [
                    'type' => 'switch',
                    'label' => $this->l('Ativar Multibanco'),
                    'name' => 'activar_multibanco',
                    'is_bool' => true,
                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],

                [
                    'type' => 'text',
                    'hint' => $this->l('Depois de ultrapassado o período escolhido, a referência multibanco será removida'),
                    'label' => $this->l('Data de expiração da referência multibanco'),
                    'desc' => $this->l('Número maximo de dias é 180'),
                    'name' => 'EASYPAY_DIAS_ESQUECER_MB',
                    'size' => 20,
                    'required' => true
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('Montante Mínimo para pagamentos frequentes com MB'),
                    'suffix' => '€',
                    'name' => 'EASYPAY_MIN_MB',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante máximo para pagamentos frequentes com MB'),
                    'class'    => 'panel-group',
                    'suffix' => '€',
                    'name' => 'EASYPAY_MAX_MB',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Ativar MBWAY'),
                    'name' => 'activar_mbw',
                    'is_bool' => true,
                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante Mínimo para pagamentos frequentes com MBWAY'),
                    'suffix' => '€',
                    'name' => 'EASYPAY_MIN_MBWAY',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante máximo para pagamentos frequentes com MBWAY'),
                    'class'    => 'panel-group',
                    'suffix' => '€',
                    'name' => 'EASYPAY_MAX_MBWAY',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Ativar Débito Direto'),
                    'name' => 'activar_dd',
                    'is_bool' => true,

                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante Mínimo para pagamentos frequentes com Débito Direto'),
                    'suffix' => '€',
                    'name' => 'EASYPAY_MIN_DD',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montante máximo para pagamentos frequentes com Débito Direto'),
                    'class'    => 'panel-group',
                    'suffix' => '€',
                    'name' => 'EASYPAY_MAX_DD',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Ativar Boleto'),
                    'name' => 'activar_bb',
                    'is_bool' => true,
                    //'desc' => $this->l('Description'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Value1')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Value2')
                        )
                    ),
                ],
                // [
                //     'type' => 'text',
                //     'label' => $this->l('IP PERMITIDO'),
                //     'name' => 'EASYPAY_IP',
                //     'desc' => $this->l('Indique o IP do gateway para maior segurança.'),
                //     'hint' => $this->l('Inserir o IP da EASYPAY (SISTEMA DE SEGURANÇA)'),
                //     'size' => 30,
                //     'required' => false
                // ],
                [
                    'type' => 'text',
                    'label' => $this->l('GENERIC LINK'),
                    'name' => 'EASYPAY_GENERIC_LINK',
                    'hint' => $this->l('Deves inserir este URL no BackOffice do Easypay [Web Services->Configuração API->Notificações] especificamente nas caixas de "Generic - URL", "Payment - URL" e "Authorisation - URL"'),
                    'size' => 20,
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('VISA DETALHE'),
                    'name' => 'EASYPAY_VISA_DETALHES',
                    'hint' => $this->l('Deves inserir este URL no BackOffice do Easypay [Web Services->consiguração URL -> VISA: Detalhe], caso contrario vai ser cobrado 0,5% do valor da encomenda como penalização'),
                    'size' => 20,
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('VISA FORWARD'),
                    'name' => 'EASYPAY_VISA_FOWARD',
                    'hint' => $this->l('Deves inserir este URL no BackOffice do Easypay [Web Services->consiguração URL -> VISA: Forward], caso contrario vai ser cobrado 0,5% do valor da encomenda como penalização'),
                    'size' => 20,
                    'required' => false
                ],
                // [
                //     'type' => 'text',
                //     'label' => $this->l('CRONJOB'),
                //     'name' => 'EASYPAY_CRONJOB',
                //     'hint' => $this->l('Usado para atualizar MBWAY'),
                //     'desc' => 'Crie um cronjob no seu servidor com este PATH que será executado a cada 1 minuto',
                //     'required' => false
                // ],

            ],

            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value
        $helper->fields_value['activar_testes'] = Configuration::get('EASYPAY_TESTES');

        $helper->fields_value['EASYPAY_DIAS_ESQUECER'] = Configuration::get('EASYPAY_DIAS_ESQUECER');
        $helper->fields_value['EASYPAY_DIAS_ESQUECER_MB'] = Configuration::get('EASYPAY_DIAS_ESQUECER_MB');
        $helper->fields_value['EASYPAY_IP'] = Configuration::get('EASYPAY_API_IP');
        $helper->fields_value['EASYPAY_API_ID'] = Configuration::get('EASYPAY_API_ID');
        $helper->fields_value['autorizar_pagos'] = Configuration::get('EASYPAY_AUTORIZAR_PAGOS');
        $helper->fields_value['EASYPAY_VISA_DETALHES'] = _PS_BASE_URL_ . '' . __PS_BASE_URI__ . 'modules/easypay/visadetails.php';
        $helper->fields_value['EASYPAY_VISA_FOWARD'] = _PS_BASE_URL_ . '' . __PS_BASE_URI__ . 'index.php?fc=module&module=easypay&controller=visafoward';


        $helper->fields_value['EASYPAY_API_KEY'] = Configuration::get('EASYPAY_API_KEY');
        $helper->fields_value['PRESTASHOP_INPUT_SWITCH'] = Configuration::get('EASYPAY_VISA');
        $helper->fields_value['EASYPAY_MIN_VISA'] = Configuration::get('EASYPAY_MIN_VISA');
        $helper->fields_value['EASYPAY_MAX_VISA'] = Configuration::get('EASYPAY_MAX_VISA');


        $helper->fields_value['activar_multibanco'] = Configuration::get('EASYPAY_MULTIBANCO');
        $helper->fields_value['EASYPAY_MIN_MB'] = Configuration::get('EASYPAY_MIN_MB');
        $helper->fields_value['EASYPAY_MAX_MB'] = Configuration::get('EASYPAY_MAX_MB');


        $helper->fields_value['activar_bb'] = Configuration::get('EASYPAY_BB');


        $helper->fields_value['activar_mbw'] = Configuration::get('EASYPAY_MBW');
        $helper->fields_value['EASYPAY_MIN_MBWAY'] = Configuration::get('EASYPAY_MIN_MBWAY');
        $helper->fields_value['EASYPAY_MAX_MBWAY'] = Configuration::get('EASYPAY_MAX_MBWAY');


        $helper->fields_value['activar_dd'] = Configuration::get('EASYPAY_DD');
        $helper->fields_value['EASYPAY_MIN_DD'] = Configuration::get('EASYPAY_MIN_DD');
        $helper->fields_value['EASYPAY_MAX_DD'] = Configuration::get('EASYPAY_MAX_DD');
        $helper->fields_value['EASYPAY_CRONJOB'] = dirname(__FILE__) . "/cronjob.php";


        $helper->fields_value['EASYPAY_GENERIC_LINK'] = _PS_BASE_URL_ . __PS_BASE_URI__ . "modules/easypay/receive_success.php";


        return $helper->generateForm($fieldsForm);
    }
}

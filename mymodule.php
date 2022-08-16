<?php 
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
class MyModule extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->author = 'PrestaSafe';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('This is my custom module', [], 'Modules.MyModule.Admin');
        $this->description = $this->trans('Display a custom text on left Column', [], 'Modules.MyModule.Admin');

        $this->templateFile = 'module:mymodule/views/templates/hook/mymodule.tpl';
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayLeftColumn') && $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->unregisterHook('displayLeftColumn') && $this->unregisterHook('displayHeader');
    }

    public function postProcess()
    {
        if(Tools::isSubmit('btnSubmitMyModule'))
        {

            foreach (Language::getLanguages(false) as $lang) {
                Configuration::updateValue('TEST_INPUT_'.$lang['id_lang'], Tools::getValue('TEST_INPUT_'.$lang['id_lang']), true);
            }
        }
    }

    public function getContent()
    {
       
        return $this->postProcess().$this->renderForm();
    }

    public function hookdisplayHeader()
    {
        if($this->context->controller->php_self == 'category')
        {
            $this->context->controller->addCSS($this->_path . 'views/css/front.css', 'all');
        }
            
    }

    public function hookdisplayTest()
    {
        return "display test";
    }

    // public function hookdisplayLeftColumn()
    // {
    //     $this->context->smarty->assign('message',Configuration::get('TEST_INPUT_'.$this->context->language->id));
    //     return $this->fetch($this->templateFile); 
    // }


    public function renderForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Contact details', [], 'Modules.Checkpayment.Admin'),
                    'icon' => 'icon-envelope',
                ],
                'input' => [
                    [
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'label' => $this->trans('Payee (name)', [], 'Modules.Checkpayment.Admin'),
                        'name' => 'TEST_INPUT',
                        'required' => true,
                        'lang' => true
                    ],
                    // [
                    //     'type' => 'textarea',
                    //     'label' => $this->trans('Address', [], 'Modules.Checkpayment.Admin'),
                    //     'desc' => $this->trans('Address where the check should be sent to.', [], 'Modules.Checkpayment.Admin'),
                    //     'name' => 'CHEQUE_ADDRESS',
                    //     'required' => true,
                    // ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = $this->name;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitMyModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
            ];
        }
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        $res = [];
        foreach (Language::getLanguages(false) as $lang) {
            $res['TEST_INPUT'][$lang['id_lang']] = Tools::getValue('TEST_INPUT_'.$lang['id_lang'], Configuration::get('TEST_INPUT_'.$lang['id_lang']));
        }

        return $res;

    }


      /**
     * {@inheritdoc}
     */
    public function renderWidget($hookName, array $configuration)
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch(
           $this->templateFile
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetVariables($hookName, array $configuration)
    {
        return [
            'message' => Configuration::get('TEST_INPUT_'.$this->context->language->id)
        ];
    }
}
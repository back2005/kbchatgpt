<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2018 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Added demo flag for the module
 * @date 30-12-2024
 * @modifier Amit Singh
 */
define('KB_CHAT_DEMO', false);

class Kbchatgpt extends Module
{   
    const PARENT_TAB_CLASS = 'AdminkbchatgptConfigure';
    const SELL_CLASS_NAME = 'SELL';

    public function __construct()
    {
        $this->name = 'kbchatgpt';
        $this->tab = 'administration';
        $this->version = '1.0.3';
        $this->author = 'Knowband';
        $this->bootstrap = true;
        parent::__construct();
        $this->module_key = 'f3fa44ac4e1a215581af19c105ab85ee';
        $this->author_address = '0x2C366b113bd378672D4Ee91B75dC727E857A54A6';
        $this->displayName = $this->l('Knowband Prestashop ChatGPT Generator/Translator');
        $this->description = $this->l('Translate product and category details using ChatGPT.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /**
     * Below function returns the module tabs array for the left menu in Admin
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return array
     */
    public function adminSubMenus()
    {
        $subMenu = array(
            array(
                'class_name' => 'AdminKbchatgptGeneralSetting',
                'name' => $this->l('General Settings'),
                'active' => 1,
            ),
            array(
                'class_name' => 'AdminKbchatgptPrompts',
                'name' => $this->l('ChatGPT Prompts'),
                'active' => 1,
            ),
            array(
                'class_name' => 'AdminKbchatgptLogs',
                'name' => $this->l('Tasks Log'),
                'active' => 1,
            )
            );

        return $subMenu;
    }

    /**
     * Below function creates the module tabs in the Admin
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return bool
     */
    public function installKbTabs()
    {
        $parentTab = new Tab();
        $parentTab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $parentTab->name[$lang['id_lang']] = $this->l('Knowband ChatGPT');
        }

        $parentTab->class_name = self::PARENT_TAB_CLASS;
        $parentTab->module = $this->name;
        $parentTab->active = 1;
        $parentTab->id_parent = Tab::getIdFromClassName(self::SELL_CLASS_NAME);
        $parentTab->icon = 'bookmark';
        $parentTab->add();

        $id_parent_tab = (int) Tab::getIdFromClassName(self::PARENT_TAB_CLASS);
        $admin_menus = $this->adminSubMenus();

        foreach ($admin_menus as $menu) {
            $tab = new Tab();
            foreach (Language::getLanguages(true) as $lang) {
                if ($this->getModuleTranslationByLanguage($this->name, $menu['name'], $this->name, $lang['iso_code']) != '') {
                    $tab->name[$lang['id_lang']] = $this->getModuleTranslationByLanguage($this->name, $menu['name'], $this->name, $lang['iso_code']);
                } else {
                    $tab->name[$lang['id_lang']] = $menu['name'];
                }
            }
            $tab->class_name = $menu['class_name'];
            $tab->module = $this->name;
            $tab->active = $menu['active'];
            $tab->id_parent = $id_parent_tab;
            $tab->add($this->id);
        }
        return true;
    }

    /**
     * Below function is responsible to register the Hook and create the tables in the database
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return bool
     */
    public function install()
    {
        $this->installKbTabs();
        
        $sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chatgpt_logs` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `prompt_type` TEXT,
            `entity_id` INT(11),
            `entity_type` ENUM("Product", "Category"),
            `entity_lang` INT(11),
            `prev_content` TEXT,
            `new_content` TEXT,
            `error` TEXT,
            `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`log_id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
    
        $sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chatgpt_prompts` (
            `prompt_id` INT(11) NOT NULL AUTO_INCREMENT,
            `prompt_type` TEXT NOT NULL,
            `prompt_content` TEXT NOT NULL,
            PRIMARY KEY (`prompt_id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        
        // Execute the SQL to create the table
        if (!(Db::getInstance()->execute($sql1)
            && Db::getInstance()->execute($sql2))) {
            return false;
        }
        // Insert default prompts
        $this->resetPromptsToDefault();
        if (!Configuration::hasKey('KBChat_MODULE_CONFIGURATIONS')) {
            $defaultsettings = json_encode($this->getDefaultSettings());
            Configuration::updateValue('KBChat_MODULE_CONFIGURATIONS', $defaultsettings);
        }
        return parent::install() && $this->registerHook('displayBackOfficeHeader');
    }

    /**
     * Reset the prompts table to the default set of prompts
     * @date 15-06-2025
     * @modifier GPT Agent
     * @return void
     */
    public function resetPromptsToDefault()
    {
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'chatgpt_prompts`');
        foreach ($this->getDefaultPrompts() as $prompt) {
            Db::getInstance()->insert(
                'chatgpt_prompts',
                array(
                    'prompt_type' => pSQL($prompt['prompt_type']),
                    'prompt_content' => pSQL($prompt['prompt_content'], true),
                )
            );
        }
    }

    /**
     * Return the latest default template for generating product descriptions.
     *
     * @return string
     */
    public function getDefaultProductDescriptionPrompt()
    {
        return "Stwórz mi opis w tym stylu: <p>Brązowy t-shirt damski basic Fire to wygodna baza do codziennych stylizacji. Luźniejszy krój miękko układa się na sylwetce i nie krępuje ruchów, a trójkątny dekolt na plecach dodaje koszulce kobiecego pazura.</p>\n<p>Bawełniana dzianina z dodatkiem elastanu jest miękka, przewiewna i elastyczna, dzięki czemu t-shirt świetnie sprawdza się zarówno solo, jak i jako pierwsza warstwa pod bluzy czy swetry. Noś go do jeansów, spódnic czy dopasowanych spodni – na co dzień, do pracy lub na spotkania ze znajomymi.</p>\n<ul>\n<li><strong>Skład:</strong> 90% bawełna, 10% elastan</li>\n<li><strong>Sposób prania:</strong> pranie w pralce w 30°C</li>\n<li><strong>Modelka:</strong> ma na sobie rozmiar S (wzrost 176 cm, biust 85 cm, talia 64 cm, biodra 92 cm)</li>\n<li><strong>Wymiary t-shirtu w rozmiarze S mierzone na płasko:</strong> szerokość pod pachami 56 cm, długość całkowita 67 cm</li>\n</ul>\n\nOdnosząc się do: {description}\nWygeneruj mi tylko kod html bez żadnych komentarzy";
    }

    /**
     * Return the legacy default template that shipped before the style update.
     *
     * @return string
     */
    public function getLegacyProductDescriptionPrompt()
    {
        return "Długi opis:\nOriginal description: {description}\nImprove the description while keeping all HTML structure intact. Return only the refreshed description content.\nCreating it for SEO while maintaining this style:\n\n    The brown Fire basic women's T-shirt is a comfortable base for everyday styling.\n    The looser cut fits softly on the figure and does not restrict movement, while\n    the triangular neckline at the back adds a feminine touch to the shirt.\n  \n  \n    The cotton fabric with elastane is soft, breathable, and stretchy,\n    making the T-shirt perfect for wearing on its own or as a base layer\n    under sweatshirts or sweaters. Wear it with jeans, skirts, or fitted\n    pants—every day, to work, or when meeting friends.\n  \n\n  \n    Composition: 90% cotton, 10% elastane\n    Washing instructions: machine wash at 30°C\n    \n      Model: wears size S\n      (height 176 cm, bust 85 cm, waist 64 cm, hips 92 cm)\n    \n    \n      Dimensions of the T-shirt in size S measured flat:\n      width under the arms 56 cm, total length 67 cm\n    ";
    }

    /**
     * Return the default prompts definitions
     * @date 15-06-2025
     * @modifier GPT Agent
     * @return array
     */
    private function getDefaultPrompts()
    {
        return array(
            array(
                'prompt_type' => 'Generate Product Summary',
                'prompt_content' => "Podsumowanie:\nCurrent summary: {summary}\nFull description: {description}\nCraft an improved short summary that blends both pieces of information. Preserve any HTML that appears in the summary, keep it concise, and return only the refreshed summary text.\nCreate it for SEO while maintaining this style:\nBrown basic Fire women's T-shirt with a neckline at the back – cotton, slightly loose, perfect for everyday wear, work, and going out with friends."
            ),
            array(
                'prompt_type' => 'Generate Product Description',
                'prompt_content' => $this->getDefaultProductDescriptionPrompt()
            ),
            array(
                'prompt_type' => 'Generate Product Title',
                'prompt_content' => "Tytuł:\nExisting title: {title}\nCurrent summary: {summary}\nCurrent description: {description}\nCreate a concise, compelling product title using all of the above context. Avoid using quotes and return only the final title text.\nCreate an SEO-friendly title in this style: “Brown women's basic T-shirt with a back neckline, Fire.”"
            ),
        );
    }

    /**
     * Below function returns the default settings for the module
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return array
     */
    private function getDefaultSettings()
    {
        $field_value = array(
            'module_status' => 0,
            'api_key' => '',
            'content_engine' => '',
            'default_language' => '',
            /**
            * Added default values for max token and temperature
            * @date 30-12-2024
            * @modifier Amit Singh
            */
            'chatgpt_max_token' => 500,
            'chatgpt_temperature' => 0.1,
            );
        return $field_value;
    }

    /**
     * Below function returns the content of the module configuration page
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return HTML
     */
    public function getContent()
    {
        $output = '';
        $errors = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = ((int) ($language['id_lang'] == Context::getContext()->language->id));
        }

        $output_msg = '';
            
        // Check if the form is submitted, then update the settings
        if (Tools::isSubmit('submit_button')) {

            $enable = Tools::getAllValues();
            $module_settings = array();
	        /**
             * Added fix for the demo mode and undefined index error
             * @date 30-12-2024
             * @modifier Amit Singh
             */
            $module_settings['KB_CHAT_DEMO'] = KB_CHAT_DEMO;
            $module_settings['module_status'] = isset($enable['module_status'])?$enable['module_status']:0;
            $module_settings['api_key'] = isset($enable['api_key'])?$enable['api_key']:'';
            $module_settings['content_engine'] = isset($enable['content_engine'])?$enable['content_engine']:'';
            $module_settings['chatgpt_temperature'] = isset($enable['chatgpt_temperature'])?(float)$enable['chatgpt_temperature']:0.1;
            $module_settings['chatgpt_max_token'] = isset($enable['chatgpt_max_token'])?(int)$enable['chatgpt_max_token']:500;
            $module_settings['default_language'] = isset($enable['default_language'])?$enable['default_language']:'';
            
            // Validate the API Key and if not proper then disable the module
            if($enable['api_key'] == '') {
                $errors[] = $this->l('API Key is empty');
                $module_settings['module_status'] = 0;
            } else {
                if(!$this->isApiKeyValid($enable['api_key'])) {
                    $errors[] = $this->l('Invalid API Key');
		           /**
                    * Added fix to disable the module if the API key is invalid and demo mode is off
                    * @date 30-12-2024
                    * @modifier Amit Singh
                    */
                    if (!KB_CHAT_DEMO) {
                        $module_settings['module_status'] = 0;
                    }
                    
                }
            }

            if(empty($errors)) {
                $output_msg = $this->displayConfirmation($this->l('Settings updated successfully.'));
            } 
            Configuration::updateValue('KBChat_MODULE_CONFIGURATIONS', json_encode($module_settings));
            // Display a confirmation message
            
        } else if (!Configuration::get('KBChat_MODULE_CONFIGURATIONS') || Configuration::get('KBChat_MODULE_CONFIGURATIONS') == '') {
            $module_settings = $this->getDefaultSettings();
        } else {
            $module_settings = json_decode(Configuration::get('KBChat_MODULE_CONFIGURATIONS'), true);
            if (!is_array($module_settings)) {
                $module_settings = $this->getDefaultSettings();
            }
        }
        $field_value = array(
            'module_status' => $module_settings['module_status'],
            'api_key' => $module_settings['api_key'],
            'content_engine' => $module_settings['content_engine'],
	        /**
            * Added default values for max token and temperature
            * @date 30-12-2024
            * @modifier Amit Singh
            */
            'chatgpt_max_token' => $module_settings['chatgpt_max_token'],
            'chatgpt_temperature' => $module_settings['chatgpt_temperature'],
            'default_language' => $module_settings['default_language'],
        );
        
        $action = AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&tab_module='.$this->tab.'&module_name=' . urlencode($this->name);
        
        $this->fields_form = $this->getConfigurationTabFields();
        $form = $this->getFormHtml($this->fields_form, $languages, $field_value, 'module_config', $action);
        $this->context->smarty->assign('form', $form);
        $this->context->smarty->assign('firstCall', false);

        $this->context->smarty->assign(
            'admin_pb_configure_controller',
            $this->context->link->getAdminLink('AdminModules', true).'&configure='.urlencode($this->name).'&tab_module='.$this->tab.'&module_name='.urlencode($this->name)
        );

        $this->context->smarty->assign(
            'admin_pb_prompts',
            $this->context->link->getAdminLink('AdminKbchatgptPrompts', true)
        );

        $this->context->smarty->assign(
            'admin_pb_logs',
            $this->context->link->getAdminLink('AdminKbchatgptLogs', true)
        );

        $this->context->smarty->assign('selected_nav', 'config');

        $this->context->smarty->assign('kb_admin_link', $this->context->link->getAdminLink('AdminKbchatgptPrompts', true).'&configure='. $this->name.'&ajaxproductaction=true');
        $kbTabs = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->name.'/views/templates/admin/kb_tabs.tpl'
        );

        $this->context->smarty->assign('kb_tabs', $kbTabs);

        $this->context->smarty->assign('errors', $errors);
        $output = $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/errors.tpl');

        $this->context->controller->addCSS($this->_path . 'views/css/admin/kbchatgpt_admin.css');

        $configInfoTPL = $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/config_info.tpl');

        // Render the form and return the output
        $output = $output_msg . $output . $kbTabs . $form . $configInfoTPL;
        return $output;
    }

    /**
     * Below function returns the HTML of the General Settings form
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param array $field_form
     * @return HTML
     */
    public function getFormHtml($field_form, $languages, $field_value, $id, $action)
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->fields_value = $field_value;
        $helper->name_controller = $this->name;
        $helper->languages = $languages;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->default_form_language = $this->context->language->id;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        if ($id == 'module_config') {
            $helper->show_toolbar = true;
        } else {
            $helper->show_toolbar = false;
        }
        $helper->table = $id;
        $helper->toolbar_scroll = true;
        $helper->show_cancel_button= false;
        $helper->submit_action = $action;
        return $helper->generateForm(array('form' => $field_form));
    }

    /**
     * Below function returns the form structure of the General Settings
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return array
     */
    public function getConfigurationTabFields()
    {
        $fields_form = [
            'form' => [
                'id_form' => 'kbchatgpt_general_settings',
                'legend' => [
                    'title' => $this->l('Knowband ChatGPT General Settings'),
                ],
                'input' => [
                    [
                        'label' => $this->l('Enable/Disable the module'),
                        'type' => 'switch',
                        'class' => 'module_config_tab',
                        'name' => 'module_status',
                        'values' => array(
                            array(
                                'id' => 'module_status_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'module_status_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Enable or Disable the plugin functionality'),
                    ],
                    // API Key for ChatGPT Field
                    [
                        'label' => $this->l('API Key for ChatGPT'),
                        'type' => 'text',
                        'name' => 'api_key',
                        'required' => true,
                        'hint' => $this->l('Enter the API key to communicate with ChatGPT.'),
                        'validation' => 'isApiKeyValid',  // Custom validation function to check API key
                    ],
                    [
                        'label' => $this->l('ChatGPT Engine for Content Generation'),
                        'type' => 'select',
                        'name' => 'content_engine',
                        'options' => [
                            'query' => [
                                ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo'],
                                ['id' => 'gpt-4', 'name' => 'GPT-4'],
				                /**
                                 * Added GPT-4 Turbo and GPT-4.0
                                 * @date 30-12-2024
                                 * @modifier Amit Singh
                                 */
                                ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo'],
                                ['id' => 'gpt-4.0', 'name' => 'GPT-4.0'],
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'hint' => $this->l('Select the ChatGPT engine to be used for content generation.'),
                        'required' => true,
                        'desc' => $this->l('GPT-3.5-turbo - Cost-effective, suitable for general tasks.') . '<br>' . $this->l('GPT-4 - Advanced model with improved accuracy and context handling (More expensive then GPT-3.5'). '<br>' . $this->l('GPT-4-turbo - Advanced model with improved accuracy and context handling(More expensive then GPT-3.5).'). '<br>' . $this->l('GPT-4.0 - Future release of the GPT-4 series.')

                    ],
		            /**
                     * Added fields for max token and temperature
                     * @date 30-12-2024
                     * @modifier Amit Singh
                     */
                    [
                        'label' => $this->l('Temperature'),
                        'type' => 'text',
                        'name' => 'chatgpt_temperature',
                        'required' => true,
                        'hint' => $this->l('A higher temperature like 0.9 makes outputs more creative or random, while a lower temperature like 0.1 makes them more focused and deterministic.'),

                    ],
                    [
                        'label' => $this->l('Maximum Tokens'),
                        'type' => 'text',
                        'name' => 'chatgpt_max_token',
                        'required' => true,
                        'hint' => $this->l('Tokens represent chunks of words or characters. Use 500 as default maximum token.'),
                    ],
                    [
                        'label' => $this->l('Default Language for Content Generation'),
                        'type' => 'select',
                        'name' => 'default_language',
                        'options' => [
                            'query' => Language::getLanguages(false),  // Get available languages in Prestashop
                            'id' => 'id_lang',
                            'name' => 'name'
                        ],
                        'hint' => $this->l('Select the default language to be used for content generation.'),
                        'required' => true,
                    ]
                ],
                'buttons' => array(
                    array(
                        'type' => 'submit',
                        'title' => $this->l('Save'),       
                        'class' => 'btn btn-default pull-right',
                        'name' => 'submit_button',
                    )   
                ),
            ]
        ];
        return $fields_form;
    }

    /**
     * Below function is responsible to add the JS and CSS files on the Admin controllers 
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return bool
     */
    public function hookDisplayBackOfficeHeader() {

        $moduleSettings = json_decode(Configuration::get('KBChat_MODULE_CONFIGURATIONS'), true);

        if(is_array($moduleSettings) && isset($moduleSettings['module_status']) && $moduleSettings['module_status'] == 1) {
            if ('AdminProducts' == Tools::getValue('controller')) {
                $this->context->controller->addJS($this->_path . 'views/js/admin/products.js');
                /**
                 * Updated the file name in the path to load the CSS file
                 * @modifier Himanshu Vishwakarma
                 * @date 19-03-2025
                 */
                $this->context->controller->addCSS($this->_path . 'views/css/admin/kbchatgp.css');
            }
    
            if ('AdminProducts' == Tools::getValue('controller')) {

                $ajaxUrl = $this->context->link->getAdminLink('AdminKbchatgptPrompts', true);
                $currentDomain = $_SERVER['HTTP_HOST'];
                $parsedUrl = parse_url($ajaxUrl);
                $originalDomain = $parsedUrl['host'];
                $ajaxUrl = str_replace($originalDomain, $currentDomain, $ajaxUrl);
    
                // load jquery if not already loaded
                $this->context->controller->addJquery();
                
                $entity = 'product';

                /**
                 * Added demo flag for the module
                 * @date 30-12-2024
                 * @modifier Amit Singh
                 */
                $demo = isset($moduleSettings['KB_CHAT_DEMO'])?$moduleSettings['KB_CHAT_DEMO']:0;
                
                Media::addJsDef([
                    'ajaxUrl' => $ajaxUrl,
                    'kbchatgpt' => $demo,
                    'textdemocontentEngine' => $this->l('Content Can not be generated in Demo'),
                    'textgenerateProductSummary' => $this->l('Generate Product Summary'),
                    'textgenerateProductDescription' => $this->l('Generate Product Description'),
                    'textgenerateProductTitle' => $this->l('Generate Product Title'),
                    'textcontentEngine' => $this->l('Content will be generated in a few minutes.'),
                    'textError' => $this->l('An error occured.'),
                    'kbentity' => $entity,
                ]);
            }
        }
    }

    /**
     * Below function is responsible to get the module translation by language
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return string
     */
    public function getModuleTranslationByLanguage($module, $string, $source, $language, $sprintf = null, $js = false)
    {
        $modules = array();
        $langadm = array();
        $translations_merged = array();
        $name = $module instanceof Module ? $module->name : $module;
        
        if (!isset($translations_merged[$name]) && isset(Context::getContext()->language)) {
            $files_by_priority = array(
                _PS_MODULE_DIR_ . $name . '/translations/' . $language . '.php'
            );
            foreach ($files_by_priority as $file) {
                if (file_exists($file)) {
                    include($file);
                    /* No need to define $_MODULE as it is defined in the above included file. */
                    $modules = $_MODULE;
                    $translations_merged[$name] = true;
                }
            }
        }

        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);
        if ($modules == null) {
            if ($sprintf !== null) {
                $string = Translate::checkAndReplaceArgs($string, $sprintf);
            }

            return str_replace('"', '&quot;', $string);
        }
        $current_key = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $source) . '_' . $key;
        $default_key = Tools::strtolower('<{' . $name . '}prestashop>' . $source) . '_' . $key;
        if ('controller' == Tools::substr($source, -10, 10)) {
            $file = Tools::substr($source, 0, -10);
            $current_key_file = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $file) . '_' . $key;
            $default_key_file = Tools::strtolower('<{' . $name . '}prestashop>' . $file) . '_' . $key;
        }

        if (isset($current_key_file) && !empty($modules[$current_key_file])) {
            $ret = Tools::stripslashes($modules[$current_key_file]);
        } elseif (isset($default_key_file) && !empty($modules[$default_key_file])) {
            $ret = Tools::stripslashes($modules[$default_key_file]);
        } elseif (!empty($modules[$current_key])) {
            $ret = Tools::stripslashes($modules[$current_key]);
        } elseif (!empty($modules[$default_key])) {
            $ret = Tools::stripslashes($modules[$default_key]);
        // if translation was not found in module, look for it in AdminController or Helpers
        } elseif (!empty($langadm)) {
            /**
             * Update value for ret variable since it was showing error in ps validator
             * @modifier Himanshu Vishwakarma
             * @date 19-03-2025
             */
            //$ret = Tools::stripslashes(Translate::getGenericAdminTranslation($string, $key, $langadm));
            $ret = Tools::stripslashes(
                \Context::getContext()->getTranslator()->trans(
                    $string,
                    [],
                    'Admin.Global'  // Replace with the appropriate translation domain if needed
                )
                );
        } else {
            $ret = Tools::stripslashes($string);
        }

        if ($sprintf !== null) {
            $ret = Translate::checkAndReplaceArgs($ret, $sprintf);
        }

        if ($js) {
            $ret = addslashes($ret);
        } else {
            $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
        }
        return $ret;
    }

    /**
     * Below function is responsible to uninstall the module tabs
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return bool
     */
    protected function unInstallKbTabs()
    {
        $parentTab = new Tab(Tab::getIdFromClassName(self::PARENT_TAB_CLASS));
        $parentTab->delete();

        $admin_menus = $this->adminSubMenus();

        foreach ($admin_menus as $menu) {
            $sql = 'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE class_name = "' . pSQL($menu['class_name']) . '" 
                AND module = "' . pSQL($this->name) . '"';
            $id_tab = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    public function uninstall()
    {
        $this->unInstallKbTabs();
        return parent::uninstall() && $this->unregisterHook('displayBackOfficeHeader');
    }

    /**
     * Below function is responsible to check if the API key is valid
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param string $apiKey
     * @return bool
     */
    private function isApiKeyValid($apiKey)
    {
        $url = 'https://api.openai.com/v1/models'; // URL to send the test request to OpenAI

        $response = null;

        try {
            // Create a cURL handle
            $ch = curl_init($url);

            // Set the headers including the API key
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);

            // Get the HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Close cURL session
            curl_close($ch);
            
            // If the status code is 200, the API key is valid
            return $httpCode === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

}

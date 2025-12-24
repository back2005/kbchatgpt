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
include_once(_PS_MODULE_DIR_.'kbchatgpt/classes/kbchatgptPrompts.php');
class AdminKbchatgptPromptsController extends ModuleAdminController
{
    protected $kb_module_name = 'kbchatgpt';

    /**
     * Below function is the constructor function of the class
     * It is used to initialize the class level variables and list the table fields
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->allow_export = false;
        $this->context = Context::getContext();
        $this->list_no_link = true;
        $this->all_languages = $this->getAllLanguages();
        $this->table = 'chatgpt_prompts';
        $this->className = 'kbchatgptPrompts';
        $this->identifier = 'prompt_id';
        $this->lang = false;
        $this->display = 'list';
        parent::__construct();

        $this->ensurePromptTemplates();
        
        if (Tools::getValue('prompt_id')) {
            $this->toolbar_title = $this->module->l('Edit Prompt for ChatGPT', 'AdminKbchatgptPromptsController');
        } else {
            $this->toolbar_title = $this->module->l('ChatGPT Prompts Listing', 'AdminKbchatgptPromptsController');
        }
        $this->fields_list = array(
            'prompt_id' => array(
                'title' => $this->module->l('ID', 'AdminKbchatgptPromptsController'),
                'search' => true,
                'align' => 'left',
            ),
            'prompt_type' => array(
                'title' => $this->module->l('Prompt Name', 'AdminKbchatgptPromptsController'),
                'search' => true,
                'align' => 'left',
                /**
                * Below code is used to translate the prompt type
                * @date 30-12-2024
                * @modifier Amit Singh
                */
                'callback' => 'translatePromptType',
            ),
            'prompt_content' => array(
                'title' => $this->module->l('Prompt Content', 'AdminKbchatgptPromptsController'),
                'havingFilter' => false,
                'search' => false,
                'align' => 'left',
                'orderby' => false
            ),
        );

        $this->addRowAction('edit');
    }
    /**
     * Below function is used to define the module_dir variable
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function init() {
        parent::init();
        $this->module_dir = _PS_MODULE_DIR_ . 'kbchatgpt/';
    }

    /**
     * Below function is used to get all the languages
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return array
     */
    public function getAllLanguages()
    {
        return Language::getLanguages(false);
    }

    /**
     * Below function is used to set the media files
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param bool $isNewTheme
     * @return void
     */
    public function setmedia($isNewTheme = false)
    {
        parent::setmedia($isNewTheme);
        
        $this->addJS(_PS_MODULE_DIR_.$this->kb_module_name.'/views/js/admin/prompt_validation.js');
        $this->addCSS($this->module_dir .'views/css/admin/kbchatgpt_admin.css');
    }

    /**
     * Ensure only the supported prompts are present
     * @date 15-06-2025
     * @modifier GPT Agent
     * @return void
     */
    private function ensurePromptTemplates()
    {
        $existing = Db::getInstance()->executeS("SELECT prompt_type FROM " . _DB_PREFIX_ . "chatgpt_prompts");
        $existingTypes = array_map(function($row) {
            return $row['prompt_type'];
        }, $existing);

        $expected = array('Generate Product Summary', 'Generate Product Description', 'Generate Product Title');
        sort($existingTypes);
        $expectedSorted = $expected;
        sort($expectedSorted);

        if ($existingTypes !== $expectedSorted) {
            $this->module->resetPromptsToDefault();
        }
    }
   
   /**
     * Below function is used to translate the prompt type
     * @date 30-12-2024
     * @modifier Amit Singh
     * @param string $value, array $row
     * @return string
     */
    /**
     * Updated function to return the translated prompt type
     * @modifier Himanshu Vishwakarma
     * @date 19-03-2025
     */
    public function translatePromptType($value, $row)
    {
        if($value == 'Generate Product Summary') {
            return $this->module->l('Generate Product Summary', 'AdminKbchatgptPromptsController');
        } else if($value == 'Generate Product Description') {
            return $this->module->l('Generate Product Description', 'AdminKbchatgptPromptsController');
        } else if($value == 'Generate Product Title') {
            return $this->module->l('Generate Product Title', 'AdminKbchatgptPromptsController');
        }
        return '';
    }
    
    /**
     * Below function is used to get the content of the page
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function initContent()
    {
        if (Tools::getvalue('kbentity')) {
            if (Tools::getvalue('kbentity') == 'product') {
                echo $this->ajaxProductContent();
            }
            die;
        }
        if (isset($this->context->cookie->kb_redirect_success)) {
            $this->confirmations[] = $this->context->cookie->kb_redirect_success;
            unset($this->context->cookie->kb_redirect_success);
        }
        if (isset($this->context->cookie->kb_redirect_error)) {
            $this->errors[] = $this->context->cookie->kb_redirect_error;
            unset($this->context->cookie->kb_redirect_error);
        }
        
        $this->context->smarty->assign(
            'admin_pb_configure_controller',
            $this->context->link->getAdminLink('AdminModules', true).'&configure='.urlencode($this->module->name).'&tab_module='.$this->module->tab.'&module_name='.urlencode($this->module->name)
        );

        $this->context->smarty->assign(
            'admin_pb_prompts',
            $this->context->link->getAdminLink('AdminKbchatgptPrompts', true)
        );

        $this->context->smarty->assign(
            'admin_pb_logs',
            $this->context->link->getAdminLink('AdminKbchatgptLogs', true)
        );
        
        $this->context->smarty->assign('selected_nav', 'kbpb_rule_config');
        
        $this->context->smarty->assign('kb_admin_link', $this->context->link->getAdminLink('AdminKbchatgptPrompts', true).'&configure='. $this->module->name.'&ajaxproductaction=true');
        $kb_tabs = '';
        $kb_tabs = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/kb_tabs.tpl'
        );

        $this->content .= $kb_tabs;
        
        parent::initContent();
    }

    /**
     * Below function is used to get the form of the Prompt
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function renderForm()
    {
        if ((isset($this->tabAccess['edit']) && !$this->tabAccess['edit'] && Tools::getValue('prompt_id')) ||
            (isset($this->tabAccess['add']) && !$this->tabAccess['add'] && !Tools::getValue('prompt_id'))) {
            $this->errors[] = Tools::displayError('You do not have permission to use this form.');
            return false;
        }
        
        if (Tools::getValue('prompt_id')) {
            $template_action = $this->module->l('Edit', 'AdminKbchatgptPrompts');
        } else {
            $template_action = $this->module->l('Add', 'AdminKbchatgptPrompts');
        }
        $customizable = false;
        /**
         * Updated the format for creating the template, since kb_smarty variable has been removed
         * @modifier Himanshu Vishwakarma
         * @date 19-03-2025
         */
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'kbchatgpt/views/templates/admin/kb_prompt_form.tpl');
        
        $tpl->assign(array(
            'kb_form_contents' => $this->getAddFieldForm(),
            'edit_field_form' => (Tools::getValue('prompt_id') && Tools::getIsset('update'.$this->table)) ? 1 : 0,
            'template_action' => $template_action,
            'customizable' => $customizable,
            'moduledir_url' => $this->getModuleDirUrl(),
        ));

        Media::addJsDef([
            'error_blank' => $this->module->l('This field is required.', 'AdminKbchatgptPrompts')
        ]);
        
        return $tpl->fetch().parent::renderForm();
    }

    /**
     * Below function is used to get the values of the fields
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    protected function getEditFieldValues()
    {
        $prompt_id = Tools::getValue('prompt_id');
        $prompt_data = DB::getInstance()->getRow("SELECT * FROM "._DB_PREFIX_."chatgpt_prompts WHERE prompt_id = ".(int) $prompt_id);
        
        $field_value = array(
            'prompt_type' => $prompt_data['prompt_type'],
            'prompt_content' => $prompt_data['prompt_content'],
        );
        
        return $field_value;
    }

    /**
     * Below function is used to get the module directory URL
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return string
     */
    protected function getModuleDirUrl()
    {
        $module_dir = '';
        if ($this->checkSecureUrl()) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }

    /**
     * Below function is used to check if the URL is secure or not
     * @date 09-11-2024
     *  @modifier Nikhil Aggarwal
     * @return bool
     */
    private function checkSecureUrl()
    {
        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }
        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Below function returns the edit form fields for the Prompts
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return array
     */
    protected function getAddFieldForm()
    {
        $tpl_vars = array();
        $submit_btn = '';
        $field_value = array();
        $promptType = Tools::getValue('prompt_type');

        if ((Tools::getValue('prompt_id') != '') && Tools::getIsset('update'.$this->table)) {
            $submit_btn = 'update_submit_prompt';
            $field_value = $this->getEditFieldValues();
            if (isset($field_value['prompt_type'])) {
                $promptType = $field_value['prompt_type'];
            }
        }

        $this->fields_form = array(
            'form' => array(
                'id_form' => 'kbcf_edit_prompt',
                'legend' => array(
                    'title' => $this->module->l('Edit ChatGPT Prompt', 'AdminKbchatgptPrompts'),
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'name' => 'prompt_guidance',
                        'col' => 12,
                        'html_content' => $this->getPromptGuidanceBlock($promptType),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Prompt Type', 'AdminKbchatgptPrompts'),
                        'name' => 'prompt_type',
                        'required' => true,
                        'col' => 4,
                        'hint' => $this->module->l('For Admin Purpose Only', 'AdminKbchatgptPrompts'),
                        'disabled' => (Tools::getValue('prompt_id') && Tools::getIsset('update'.$this->table)) ? true : false,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->module->l('Prompt Content', 'AdminKbchatgptPrompts'),
                        'required' => true,
                        'name' => 'prompt_content',
                        'autoload_rte' => true,
                        'rows' => 12,
                        'col' => 4,
                        'desc' => $this->module->l('Template Variables Definition', 'AdminKbchatgptPrompts')
                        . '</br>' . $this->module->l('{product_name} - Name of the Product (Applicable only for Products)', 'AdminKbchatgptPrompts') . '<br/>'
                        . $this->module->l('{shop_name} - Name of the Shop', 'AdminKbchatgptPrompts') . '<br/>'
                        . $this->module->l('{category} - Name of the default category of the product', 'AdminKbchatgptPrompts') . '<br/>'
                        . $this->module->l('You can freely edit HTML here to guide the GPT output format.', 'AdminKbchatgptPrompts')
                    ),
                ),
                'submit' => array(
                    'title' => $this->module->l('Save', 'AdminKbchatgptPrompts'),
                    'class' => 'btn btn-default pull-right ' .$submit_btn
                ),
            ),
        );
        
        $this->context->smarty->assign('path', $this->module->name);

        $this->context->controller->addJs($this->getModuleDirUrl() . 'kbproductbadge/views/js/velovalidation.js');
        return $this->renderGenericForm(
            array(
                'form' => $this->fields_form
            ),
            $field_value,$this->token,
            $tpl_vars
        );
    }

    /**
     * Below function is used to render the Prompt form
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param array $fields_form, array $fields_value, string $admin_token, array $tpl_vars
     * @return string
     */
    public function renderGenericForm($fields_form, $fields_value, $admin_token, $tpl_vars = array())
    {
        $languages = $this->all_languages;
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = ((int) ($language['id_lang'] == $this->context->language->id));
        }
        $helper = new HelperForm($this);
        $this->setHelperDisplay($helper);
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->show_cancel_button = true;
        $helper->languages = $languages;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->fields_form = array();
        $helper->token = $admin_token;
        $helper->tpl_vars = array_merge(array(
                'fields_value' => $fields_value
            ), $tpl_vars);

        /**
         * Updated the format to generate form, since sticker.tpl file has been removed
         * @modifier Himanshu Vishwakarma
         * @date 19-03-2025
         */
        return $helper->generateForm($fields_form);
    }

    /**
     * Build the visual guidance block for each prompt with a button, usage notes and an HTML example
     * @date 16-06-2025
     * @modifier GPT Agent
     * @param string $promptType
     * @return string
     */
    private function getPromptGuidanceBlock($promptType)
    {
        $guideList = array(
            'Generate Product Summary' => array(
                'button' => $this->module->l('Korzystaj z: {summary} + {description}', 'AdminKbchatgptPromptsController'),
                'instruction' => $this->module->l('Twórz krótkie, marketingowe streszczenie na podstawie bieżącego opisu skróconego i pełnego. Zostaw HTML, który już istnieje, ale zadbaj o spójny ton.', 'AdminKbchatgptPromptsController'),
                'example_html' => '<div class="product-summary">\n  <p class="lead">Brązowy t-shirt Fire z dekoltem na plecach – miękka bawełna, luźny krój, idealny na co dzień.</p>\n  <p class="meta">Materiał: 90% bawełna, 10% elastan</p>\n</div>',
                'uses' => array('{summary}', '{description}'),
                'steps' => array(
                    array(
                        'label' => $this->module->l('Cel', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Opracuj zwięzłe streszczenie produktu w tonie marketingowym.', 'AdminKbchatgptPromptsController'),
                    ),
                    array(
                        'label' => $this->module->l('Działanie', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Wklej wygenerowany tekst w akapicie lead i zostaw układ meta poniżej.', 'AdminKbchatgptPromptsController'),
                    ),
                    array(
                        'label' => $this->module->l('Źródła danych', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Uzupełnij treść na podstawie pól {summary} oraz {description}.', 'AdminKbchatgptPromptsController'),
                    ),
                )
            ),
            'Generate Product Description' => array(
                'button' => $this->module->l('Korzystaj z: {description}', 'AdminKbchatgptPromptsController'),
                'instruction' => $this->module->l('Aktualizuj długi opis, zachowując istniejącą strukturę HTML i układ akapitów, list oraz wyróżnień.', 'AdminKbchatgptPromptsController'),
                'example_html' => '<section class="product-description">\n  <p class="intro">Bawełniany t-shirt Fire to baza do codziennych stylizacji. Luźny krój nie krępuje ruchów, a trójkątny dekolt na plecach dodaje lekkości.</p>\n  <ul class="features">\n    <li>Miękka dzianina z domieszką elastanu</li>\n    <li>Świetny jako warstwa pod bluzy i swetry</li>\n    <li>Pasuje do dżinsów, spódnic i spodni typu slim</li>\n  </ul>\n  <p class="meta">Pranie: 30°C | Skład: 90% bawełna, 10% elastan</p>\n</section>',
                'uses' => array('{description}'),
                'steps' => array(
                    array(
                        'label' => $this->module->l('Cel', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Rozwiń opis produktu, pozostawiając istniejące znaczniki sekcji, list i akapitów.', 'AdminKbchatgptPromptsController'),
                    ),
                    array(
                        'label' => $this->module->l('Działanie', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Wypełnij tekstem placeholdery w sekcji intro, liście features oraz bloku meta.', 'AdminKbchatgptPromptsController'),
                    ),
                    array(
                        'label' => $this->module->l('Źródła danych', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Treść generuj na bazie pola {description}.', 'AdminKbchatgptPromptsController'),
                    ),
                )
            ),
            'Generate Product Title' => array(
                'button' => $this->module->l('Korzystaj z: {title} + {summary} + {description}', 'AdminKbchatgptPromptsController'),
                'instruction' => $this->module->l('Stwórz krótki, chwytliwy tytuł na podstawie bieżącego tytułu oraz treści opisów. Bez cudzysłowów.', 'AdminKbchatgptPromptsController'),
                'example_html' => '<h1 class="product-title">Brązowy damski t-shirt basic z dekoltem na plecach Fire</h1>',
                'uses' => array('{title}', '{summary}', '{description}'),
                'steps' => array(
                    array(
                        'label' => $this->module->l('Cel', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Nadaj produktowi tytuł sprzedażowy, który pasuje do sklepu i SEO.', 'AdminKbchatgptPromptsController'),
                    ),
                    array(
                        'label' => $this->module->l('Działanie', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Podmień zawartość nagłówka H1, zostawiając klasę product-title.', 'AdminKbchatgptPromptsController'),
                    ),
                    array(
                        'label' => $this->module->l('Źródła danych', 'AdminKbchatgptPromptsController'),
                        'detail' => $this->module->l('Oprzyj propozycję na polach {title}, {summary} oraz {description}.', 'AdminKbchatgptPromptsController'),
                    ),
                )
            ),
        );

        $guide = isset($guideList[$promptType]) ? $guideList[$promptType] : array(
            'button' => $this->module->l('Korzystaj z danych produktu', 'AdminKbchatgptPromptsController'),
            'instruction' => $this->module->l('Uzupełnij instrukcję tak, aby wskazywała źródła danych oraz oczekiwany format HTML.', 'AdminKbchatgptPromptsController'),
            'example_html' => '<div class="kb-prompt-guide-placeholder">&lt;p&gt;Dodaj przykładowy kod HTML, który pokaże styl docelowy.&lt;/p&gt;</div>',
            'uses' => array(),
            'steps' => array(
                array(
                    'label' => $this->module->l('Cel', 'AdminKbchatgptPromptsController'),
                    'detail' => $this->module->l('Określ, jak powinien wyglądać wynik i z jakich pól pochodzić.', 'AdminKbchatgptPromptsController'),
                ),
                array(
                    'label' => $this->module->l('Działanie', 'AdminKbchatgptPromptsController'),
                    'detail' => $this->module->l('Dodaj przykładowy kod HTML w tabeli poniżej, aby wskazać strukturę.', 'AdminKbchatgptPromptsController'),
                ),
                array(
                    'label' => $this->module->l('Źródła danych', 'AdminKbchatgptPromptsController'),
                    'detail' => $this->module->l('Wypisz, które pola produktu lub kategorii mają zasilać prompt.', 'AdminKbchatgptPromptsController'),
                ),
            )
        );

        $usesTitle = $this->module->l('Wykorzystuje pola:', 'AdminKbchatgptPromptsController');
        $exampleTitle = $this->module->l('Przykładowy kod HTML do naśladowania', 'AdminKbchatgptPromptsController');
        $howToTitle = $this->module->l('Jak użyć tego wzoru', 'AdminKbchatgptPromptsController');
        $tableColumnLabel = $this->module->l('Sekcja', 'AdminKbchatgptPromptsController');
        $tableColumnContent = $this->module->l('Treść', 'AdminKbchatgptPromptsController');
        $instructionColumn = $this->module->l('Opis', 'AdminKbchatgptPromptsController');
        $actionsTitle = $this->module->l('Czynności i źródła danych', 'AdminKbchatgptPromptsController');

        $usesBadges = '';
        if (!empty($guide['uses'])) {
            $badges = array();
            foreach ($guide['uses'] as $use) {
                $badges[] = '<span class="label label-default kb-prompt-guide-use">' . Tools::safeOutput($use) . '</span>';
            }
            $usesBadges = '<div class="kb-prompt-guide-uses"><span class="kb-prompt-guide-uses-title">' . $usesTitle . '</span>' . implode('', $badges) . '</div>';
        }

        $htmlExample = htmlspecialchars($guide['example_html'], ENT_QUOTES, 'UTF-8');

        $panel  = '<div class="kb-prompt-guide-panel">';
        $panel .= '<div class="kb-prompt-guide-header"><button type="button" class="btn btn-info kb-prompt-guide-badge">' . $guide['button'] . '</button></div>';
        $panel .= '<p class="kb-prompt-guide-text">' . $guide['instruction'] . '</p>';
        $panel .= $usesBadges;
        $panel .= '<div class="kb-prompt-guide-table-block">';
        $panel .= '<p class="kb-prompt-guide-table-title">' . $exampleTitle . '</p>';
        $panel .= '<table class="table kb-prompt-guide-table">';
        $panel .= '<thead><tr><th>' . $tableColumnLabel . '</th><th>' . $tableColumnContent . '</th></tr></thead>';
        $panel .= '<tbody>';
        $panel .= '<tr><td class="kb-prompt-guide-table-label">' . $this->module->l('Wzór HTML', 'AdminKbchatgptPromptsController') . '</td><td><pre><code>' . $htmlExample . '</code></pre></td></tr>';
        $panel .= '</tbody></table>';
        $panel .= '</div>';

        $panel .= '<div class="kb-prompt-guide-table-block">';
        $panel .= '<p class="kb-prompt-guide-table-title">' . $actionsTitle . '</p>';
        $panel .= '<table class="table kb-prompt-guide-table">';
        $panel .= '<thead><tr><th>' . $howToTitle . '</th><th>' . $instructionColumn . '</th></tr></thead>';
        $panel .= '<tbody>';

        if (!empty($guide['steps'])) {
            foreach ($guide['steps'] as $index => $step) {
                $panel .= '<tr>';
                $panel .= '<td class="kb-prompt-guide-step">'
                    . '<span class="kb-prompt-guide-step-count">' . sprintf($this->module->l('Krok %d', 'AdminKbchatgptPromptsController'), (int) ($index + 1)) . '</span>'
                    . '<span class="kb-prompt-guide-step-label">' . Tools::safeOutput($step['label']) . '</span>'
                    . '</td>';
                $panel .= '<td>' . $step['detail'] . '</td>';
                $panel .= '</tr>';
            }
        }

        if (!empty($guide['uses'])) {
            $panel .= '<tr>';
            $panel .= '<td class="kb-prompt-guide-step">' . $this->module->l('Tokeny do podmiany', 'AdminKbchatgptPromptsController') . '</td>';
            $panel .= '<td>' . $usesBadges . '</td>';
            $panel .= '</tr>';
        }

        $panel .= '</tbody></table>';
        $panel .= '</div>';
        $panel .= '</div>';

        return $panel;
    }

    public function initToolbar() {
        // Call the parent method to initialize toolbar buttons first
        parent::initToolbar();
        
        // Now remove the "Add" button
        unset($this->toolbar_btn['new']);
    }

    /**
     * Below function is used to process the update action of the Prompt
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function processUpdate()
    {
        if (Tools::isSubmit('prompt_id')) {
            $kbUpdateField = new kbchatgptPrompts((int)Tools::getValue('prompt_id'));
            $sticker_name = Tools::getValue('prompt_content');
            if($sticker_name == '' || empty($sticker_name)) {
                $errors[] = $this->module->l('Prompt Content not Provided', 'AdminKbchatgptPrompts');
            }
            
            $kbUpdateField->prompt_content = $sticker_name;
            if(!empty($errors)) {
                $this->context->cookie->__set(
                    'kb_redirect_error',
                    $this->module->l('Prompt Content not Provided', 'AdminKbchatgptPrompts')
                );
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbchatgptPrompts', true));
            } else {
                if ($kbUpdateField->update()) {
                    $this->context->cookie->__set(
                        'kb_redirect_success',
                        $this->module->l('Prompt Content updated successfully', 'AdminKbchatgptPrompts')
                    );
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbchatgptPrompts', true));
                } else {
                    $this->context->cookie->__set(
                        'kb_redirect_error',
                        $this->module->l('Something went wrong while updating the Prompt Content. Please try again.', 'AdminKbchatgptPrompts')
                    );
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbchatgptPrompts', true));
                }
            }
        }
    }

    /**
     * Below function is used to generate the content of product as per request
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function ajaxProductContent() {
        $action = Tools::getValue('action');
        $prompt_type = '';
        if($action == 'generateProductSummary') {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "chatgpt_prompts WHERE prompt_type = 'Generate Product Summary'";
            $prompt_type = 'Generate Product Summary';
        } else if($action == 'generateProductDescription') {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "chatgpt_prompts WHERE prompt_type = 'Generate Product Description'";
            $prompt_type = 'Generate Product Description';
        } else if ($action == 'generateProductTitle') {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "chatgpt_prompts WHERE prompt_type = 'Generate Product Title'";
            $prompt_type = 'Generate Product Title';
        }
        if (!empty($prompt_type)) {
            $result = Db::getInstance()->getRow($sql);
            if($result) {
                $this->generateProductContent($result['prompt_content'], $prompt_type);
            }
        }
    }

    /**
     * Below function is used to generate the content of category as per request
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function ajaxCategoryContent() {
        $action = Tools::getValue('action');
        $prompt_type = '';
        if($action == 'generateCategoryDescription') {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "chatgpt_prompts WHERE prompt_type = 'Generate Category Description'";
            $prompt_type = 'Generate Category Description';
        } else if($action == 'generateCategoryMetaTitle') {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "chatgpt_prompts WHERE prompt_type = 'Generate Category Meta Title'";
            $prompt_type = 'Generate Category Meta Title';
        } else if ($action == 'generateCategoryMetaDescription') {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "chatgpt_prompts WHERE prompt_type = 'Generate Category Meta Description'";
            $prompt_type = 'Generate Category Meta Description';
        }
        $result = Db::getInstance()->getRow($sql);
        
        if($result) {
            $this->generateCategoryContent($result['prompt_content'], $prompt_type);
        }
    }

    /**
     * Below function is used to generate the content of product
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param string $origianlText, string $prompt_type
     * @return void
     */
    private function generateProductContent($origianlText, $prompt_type)
    {
        $apiUrl = 'https://api.openai.com/v1/chat/completions';

        $products = Tools::getValue('products');

        $module_settings = Configuration::get('KBChat_MODULE_CONFIGURATIONS');
        $module_settings = json_decode($module_settings, true);
        $iso = Language::getIsoById($module_settings['default_language']);

        foreach($products as $product) {
            $productData = new Product((int) $product);

            $currentContent = $this->getProductCurrentContent((int) $product, (int) $module_settings['default_language']);

            $summary = isset($currentContent['description_short']) ? $currentContent['description_short'] : '';
            $description = isset($currentContent['description']) ? $currentContent['description'] : '';
            $title = isset($currentContent['name']) ? $currentContent['name'] : '';

            $text = $origianlText;
            $text = str_replace("{summary}", $summary, $text);
            $text = str_replace("{description}", $description, $text);
            $text = str_replace("{title}", $title, $text);

            $postData = json_encode([
                'model' => $module_settings['content_engine'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an e-commerce content generator specialized in creating SEO-optimized content for products and categories in a PrestaShop store.'],
                    ['role' => 'user', 'content' => $text . " in the language having iso code " . $iso ]
                ],
                'max_tokens' => isset($module_settings['chatgpt_max_token'])?(int)$module_settings['chatgpt_max_token']:500,
                'temperature' => isset($module_settings['chatgpt_temperature'])?(float)$module_settings['chatgpt_temperature']:0.1,
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $module_settings['api_key'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if($responseData) {
                if(isset($responseData['error']) && $responseData['error']['message']) {
                    $sql = "INSERT INTO " . _DB_PREFIX_ . "chatgpt_logs (prompt_type, entity_id, entity_type, entity_lang, prev_content, new_content, error, date_added) VALUES ('" . pSQL($prompt_type) . "', '" . (int) $product . "', 'Product', '', '', '', '" . pSQL($responseData['error']['message']) . "', NOW())";
                    Db::getInstance()->execute($sql);
                    return;
                } else {
                    foreach(Language::getLanguages(false) as $lang) {
                        $prev_content = '';
                        $existingLangContent = $this->getProductCurrentContent((int) $product, (int) $lang['id_lang']);
                        $newContent = $responseData['choices'][0]['message']['content'] ?? '';
                        if (Tools::getValue('action') == 'generateProductSummary') {
                            $prev_content = isset($existingLangContent['description_short']) ? $existingLangContent['description_short'] : '';
                            $productData->description_short[$lang['id_lang']] = $newContent;
                        } else if (Tools::getValue('action') == 'generateProductDescription') {
                            $prev_content = isset($existingLangContent['description']) ? $existingLangContent['description'] : '';
                            $productData->description[$lang['id_lang']] = $newContent;
                        } else if (Tools::getValue('action') == 'generateProductTitle') {
                            $prev_content = isset($existingLangContent['name']) ? $existingLangContent['name'] : '';
                            $newContent = preg_replace('/^"(.+)"$/', '$1', $newContent);
                            $productData->name[$lang['id_lang']] = $newContent;
                        }
                        $sql = "INSERT INTO " . _DB_PREFIX_ . "chatgpt_logs (prompt_type, entity_id, entity_type, entity_lang, prev_content, new_content, error, date_added) VALUES ('" . pSQL($prompt_type) . "', '" . (int) $product . "', 'Product', '" . (int) $lang['id_lang'] . "', '" . pSQL($prev_content, true) . "', '" . pSQL($newContent, true) . "', '', NOW())";

                        Db::getInstance()->execute($sql);
                    }
                    $productData->save();
                }
            }
        }
    }

    /**
     * Fetch the current product content for the given language directly from the database
     * to ensure placeholders are replaced with up-to-date values.
     *
     * @param int $productId
     * @param int $idLang
     *
     * @return array
     */
    private function getProductCurrentContent($productId, $idLang)
    {
        $idShop = (int) (isset($this->context->shop->id) ? $this->context->shop->id : Configuration::get('PS_SHOP_DEFAULT'));

        $query = new DbQuery();
        $query->select('pl.name, pl.description, pl.description_short');
        $query->from('product_lang', 'pl');
        $query->where('pl.id_product = ' . (int) $productId);
        $query->where('pl.id_lang = ' . (int) $idLang);
        $query->where('pl.id_shop = ' . (int) $idShop);

        $content = Db::getInstance()->getRow($query);

        if (!$content) {
            $fallbackQuery = new DbQuery();
            $fallbackQuery->select('pl.name, pl.description, pl.description_short');
            $fallbackQuery->from('product_lang', 'pl');
            $fallbackQuery->where('pl.id_product = ' . (int) $productId);
            $fallbackQuery->where('pl.id_lang = ' . (int) $idLang);
            $content = Db::getInstance()->getRow($fallbackQuery);
        }

        return $content ? $content : array('name' => '', 'description' => '', 'description_short' => '');
    }

    /**
     * Below function is used to generate the content of category
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param string $origianlText, string $prompt_type
     * @return void
     */
    private function generateCategoryContent($origianlText, $prompt_type) {
        $apiUrl = 'https://api.openai.com/v1/chat/completions';

        $categories = Tools::getValue('categories');

        $module_settings = Configuration::get('KBChat_MODULE_CONFIGURATIONS');
        $module_settings = json_decode($module_settings, true);
        $iso = Language::getIsoById($module_settings['default_language']);
        
        foreach($categories as $category) {
            $categoryData = new Category((int) $category);
            
            $text = $origianlText;
            $text = str_replace("{product_name}", '', $text);
            $text = str_replace("{shop_name}", Configuration::get('PS_SHOP_NAME'), $text);
            $text = str_replace("{category}", $categoryData->name[$module_settings['default_language']], $text);

            $postData = json_encode([
                'model' => $module_settings['content_engine'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an e-commerce content generator specialized in creating SEO-optimized content for products and categories in a PrestaShop store.'],
                    /**
                     * Updated the content since iso code was not getting detected correctly.
                     * @modifier Himanshu Vishwakarma
                     * @date 24-03-2025
                     */
                    ['role' => 'user', 'content' => $text . " in the language having iso code ". $iso ]
                ],
                /**
                 * Below code is used to set the max tokens and temperature
                 * @date 30-12-2024
                 * @modifier Amit Singh
                 */
                'max_tokens' => isset($module_settings['chatgpt_max_token'])?(int)$module_settings['chatgpt_max_token']:500,
                'temperature' => isset($module_settings['chatgpt_temperature'])?(float)$module_settings['chatgpt_temperature']:0.1,
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $module_settings['api_key'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);
            
            foreach(Language::getLanguages(false) as $lang) {
                $prev_content = '';
                if($responseData) {
                    if(isset($responseData['error']) && $responseData['error']['message']) {
                        $sql = "INSERT INTO " . _DB_PREFIX_ . "chatgpt_logs (prompt_type, entity_id, entity_type, entity_lang, prev_content, new_content, error, date_added) VALUES ('" . pSQL($prompt_type) . "', '" . (int) $category . "', 'Category', '" . (int) $lang['id_lang'] . "', '', '', '" . pSQL($responseData['error']['message']) . "', NOW())";
                        Db::getInstance()->execute($sql);
                        return;
                    } else {
                        if (Tools::getValue('action') == 'generateCategoryDescription') {
                            $prev_content = $categoryData->description[$lang['id_lang']];
                            $categoryData->description[$lang['id_lang']] = $responseData['choices'][0]['message']['content'] ?? '';
                        } else if (Tools::getValue('action') == 'generateCategoryMetaTitle') {
                            $prev_content = $categoryData->meta_title[$lang['id_lang']];
                            $responseData['choices'][0]['message']['content'] = preg_replace('/^"(.+)"$/', '$1', $responseData['choices'][0]['message']['content']);
                            $categoryData->meta_title[$lang['id_lang']] = $responseData['choices'][0]['message']['content'] ?? '';
                        } else if (Tools::getValue('action') == 'generateCategoryMetaDescription') {
                            $prev_content = $categoryData->meta_description[$lang['id_lang']];
                            $responseData['choices'][0]['message']['content'] = preg_replace('/^"(.+)"$/', '$1', $responseData['choices'][0]['message']['content']);
                            $categoryData->meta_description[$lang['id_lang']] = $responseData['choices'][0]['message']['content'] ?? '';
                        }
                        $sql = "INSERT INTO " . _DB_PREFIX_ . "chatgpt_logs (prompt_type, entity_id, entity_type, entity_lang, prev_content, new_content, error, date_added) VALUES ('" . pSQL($prompt_type) . "', '" . (int) $category . "', 'Category', '" . (int) $lang['id_lang'] . "', '" . pSQL($prev_content, true) . "', '" . pSQL($responseData['choices'][0]['message']['content'], true) . "', '', NOW())";
                        
                        Db::getInstance()->execute($sql);
                    }
                    $categoryData->save();
                }
            }      
        }
    }
}

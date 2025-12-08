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
class AdminKbchatgptLogsController extends ModuleAdminController
{
    protected $kb_module_name = 'kbchatgpt';

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
     * Below function is the constructor function of the class
     * It is used to initialize the class level variables and list the table fields
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     */
    public function __construct()
    {
        $this->name = 'kbchatgpt';
        $this->bootstrap = true;
        $this->allow_export = true;
        $this->context = Context::getContext();
        $this->list_no_link = true;
        $this->all_languages = $this->getAllLanguages();
        $this->table = 'chatgpt_logs';
        $this->identifier = 'log_id';
        $this->lang = false;
        $this->display = 'list';
        parent::__construct();
        
        $this->toolbar_title = $this->module->l('ChatGPT Prompts Task Log', 'AdminKbchatgptLogsController');
        
        $this->addRowAction('rollBack');
        $this->addRowAction('showModal');

        $this->fields_list = array(
            'log_id' => array(
                'title' => $this->module->l('ID', 'AdminKbchatgptLogsController'),
                'search' => true,
                'align' => 'left',
                'class' => 'fixed-width-xs',
                'type' => 'int',
            ),
            'prompt_type' => array(
                'title' => $this->module->l('Prompt Type', 'AdminKbchatgptLogsController'),
                'search' => true,
                'align' => 'left',
                'filter_key' => 'a!prompt_type',
                'type' => 'text',
            ),
            'product_name' => array(
                'title' => $this->module->l('Product', 'AdminKbchatgptLogsController'),
                'search' => true,
                'align' => 'left',
                'filter_key' => 'pl!name',
            ),
            'category_name' => array(
                'title' => $this->module->l('Category', 'AdminKbchatgptLogsController'),
                'search' => true,
                'align' => 'left',
                'filter_key' => 'cl!name',
            ),
            // 'entity_type' => array(
            //     'title' => $this->module->l('Product/Category Name', 'AdminKbchatgptLogsController'),
            //     'search' => true,
            //     'align' => 'left',
            //     'filter_key' => 'a!entity_type',
            //     'callback' => 'getEntityName',
            // ),
            'entity_lang' => array(
                'title' => $this->module->l('Language', 'AdminKbchatgptLogsController'),
                'search' => false,
                'align' => 'left',
                'filter_key' => 'entity_lang',
                'callback' => 'getLanguageName',
            ),
            'prev_content' => array(
                'title' => $this->module->l('Previous Content', 'AdminKbchatgptLogsController'),
                'search' => true,
                'align' => 'left',
                'filter_key' => 'prev_content',
                'maxlength' => 150,
            ),
            'new_content' => array(
                'title' => $this->module->l('New Content', 'AdminKbchatgptLogsController'),
                'search' => true,
                'align' => 'left',
                'filter_key' => 'new_content',
                'maxlength' => 150,
            ),
            // 'error' => array(
            //     'title' => $this->module->l('Error', 'AdminKbchatgptLogsController'),
            //     'search' => false,
            //     'align' => 'left',
            //     'filter_key' => 'error',
            //     'maxlength' => 150,
            // ),
            'date_added' => array(
                'title' => $this->module->l('Date', 'AdminKbchatgptLogsController'),
                'havingFilter' => false,
                'search' => false,
                'align' => 'left',
                'orderby' => false,
                'type' => 'date',
            ),
        );

        $this->_select = 'a.*, pl.name as product_name, cl.name as category_name';
        
        $this->_join = ' LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (a.entity_id = pl.id_product AND a.entity_type = "Product" AND pl.id_lang = '. (int) $this->context->language->id .') LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (a.entity_id = cl.id_category AND a.entity_type = "Category" AND cl.id_lang = '. (int) $this->context->language->id .')';

        /**
         * Add the group by clause to avoid duplicate records
         * @modifier Himanshu Vishwakarma
         * @date 27-03-2025
         */
        $this->_group = 'GROUP BY a.log_id';

        $this->_orderBy = 'log_id';
        $this->_orderWay = 'DESC';
        
    }

    /**
     * Below function is used to display the rollback link
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param string $token, int $id, string $name
     * @return string
     */
    public function displayRollBackLink($token, $id, $name = null)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'chatgpt_logs WHERE log_id = ' . (int)$id;
        $tr = Db::getInstance()->getRow($sql);
        
        $url = '#';
        if(!empty($tr)) {
	        /**
             * If there is an error in the log, then show the error message in the modal
             * Else show the error button
             * @date 30-12-2024
             * @modifier Amit Singh
             */
            if(!empty($tr['error'])) {
                $this->context->smarty->assign([
                    'show_modal_url' => '#',
                    'show_modal_title' => $this->module->l('Show Error', 'AdminKbchatgptLogsController'),
                    'error_msg' => $tr['error'],
                    'log_id' => (int)$id,
                ]);
                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbchatgpt/views/templates/admin/show_error_modal_button.tpl');
            }else{
                $url = self::$currentIndex . '&log_id=' . (int)$id . '&rollBack=1&token=' . $token . '&entity_type=' . pSQL($tr['entity_type']);
                $this->context->smarty->assign([
                    'rollback_url' => $url,
                    'rollback_title' => $this->module->l('Rollback', 'AdminKbchatgptLogsController'),
                ]);
                if(!empty($tr['error'])) {
                    return '';
                }
        
                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbchatgpt/views/templates/admin/rollback_button.tpl');

            }
            
        }
        return '';
        
    }

    public function displayShowModalLink($token, $id, $name = null)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'chatgpt_logs WHERE log_id = ' . (int)$id;
        $tr = Db::getInstance()->getRow($sql);
        
        $url = '#';
        
        if (!empty($tr)) {
            if(empty($tr['error'])) {
                $url = self::$currentIndex . '&ajax=1&action=loadModalContent&log_id=' . (int)$id . '&token=' . $token;
                $this->context->smarty->assign([
                    'show_modal_url' => $url,
                    'show_modal_title' => $this->module->l('Display Content', 'AdminKbchatgptLogsController'),
                    'log_id' => (int)$id,
                ]);
                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbchatgpt/views/templates/admin/show_modal_button.tpl');
            }
        }
        return '';
        
    }

    /**
     * Below function is used to get the entity name either Product or Category
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param array $row_data, array $tr
     * @return string
     */
    // public function getEntityName($row_data, $tr)
    // {
    //     if($tr['entity_type'] == 'Product') {
    //         $product = new Product((int)$tr['entity_id']);
    //         return $product->name[$this->context->language->id];
    //     } elseif($tr['entity_type'] == 'Category') {
    //         $category = new Category((int)$tr['entity_id']);
    //         return $category->name[$this->context->language->id];
    //     }
    // }
    
    /**
     * Below function is used to assign the tab links to the tpl
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function initContent()
    {
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

        if (isset($this->context->cookie->kb_redirect_success)) {
            $this->confirmations[] = $this->context->cookie->kb_redirect_success;
            unset($this->context->cookie->kb_redirect_success);
        }
        if (isset($this->context->cookie->kb_redirect_error)) {
            $this->confirmations[] = $this->context->cookie->kb_redirect_error;
            unset($this->context->cookie->kb_redirect_error);
        }
        
        $this->context->smarty->assign('selected_nav', 'kbpb_logs');
        
        $this->context->smarty->assign('kb_admin_link', $this->context->link->getAdminLink('AdminKbchatgptPrompts', true).'&configure='. $this->module->name.'&ajaxproductaction=true');
        $kb_tabs = '';
        $kb_tabs = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/kb_tabs.tpl'
        );

        $this->content .= $kb_tabs;
        
        parent::initContent();
    }

    /**
     * Below function is used to initialize the toolbar and remove the add button
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @return void
     */
    public function initToolbar() {
        // Call the parent method to initialize toolbar buttons first
        parent::initToolbar();
        
        // Now remove the "Add" button
        unset($this->toolbar_btn['new']);
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
     * Below function is used to get the language name
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param int $id_lang
     * @return string
     */
    public function getLanguageName($id_lang)
    {
        $lang = new Language($id_lang);
        return $lang->name;
    }

    /**
     * Below function is used to execute the rollBack operation
     */
    public function postProcess()
    {
        if(Tools::isSubmit('rollBack')) {
            $this->performRollback((int)Tools::getValue('log_id'));
        }
        parent::postProcess();
    }

    /**
     * Below function is used to perform the rollback operation
     * @date 09-11-2024
     * @modifier Nikhil Aggarwal
     * @param int $task_product_id
     * @return void
     */
    protected function performRollback($task_product_id)
    {
        $rollbackData = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'chatgpt_logs WHERE log_id = '.(int)$task_product_id
        );

        if ($rollbackData && $rollbackData['entity_type'] == 'Product') {
            if($rollbackData['prompt_type'] == 'Translate Product Title') {
                $column_name = 'name';
            } else if($rollbackData['prompt_type'] == 'Translate Product Description' || $rollbackData['prompt_type'] == 'Generate Product Description') {
                $column_name = 'description';
            } else if($rollbackData['prompt_type'] == 'Translate Product Meta Title' || $rollbackData['prompt_type'] == 'Generate Product Meta Title') {
                $column_name = 'meta_title';
            } else if($rollbackData['prompt_type'] == 'Translate Product Meta Description' || $rollbackData['prompt_type'] == 'Generate Product Meta Description') {
                $column_name = 'meta_description';
            }
            // Update the product content with `prev_content`
            Db::getInstance()->update('product_lang', [
                $column_name => pSQL($rollbackData['prev_content'], true),
            ], 'id_product = '.(int)$rollbackData['entity_id'].' AND id_lang = '.(int)$rollbackData['entity_lang']);

            // Optionally, you can add a success message
            $this->context->cookie->__set(
                'kb_redirect_success',
                $this->module->l('Product content has been rolled back successfully.', 'AdminKbchatgptLogsController')
            );
        } else if ($rollbackData && $rollbackData['entity_type'] == 'Category') {
            if($rollbackData['prompt_type'] == 'Translate Category Title') {
                $column_name = 'name';
            } else if($rollbackData['prompt_type'] == 'Translate Category Description' || $rollbackData['prompt_type'] == 'Generate Category Description') {
                $column_name = 'description';
            } else if($rollbackData['prompt_type'] == 'Translate Category Meta Title' || $rollbackData['prompt_type'] == 'Generate Category Meta Title') {
                $column_name = 'meta_title';
            } else if($rollbackData['prompt_type'] == 'Translate Category Meta Description' || $rollbackData['prompt_type'] == 'Generate Category Meta Description') {
                $column_name = 'meta_description';
            }
            // Update the product content with `prev_content`
            Db::getInstance()->update('category_lang', [
                $column_name => pSQL($rollbackData['prev_content'], true),
            ], 'id_category = '.(int)$rollbackData['entity_id'].' AND id_lang = '.(int)$rollbackData['entity_lang']);
            $this->context->cookie->__set(
                'kb_redirect_success',
                $this->module->l('Category content has been rolled back successfully.', 'AdminKbchatgptLogsController')
            );
        } else {
            // If no data found, add an error message
            $this->context->cookie->__set(
                'kb_redirect_error',
                $this->module->l('Rollback failed. No previous content found.', 'AdminKbchatgptLogsController')
            );
            $this->errors[] = $this->module->l('Rollback failed. No previous content found.', 'AdminKbchatgptLogsController');
        }
        $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
        // Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbchatgptLogs', true));
    }

    public function ajaxProcessLoadModalContent()
    {
        $log_id = (int)Tools::getValue('log_id');
        $response = ['success' => false];

        if ($log_id) {
            // Query to get the prev and new content
            $sql = 'SELECT prev_content, new_content FROM ' . _DB_PREFIX_ . 'chatgpt_logs WHERE log_id = ' .(int) $log_id;
            $content = Db::getInstance()->getRow($sql);

            if ($content) {
                $response = [
                    'success' => true,
                    'prev_content' => $content['prev_content'],
                    'new_content' => $content['new_content'],
                ];
            }
        }

        // Return JSON response
        die(json_encode($response));
    }

    /**
     * Processes and exports the chatgpt logs as a CSV file.
     * This function retrieves the log data, sets the appropriate headers,
     * and writes the content to a CSV file with UTF-8 encoding to support special characters.
     * @modifier Himanshu Vishwakarma
     * @date 27-03-2025
     */
    public function processExport($text_delimiter = '"')
    {
        $this->getList($this->context->language->id);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="chatgpt_logs.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for correct encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Define column headers
        fputcsv($output, [
            'ID', 'Prompt Type', 'Product', 'Category', 'Language', 'Previous Content', 'New Content', 'Date'
        ], ',', $text_delimiter);

        // Fetch data
        foreach ($this->_list as $row) {
            fputcsv($output, [
                $row['log_id'],
                $row['prompt_type'],
                $row['product_name'],
                $row['category_name'],
                $this->getLanguageName($row['entity_lang']),
                $row['prev_content'],
                $row['new_content'],
                $row['date_added'],
            ], ',', $text_delimiter);
        }

        fclose($output);
        exit;
    }

    /**
     * Renders the CSV export view.
     * This function is typically used to display an interface
     * where the admin can trigger the export of logs in CSV format.
     * @modifier Himanshu Vishwakarma
     * @date 27-03-2025
     */
    public function renderCSV()
    {
        return $this->processExport();
    }

}
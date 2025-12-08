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
class kbchatgptPrompts extends ObjectModel
{
    public $prompt_id;
    public $prompt_type;
    public $prompt_content;
    
    const TABLE_NAME = 'chatgpt_prompts';

    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'prompt_id',
        'fields' => array(
            'prompt_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'prompt_type' => array(
                'type' => self::TYPE_STRING,
            ),
            'prompt_content' => array(
                'type' => self::TYPE_STRING,
            ),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }
}

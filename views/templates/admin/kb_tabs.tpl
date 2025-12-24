<script>
    var path_fold = "{$kb_admin_link}";  {*Variable contains link, escape not required*}
    $(document).ready(function() {
        $(document).on('click', '[name="submit_button"]', function() {
            // event.preventDefault();
            $('.kb_error').remove();
            error = 0;
            
            let api_val = $('#api_key').val();
            let api_val_trim = api_val.trim();
            if(api_val_trim == ''){
                $('#api_key').after('<span class="kb_error" style="color:red;">{l s='Please enter the API Key' mod='kbchatgpt'}</span>');
                error = 1;
            }
	        /**
            * @desc This is the validation for the maximum token field
            * @date 30-12-2024
            * @modifier Amit Singh
            */
            let chatgpt_max_token_val = $('#chatgpt_max_token').val();
            let chatgpt_max_token_val_trim = chatgpt_max_token_val.trim();
            if(chatgpt_max_token_val_trim == ''){
                $('#chatgpt_max_token').after('<span class="kb_error" style="color:red;">{l s='Maximum Token Field Can not be empty.' mod='kbchatgpt'}</span>');
                error = 1;
            } else if (isNaN(chatgpt_max_token_val_trim)){
                $('#chatgpt_max_token').after('<span class="kb_error" style="color:red;">{l s="The value must be numeric." mod="kbchatgpt"}</span>');
                error = 1;
            } else if(chatgpt_max_token_val_trim < 0 || chatgpt_max_token_val_trim > 500){
                $('#chatgpt_max_token').after('<span class="kb_error" style="color:red;">{l s='Value should be less then 500' mod='kbchatgpt'}</span>');
                error = 1;
            }
            /**
            * @desc This is the validation for the temperature field
            * @date 30-12-2024
            * @modifier Amit Singh
            */
            let chatgpt_temperature_val = $('#chatgpt_temperature').val();
            let chatgpt_temperature_val_trim = chatgpt_temperature_val.trim();
            if(chatgpt_temperature_val_trim == ''){
                $('#chatgpt_temperature').after('<span class="kb_error" style="color:red;">{l s='Temperature Field Can not be empty.' mod='kbchatgpt'}</span>');
                error = 1;
            } else if(isNaN(chatgpt_temperature_val_trim)) {
                $('#chatgpt_temperature').after('<span class="kb_error" style="color:red;">{l s="The value must be numeric." mod="kbchatgpt"}</span>');
                error = 1;
            } else if (chatgpt_temperature_val_trim < 0 || chatgpt_temperature_val_trim > 2) {
                $('#chatgpt_temperature').after('<span class="kb_error" style="color:red;">{l s="Value should be between 0-2" mod="kbchatgpt"}</span>');
                error = 1;
            }

            if(error == 0) {
                return true;
            } else {
                return false;
            }
        });
    });
</script>
<div class="kb_custom_tabs kb_custom_panel">
    <span>
        <a class="kb_custom_tab {if $selected_nav == 'config'}kb_active{/if}" title="{l s='Product Badge General Settings' mod='kbchatgpt'}" id="kbsl_config_link" href="{$admin_pb_configure_controller}">{*Variable contains URL content, escape not required*}
            {l s='General Settings' mod='kbchatgpt'}
        </a>
    </span>

    <span>
        <a class="kb_custom_tab {if $selected_nav == 'kbpb_rule_config'}kb_active{/if}" title="{l s='ChatGPT Prompts' mod='kbchatgpt'}" id="kbsl_gdpr_config" href="{$admin_pb_prompts}">{*Variable contains URL content, escape not required*}
            {l s='ChatGPT Prompts' mod='kbchatgpt'}
        </a>
    </span>
    <span>
        <a class="kb_custom_tab {if $selected_nav == 'kbpb_logs'}kb_active{/if}" title="{l s='Task Log' mod='kbchatgpt'}" id="kbsl_sticker" href="{$admin_pb_logs}">{*Variable contains URL content, escape not required*}
            {l s='Tasks Log' mod='kbchatgpt'}
        </a>
    </span>
</div>
        
{*
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 *}

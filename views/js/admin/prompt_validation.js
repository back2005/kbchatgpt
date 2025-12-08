/**
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
 */
$(document).ready(function() {
    // Add validation on button click of #chatgpt_prompts_form_submit_btn
    $('#chatgpt_prompts_form_submit_btn').click(function(event) {
        // Prevent default form submission
        event.preventDefault();
        $('.kb_error').remove();
        error = 0;
        // Get the value of the input field
        let prompt = $('#prompt_content').val();

        // If the prompt is empty, show an error message
        if (!prompt) {
            var alert_notify = '<div class="kb_error" style="color:red;">';
            alert_notify = alert_notify + error_blank +'</div>';
            $('#prompt_content').parent().append(alert_notify);
            $("#prompt_content").closest('.form-group').show();
            error = 1;
        }

        if(error == 1){
            return false;
        }

        // If the prompt is not empty, submit the form
        $('#kbcf_edit_prompt').submit();
    });
});
// End of prompt_validation.js
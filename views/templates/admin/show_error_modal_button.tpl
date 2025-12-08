<a href="#" class="btn btn-success kbshow-rollBackModal-btn" data-log-id="{$log_id}" title="{$show_modal_title|escape:'html':'UTF-8'}" onclick="kbshowErrorMdal($(this))"> {* variable contains URL, escaping not Required*}
    {$show_modal_title} {* variable contains dynamic content, escaping not Required*}
</a>

<div id="kbshowErrorModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{l s='ChatGPT Error' mod='kbchatgpt'}</h2>
            </div>
            <div class="modal-body">
                <div id="errorMSG" class="content-box"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{l s='Close' mod='kbchatgpt'}</button>
            </div>
        </div>
    </div>
</div>

<style>
.content-box {
    max-height: 400px;
    max-width: 400px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
}
</style>

<script type="text/javascript">
    function kbshowErrorMdal(element) {
        var modal = $('#kbshowErrorModal');
        var errormessagecontent = "{$error_msg}";
        $('#errorMSG').html(errormessagecontent);
        modal.modal('show');
        return false;
    }
    $(document).ready(function() {
        $('#header').append($('#kbshowErrorModal'));
    });

</script>
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
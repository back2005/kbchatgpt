
<a href="#" class="btn btn-success kbshow-rollBackModal-btn" data-log-id="{$log_id}" title="{$show_modal_title|escape:'html':'UTF-8'}" onclick="kbshowMdal($(this))"> {* variable contains URL, escaping not Required*}
    {$show_modal_title} {* variable contains dynamic content, escaping not Required*}
</a>


<div id="kbshowModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{l s='Previous and New Content' mod='kbchatgpt'}</h2>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Previous Content Column -->
                        <div class="col-md-6">
                            <h4>{l s='Previous Content:' mod='kbchatgpt'}</h4>
                            <div id="prevContent" class="content-box"></div>
                        </div>
                        <!-- New Content Column -->
                        <div class="col-md-6">
                            <h4>{l s='New Content:' mod='kbchatgpt'}</h4>
                            <div id="newContent" class="content-box"></div>
                        </div>
                    </div>
                </div>    
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
    function kbshowMdal(element) {
        // Get the log ID
        var logId = element.data('log-id');
        var modal = $('#kbshowModal');
        var request_url = '{$show_modal_url}';{* variable contains URL, escaping not Required*}
        // AJAX request to fetch the content
        $.ajax({
            url: request_url + '&log_id=' + logId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Populate the modal with content
                    $('#prevContent').html(response.prev_content);
                    $('#newContent').html(response.new_content);

                    // Show the modal
                    modal.modal('show');
                } else {
                    alert('Failed to load content.');
                }
            },
            error: function() {
                alert('An error occurred while loading content.');
            }
        });
        return false;
    }
    $(document).ready(function() {
        $('#header').append($('#kbshowModal'));
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
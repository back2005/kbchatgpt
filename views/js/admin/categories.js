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
function kbTranslator() {
    if (window.jQuery) {
        $(function () {
            let kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateCategoryMetaDescription\');">\n' + textgenerateCategoryMetaDescription +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateCategoryMetaTitle\');">\n' + textgenerateCategoryMetaTitle +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateCategoryDescription\');">\n' + textgenerateCategoryDescription +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
        });
    } else {
        // If jQuery is not loaded, wait for 100ms and try again
        setTimeout(kbTranslator, 100);
    }
}

function kbContent(element, action) {
    /**
     * Check if the GPT Demo is enabled or not
     * @date 30-12-2024
     * @modifier Amit Singh
     */
    if(kbchatgpt){
        $.growl.notice({message: textdemocontentEngine});
        return false;
    }
    let form = $('#category_filter_form');

    let items = $('input:checked[name="category_id_category[]"]', form);

    let Categorys = [];
    for(let i = 0; i < items.length; i++) {
        Categorys.push(items[i].value);
    }

    $.growl.notice({message: textcontentEngine});

    $.ajax({
        url: ajaxUrl + "&action=" + action + "&kbentity=" + kbentity,
        method: 'POST',
        data: {
            categories: Categorys,
        },
        error: function (response) {
            $.growl.error({message: textError});
        }
    });
    return false;
}

kbTranslator();

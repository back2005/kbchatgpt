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
            let kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'translateProductMetaDescription\');">\n' + texttranslateProductMetaDescription +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'translateProductMetaTitle\');">\n' + texttranslateProductMetaTitle +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'translateProductDescription\');">\n' + texttranslateProductDescription +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'translateProductTitle\');">\n' + texttranslateProductTitle +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateProductMetaDescription\');">\n' + textgenerateProductMetaDescription +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateProductMetaTitle\');">\n' + textgenerateProductMetaTitle +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);

            // kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateProductReviews\');">\n' + textgenerateProductReviews +
            //     '\n' +
            //     '</a>';
            // $('.js-bulk-actions-btn').next().prepend(kbelement);

            kbelement = '<a class="dropdown-item" href="#" onclick="kbContent(this, \'generateProductDescription\');">\n' + textgenerateProductDescription +
                '\n' +
                '</a>';
            $('.js-bulk-actions-btn').next().prepend(kbelement);
            $('#product_bulk_menu').next().prepend(kbelement);
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
    let form = $('#product_catalog_list');

    if (form.length === 0) {
        form = $('#product_filter_form');
    }

    let items = $('input:checked[name="bulk_action_selected_products[]"]', form);

    if (items.length === 0) {
        items = $('input:checked[name="product_bulk[]"]', form);
        if (items.length === 0) {
            return false;
        }
    }

    let products = [];
    for(let i = 0; i < items.length; i++) {
        products.push(items[i].value);
    }

    $.growl.notice({message: textcontentEngine});

    $.ajax({
        url: ajaxUrl + "&action=" + action + "&kbentity=" + kbentity,
        method: 'POST',
        data: {
            products: products,
        },
        error: function (response) {
            $.growl.error({message: textError});
        }
    });
    return false;
}

kbTranslator();

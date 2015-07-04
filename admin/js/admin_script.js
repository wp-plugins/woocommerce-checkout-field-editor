jQuery(document).ready(function () {        
    
    if (localStorage.getItem("lasttab") == null) {
        jQuery(".billing-semi,.shipping-semi,.shipping_edit_field,.billing_edit_field").hide();
        jQuery(".additional-semi").show();
    }        
    if (localStorage.getItem("lasttab") == 'billing-tab') {  
        jQuery(".billing-semi,.billing_edit_field").show();
        jQuery(".shipping-semi,.additional-semi,.shipping_edit_field").hide();
        jQuery(".shipping-tab,.additional-tab").removeClass('nav-tab-active');
        jQuery('.billing-tab').addClass('nav-tab-active');
    }
    
    if (localStorage.getItem("lasttab") == 'shipping-tab') {
        jQuery(".shipping-semi,.shipping_edit_field").show();
        jQuery(".billing-semi,.additional-semi,.billing_edit_field").hide();
        jQuery(".billing-tab,.additional-tab").removeClass('nav-tab-active');
        jQuery('.shipping-tab').addClass('nav-tab-active');
    }    
    if (localStorage.getItem("lasttab") == 'addition-tab') {
        jQuery(".additional-semi").show();
        jQuery(".billing-semi,.shipping-semi,.shipping_edit_field,.billing_edit_field").hide();
        jQuery(".billing-tab,.shipping-tab").removeClass('nav-tab-active');
        jQuery('.addition-tab').addClass('nav-tab-active');
    }    
    
    
    jQuery(".billing-tab").click(function () {
        jQuery(".shipping-tab,.additional-tab").removeClass('nav-tab-active');
        jQuery(this).addClass('nav-tab-active');
        if (typeof (Storage) != "undefined") {
            // Store
            localStorage.setItem("lasttab", "billing-tab");                        
        }
        jQuery(".shipping-semi,.additional-semi,.shipping_edit_field").hide();
        jQuery(".billing-semi,.billing_edit_field").show();
    });

    jQuery(".shipping-tab").click(function () {
        jQuery(".billing-tab,.additional-tab").removeClass('nav-tab-active');
        jQuery(this).addClass('nav-tab-active');
        if (typeof (Storage) != "undefined") {
            // Store
            localStorage.setItem("lasttab", "shipping-tab");                        
        }
        jQuery(".billing-semi,.additional-semi,.billing_edit_field").hide();
        jQuery(".shipping-semi,.shipping_edit_field").show();

    });

    jQuery(".additional-tab").click(function () {
        jQuery(".billing-tab,.shipping-tab").removeClass('nav-tab-active');
        jQuery(this).addClass('nav-tab-active');
        if (typeof (Storage) != "undefined") {
            // Store
            localStorage.setItem("lasttab", "addition-tab");
        }
        jQuery(".billing-semi,.shipping-semi,.shipping_edit_field,.billing_edit_field").hide();
        jQuery(".additional-semi").show();

    });

});


jQuery(document).ready(function () {
    jQuery(function () {
        jQuery('#select_all_rm').click(function () {
            var c = this.checked;
            jQuery('.rm').prop('checked', c);
        });
    });
});
jQuery(document).ready(function () {
    jQuery(function () {
        jQuery('#select_all_rq').click(function () {
            var c = this.checked;
            jQuery('.rq').prop('checked', c);
        });
    });
});

jQuery(document).ready(function () {
    jQuery(function () {
        jQuery('#select_all_rm_s').click(function () {
            var c = this.checked;
            jQuery('.rm_s').prop('checked', c);
        });
    });
});
jQuery(document).ready(function () {
    jQuery(function () {
        jQuery('#select_all_rq_s').click(function () {
            var c = this.checked;
            jQuery('.rq_s').prop('checked', c);
        });
    });
});
// Javascript for adding new field
jQuery(document).ready(function () {
    /**
     * Credits to the Advanced Custom Fields plugin for this code
     */
    // Update Order Numbers
    function update_order_numbers(div) {
        div.children('tbody').children('tr.wcfe-row').each(function (i) {
            jQuery(this).children('td.wcfe-order').html(i + 1);
        });
    }
    // Make Sortable
    function make_sortable(div) {
        var fixHelper = function (e, ui) {
            ui.children().each(function () {
                jQuery(this).width(jQuery(this).width());
            });
            return ui;
        };
        div.children('tbody').unbind('sortable').sortable({
            update: function (event, ui) {
                update_order_numbers(div);
            },
            handle: 'td.wcfe-order',
            helper: fixHelper
        });
    }
    var div = jQuery('.wcfe-table'),
            row_count = div.children('tbody').children('tr.wcfe-row').length;
    // Make the table sortable
    make_sortable(div);
    // Add button
    jQuery('#wcfe-additional-field').live('click', function () {
        var div = jQuery('.additional_edit_field_table'),
                row_count = div.children('tbody').children('tr.wcfe-row').length,
                new_field = div.children('tbody').children('tr.wcfe_clone').clone(false);
        // Create and add the new field
        new_field.attr('class', 'wcfe-row');
        // Update names
        new_field.find('[name]').each(function () {
            var count = parseInt(row_count);
            var name = jQuery(this).attr('name').replace('[999]', '[' + count + ']');
            jQuery(this).attr('name', name);
        });
        // Add row
        div.children('tbody').append(new_field);
        update_order_numbers(div);
        // There is now 1 more row
        row_count++;
        return false;
    });

    jQuery('#wcfe-billing-field').live('click', function () {
        var div = jQuery('.billing_edit_field_table'),
                row_count = div.children('tbody').children('tr.wcfe-row').length,
                new_field = div.children('tbody').children('tr.wcfe_clone').clone(false); // Create and add the new field
        new_field.attr('class', 'wcfe-row');
        // Update names
        new_field.find('[name]').each(function () {
            var count = parseInt(row_count);
            var name = jQuery(this).attr('name').replace('[999]', '[' + count + ']');
            jQuery(this).attr('name', name);
        });
        // Add row
        div.children('tbody').append(new_field);
        update_order_numbers(div);
        // There is now 1 more row
        row_count++;
        return false;
    });

    jQuery('select.wcfe_select_type').each(function () {
        if (jQuery(this).val() == 'select' || jQuery(this).val() == 'radio' || jQuery(this).val() == 'multiselect') {
            jQuery(this).parent('td').siblings('td').children(".wcfe_attributes_input").removeAttr("disabled");
        } else {
            jQuery(this).parent('td').siblings('td').children(".wcfe_attributes_input").attr("disabled", "disabled");
        }
    });

    jQuery('select.wcfe_select_type').live('change', function () {
        if (jQuery(this).val() == 'select' || jQuery(this).val() == 'radio' || jQuery(this).val() == 'multiselect') {
            jQuery(this).parent('td').siblings('td').children(".wcfe_attributes_input").removeAttr("disabled");
        } else {
            jQuery(this).parent('td').siblings('td').children(".wcfe_attributes_input").attr("disabled", "disabled");
        }
    });

    jQuery('#wcfe-shipping-field').live('click', function () {
        var div = jQuery('.shipping_edit_field_table');
        row_count = div.children('tbody').children('tr.wcfe-row').length;
        new_field = div.children('tbody').children('tr.wcfe_clone').clone(false); // Create and add the new field
        new_field.attr('class', 'wcfe-row');
        // Update names
        new_field.find('[name]').each(function () {
            var count = parseInt(row_count);
            var name = jQuery(this).attr('name').replace('[999]', '[' + count + ']');
            jQuery(this).attr('name', name);
        });
        // Add row
        div.children('tbody').append(new_field);
        update_order_numbers(div);
        // There is now 1 more row
        row_count++;
        return false;
    });


    // Remove button additional
    jQuery('.additional_edit_field_table .wcfe-remove-button').live('click', function () {
        var div = jQuery('.additional_edit_field_table'),
                tr = jQuery(this).closest('tr');
        tr.animate({'left': '50px', 'opacity': 0}, 250, function () {
            tr.remove();
            update_order_numbers(div);
        });
        return false;
    });
    // Remove button billing
    jQuery('.billing_edit_field_table .wcfe-remove-button').live('click', function () {
        var div = jQuery('.billing_edit_field_table'),
                tr = jQuery(this).closest('tr');
        tr.animate({'left': '50px', 'opacity': 0}, 250, function () {
            tr.remove();
            update_order_numbers(div);
        });
        return false;
    });
    // Remove button shippings
    jQuery('.shipping_edit_field_table .wcfe-remove-button').live('click', function () {
        var div = jQuery('.shipping_edit_field_table'),
                tr = jQuery(this).closest('tr');
        tr.animate({'left': '50px', 'opacity': 0}, 250, function () {
            tr.remove();
            update_order_numbers(div);
        });
        return false;
    });
});
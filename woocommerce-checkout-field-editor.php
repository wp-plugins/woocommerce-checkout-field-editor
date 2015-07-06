<?php
/*
  Plugin Name: WooCommerce Checkout field editor
  Plugin URI:http://www.solwininfotech.com/
  Description: Checkout page billing and shipping filed editor
  Version: 3.7
  Author: Solwin infotech
  Author URI: http://www.solwininfotech.com/
  License: GPLv2 or later
 */

if (!defined('ABSPATH'))
    die();

register_activation_hook(__FILE__, 'wcfe_install');
add_filter('wp_feed_cache_transient_lifetime', create_function('$a', 'return 1800;'));
add_filter('wcdn_order_info_fields', 'wcfe_woocommerce_delivery_notes_compat', 10, 2);
add_filter('woocommerce_shipping_fields', 'rs_custom_shipping_fields');
add_filter('woocommerce_billing_fields', 'rs_custom_billing_fields');
add_action('admin_enqueue_scripts', 'wcfe_scripts');
add_action('woocommerce_after_checkout_billing_form', 'wcfe_add_title');
add_action('woocommerce_after_checkout_billing_form', 'wcfe_custom_checkout_field');
add_action('woocommerce_checkout_update_order_meta', 'wcfe_custom_checkout_field_update_order_meta');
add_action('woocommerce_email_after_order_table', 'wcfe_custom_style_checkout_email');
add_action('woocommerce_checkout_process', 'wcfe_custom_checkout_field_process',1);
add_action('woocommerce_order_details_after_order_table', 'wcfe_custom_checkout_details');
add_action('wp_enqueue_script', 'wcfe_non_admin_scripts');
add_action('wp_head', 'wcfe_display_front');

function wcfe_install() {

    $defaults = array('replace' => array(
            'add_information' => __('Additional Information', 'wc-field-editor')
        ),
        'buttons' => array(
            array(
                'label' => __('Example Label', 'wc-field-editor'),
                'placeholder' => __('Example placeholder', 'wc-field-editor'),               
            )
        ),
        'checkness' => array(
            'checkbox1' => true,            
        ),
        'billing' => array(
            'label' => __('label'),
            'placeholder' => __('placeholder'),
            'attribute' => __('attribute')
        )
    );

    add_option('wcfe_settings', apply_filters('wcfe_defaults', $defaults));
}

if (is_admin()) {
    add_action('admin_menu', 'admin_menu_wcfe');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wcfe_admin_plugin_actions', -10);
    add_action('admin_init', 'register_setting_wcfe');
} else {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', plugins_url('/css/jquery-ui.css', __FILE__) );    
}

function wcfe_non_admin_scripts() {
    if(!is_admin()){
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-style', plugins_url('/css/jquery-ui.css', __FILE__)  );
    }
}

function admin_menu_wcfe() {
    add_submenu_page('woocommerce', __('Woocommerce Checkout Field Editor', 'wc-field-editor'), __('Checkout Field Editor', 'wc-field-editor'), 'manage_options', __FILE__, 'wcfe__options_page');
}

function register_setting_wcfe() {
    register_setting('wcfe_options', 'wcfe_settings', 'wcfe_options_validate');
}

function wcfe__options_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    $hidden_field_name = 'sbtHiddenvalue';
    $options = get_option('wcfe_settings');
    
    
    if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {
        update_option('wcfe_settings', $options);
        ?> <div class="updated"><p><strong><?php _e('settings saved.'); ?></strong></p></div>
        <?php
    }
    
    echo '<div class="wrap form_heading">';
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2><span class="wcfe_name_heading">WooCommerce Checkout Field Editor</span>
	</h2>';
    ?>    

    <form name="form" method="post" action="options.php" id="frm1">
        <?php
        settings_fields('wcfe_options');
        $options = get_option('wcfe_settings');
        ?>

        <div class="wcfe_title billing-tab"><?php _e('Billing Section', 'wc-field-editor'); ?></div>
        <div class="wcfe_title shipping-tab" ><?php _e('Shipping Section', 'wc-field-editor'); ?></div>
        <div class="wcfe_title additional-tab nav-tab-active"><?php _e('Additional Fields', 'wc-field-editor'); ?></div>        
        <input type="submit" class="wcfe_title wcfe_submit" value="Save" name="sbtForm" />

        <table class="widefat billing-wccs-table billing-semi" style="margin-bottom:10px;">
            <thead>
                <tr>
                    <th><?php _e('Field Name', 'wc-field-editor'); ?></th>
                    <th><input name="wcfe_settings[checkness][select_all_rm]" type="checkbox" style="margin: 0px 5px 0px 0px;vertical-align: inherit" id="select_all_rm" value="1" <?php echo (isset($options['checkness']['select_all_rm'])) ? "checked='checked'" : ""; ?> /><?php _e('Remove Field', 'wc-field-editor'); ?></th>		
                    <th><input name="wcfe_settings[checkness][select_all_rq]" type="checkbox" style="margin: 0px 5px 0px 0px;vertical-align: inherit" id="select_all_rq" value="1" <?php echo (isset($options['checkness']['select_all_rq'])) ? "checked='checked'" : ""; ?> /><?php _e('Remove Required', 'wc-field-editor'); ?></th>
                    <th class="wccfe_replace"><?php _e('Replace Label Name', 'wc-field-editor'); ?></th>
                    <th class="wccfe_replace"><?php _e('Replace Placeholder Name', 'wc-field-editor'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('First Name', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_1]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_1'])) ? "checked='checked'" : ""; ?>   /></td>
            <td><input name="wcfe_settings[checkness][wcfe_rq_1]" type="checkbox" class="rq" value="1" <?php echo (isset($options['checkness']['wcfe_rq_1'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label]"  
                       value="<?php echo esc_attr($options['replace']['label']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder]"  
                       value="<?php echo esc_attr($options['replace']['placeholder']); ?>" /></td>
            </tr>
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Last Name', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_2]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_2'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input name="wcfe_settings[checkness][wcfe_rq_2]" type="checkbox" class="rq" value="1" <?php echo (isset($options['checkness']['wcfe_rq_2'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label1]"  
                       value="<?php echo esc_attr($options['replace']['label1']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder1]"  
                       value="<?php echo esc_attr($options['replace']['placeholder1']); ?>" /></td>
            </tr>

            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Country', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_8]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_8'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input name="wcfe_settings[checkness][wcfe_rq_8]" type="checkbox" class="rq" value="1" <?php echo (isset($options['checkness']['wcfe_rq_8'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label2]"  
                       value="<?php echo esc_attr($options['replace']['label2']); ?>" /></td>
            <td></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Phone', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_10]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_10'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input name="wcfe_settings[checkness][wcfe_rq_10]" type="checkbox" class="rq" value="1" <?php echo (isset($options['checkness']['wcfe_rq_10'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label3]"  
                       value="<?php echo esc_attr($options['replace']['label3']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder3]"  
                       value="<?php echo esc_attr($options['replace']['placeholder3']); ?>" /></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Email', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_11]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_11'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input name="wcfe_settings[checkness][wcfe_rq_11]" type="checkbox" class="rq" value="1" <?php echo (isset($options['checkness']['wcfe_rq_11'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label4]"  
                       value="<?php echo esc_attr($options['replace']['label4']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder4]"  
                       value="<?php echo esc_attr($options['replace']['placeholder4']); ?>" /></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Company', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_3]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_3'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td><input type="text" name="wcfe_settings[replace][label5]"  
                       value="<?php echo esc_attr($options['replace']['label5']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder5]"  
                       value="<?php echo esc_attr($options['replace']['placeholder5']); ?>" /></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Order Notes', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_12]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_12'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td><input type="text" name="wcfe_settings[replace][label11]"  
                       value="<?php echo esc_attr($options['replace']['label11']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder11]"  
                       value="<?php echo esc_attr($options['replace']['placeholder11']); ?>" /></td>
            </tr>  
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Address 1', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_4]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_4'])) ? "checked='checked'" : ""; ?></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Address 2', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_5]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_5'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('City', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_6]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_6'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>            
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Postal Code', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_7]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_7'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>     
            <tr class="wcfe-row">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('State', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[checkness][wcfe_opt_9]" type="checkbox" class="rm" value="1" <?php echo (isset($options['checkness']['wcfe_opt_9'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>                
            </tr>    
            </tbody>

        </table>

        <div class="billing_edit_field">
            <h2 class="text-center">Billing extra fields</h2>
            <table class="widefat billing_edit_field_table">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Label</th>
                        <th>Placeholder</th>
                        <th>Type</th>
                        <th>Attribute <div class="detail-tooltip"> ex:opt1,opt2 </div></th>
                        <th>Extra class</th>
                        <th>Required</th>
                        <th>Remove</th>                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($options['billing'])) :
                        for ($i = 0; $i < count($options['billing']); $i++) :
                            if (!isset($options['billing'][$i]))
                                break;
                            ?>            
                            <tr valign="top" class="wcfe-row">       
                                <td><input type="text" name="wcfe_settings[billing][<?php echo $i; ?>][name]"  
                                           value="<?php echo esc_attr($options['billing'][$i]['name']); ?>" /></td>
                                <td><input type="text" name="wcfe_settings[billing][<?php echo $i; ?>][label]"  
                                           value="<?php echo esc_attr($options['billing'][$i]['label']); ?>" /></td>
                                <td><input type="text" name="wcfe_settings[billing][<?php echo $i; ?>][placeholder]"  
                                           value="<?php echo esc_attr($options['billing'][$i]['placeholder']); ?>" /></td>                        
                                <td>
                                    <select name="wcfe_settings[billing][<?php echo $i; ?>][type]" class="wcfe_select_type">  <!--Call run() function-->
                                        <option value="" <?php selected($options['billing'][$i]['type'], ''); ?>>Select type</option>
                                        <option value="text" <?php selected($options['billing'][$i]['type'], 'text'); ?>>Text Input</option>
                                        <option value="select" <?php selected($options['billing'][$i]['type'], 'select'); ?>>Select Options</option>
                                        <option value="multiselect" <?php selected($options['billing'][$i]['type'], 'multiselect'); ?>>Multiple selection</option>
                                        <option value="date" <?php selected($options['billing'][$i]['type'], 'date'); ?>>Date Picker</option>
                                        <option value="radio" <?php selected($options['billing'][$i]['type'], 'radio'); ?>>Radio Selection</option>
                                        <option value="checkbox" <?php selected($options['billing'][$i]['type'], 'checkbox'); ?>>Checkbox</option>    
                                        <option value="textarea" <?php selected($options['billing'][$i]['type'], 'textarea'); ?>>Teaxarea Input</option>
                                    </select>
                                </td>      
                                <td><input class="wcfe_attributes_input" type="text" name="wcfe_settings[billing][<?php echo $i; ?>][attributes]"  
                                           value="<?php echo esc_attr($options['billing'][$i]['attributes']); ?>" />
                                </td> 
                                <td><input class="" type="text" name="wcfe_settings[billing][<?php echo $i; ?>][extra_class]"  
                                           value="<?php echo esc_attr($options['billing'][$i]['extra_class']); ?>" />
                                </td> 
                                <td style="text-align: center;"><input class="" type="checkbox" name="wcfe_settings[billing][<?php echo $i; ?>][required]"  
                                                                       value="true" <?php echo (isset($options['billing'][$i]['required'])) ? "checked='checked'" : ""; ?> />
                                </td>   
                                <td class="wcfe-remove">
                                    <a class="wcfe-remove-button" title="Remove Field" href="javascript:;" style="color: #a00 !important;"><img src="<?php echo plugins_url('/images/ico-delete.png', __FILE__); ?>" alt="Remove image"></a>
                                </td>
                            </tr>
                            <?php
                        endfor;
                    endif;
                    $i = 999;
                    ?>       
                <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
                <tr valign="top" class="wcfe_clone" >                 
                    <td><input type="text" name="wcfe_settings[billing][999][name]"  
                               title="<?php esc_attr_e('Name of field', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input type="text" name="wcfe_settings[billing][999][label]"  
                               title="<?php esc_attr_e('Label of the New Field', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input type="text" name="wcfe_settings[billing][999][placeholder]"  
                               title="<?php esc_attr_e('Placeholder - Preview of Data to Input', 'wc-field-editor'); ?>" value="" /></td>                
                    <td>
                        <select name="wcfe_settings[billing][<?php echo $i; ?>][type]" class="wcfe_select_type">  <!--Call run() function-->
                            <option value="" >Select type</option>
                            <option value="text" >Text Input</option>
                            <option value="select" >Select Options</option>
                            <option value="multiselect" >Multiple selection</option>
                            <option value="date" >Date Picker</option>
                            <option value="radio" >Radio Selection</option>
                            <option value="checkbox" >Checkbox</option>    
                            <option value="textbox" >Teaxarea Input</option>    
                        </select>
                    </td>
                    <td><input class="wcfe_attributes_input" type="text" name="wcfe_settings[billing][999][attributes]"  
                               title="<?php esc_attr_e('Attributes - Preview of Data to Input', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input class="" type="text" name="wcfe_settings[billing][999][extra_class]"  
                               title="<?php esc_attr_e('Extra class - Add extra class', 'wc-field-editor'); ?>" value="" /></td>                    
                    <td style="text-align: center;"><input type="checkbox" value="" title="Add/Remove Required Attribute" name="wcfe_settings[billing][999][required]" style="float:none;"></td>
                    <td class="wcfe-remove">
                        <a class="wcfe-remove-button" title="Remove Field" href="javascript:;" style="color: #a00 !important;"><img src="<?php echo plugins_url('/images/ico-delete.png', __FILE__); ?>" alt="Remove image"></a>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="wccfe-table-footer">
                <a href="javascript:;" id="wcfe-billing-field" class="button-secondary"><?php _e('+ Add New Field', 'wc-field-editor'); ?></a>
            </div>
        </div>                        


        <table class="widefat shipping-wccs-table shipping-semi" style="">
            <thead>
                <tr>
                    <th><?php _e('Field Name', 'wc-field-editor'); ?></th>
                    <th><input name="wcfe_settings[checkness][select_all_rm_s]" type="checkbox" style="margin: 0px 5px 0px 0px;vertical-align: inherit" id="select_all_rm_s" value="1" <?php echo (isset($options['checkness']['select_all_rm_s'])) ? "checked='checked'" : ""; ?> /><?php _e('Remove Field', 'wc-field-editor'); ?></th>		
                    <th><input name="wcfe_settings[checkness][select_all_rq_s]" type="checkbox" style="margin: 0px 5px 0px 0px;vertical-align: inherit" id="select_all_rq_s" value="1" <?php echo (isset($options['checkness']['select_all_rq_s'])) ? "checked='checked'" : ""; ?> /><?php _e('Remove Required', 'wc-field-editor'); ?></th>
                    <th class="wccfe_replace"><?php _e('Replace Label Name', 'wc-field-editor'); ?></th>
                    <th class="wccfe_replace"><?php _e('Replace Placeholder Name', 'wc-field-editor'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('First Name', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_1_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_1_s'])) ? "checked='checked'" : ""; ?>
                       <?php echo (isset($options['check']['wccs_opt_1_s'])) ? "checked='checked'" : ""; ?>   /></td>
            <td><input name="wcfe_settings[check][wcfe_rq_1_s]" type="checkbox" class="rq_s" value="1" <?php echo (isset($options['check']['wcfe_rq_1_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label_s]"  
                       value="<?php echo esc_attr($options['replace']['label_s']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder_s]"  
                       value="<?php echo esc_attr($options['replace']['placeholder_s']); ?>" /></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Last Name', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_2_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_2_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input name="wcfe_settings[check][wcfe_rq_2_s]" type="checkbox" class="rq_s" value="1" <?php echo (isset($options['check']['wcfe_rq_2_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td><input type="text" name="wcfe_settings[replace][label_s1]"  
                       value="<?php echo esc_attr($options['replace']['label_s1']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder_s1]"  
                       value="<?php echo esc_attr($options['replace']['placeholder_s1']); ?>" /></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Company', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_3_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_3_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td><input type="text" name="wcfe_settings[replace][label_s2]"  
                       value="<?php echo esc_attr($options['replace']['label_s2']); ?>" /></td>
            <td><input type="text" name="wcfe_settings[replace][placeholder_s2]"  
                       value="<?php echo esc_attr($options['replace']['placeholder_s2']); ?>" /></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Country', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_8_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_8_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td><input type="text" name="wcfe_settings[replace][label_s7]"  
                       value="<?php echo esc_attr($options['replace']['label_s7']); ?>" /></td>
            <td></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Address 1', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_4_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_4_s'])) ? "checked='checked'" : ""; ?></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Address 2', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_5_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_5_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('City', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_6_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_6_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('Postal Code', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_7_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_7_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            </tbody>
            <tbody>
                <tr>
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <td><?php _e('State', 'wc-field-editor'); ?></td>
            <td><input name="wcfe_settings[check][wcfe_opt_9_s]" type="checkbox" class="rm_s" value="1" <?php echo (isset($options['check']['wcfe_opt_9_s'])) ? "checked='checked'" : ""; ?> /></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            </tbody>


        </table>

        <div class="shipping_edit_field">
            <h2 class="text-center">Shipping extra fields</h2>
            <table class="widefat shipping_edit_field_table">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Label</th>
                        <th>Placeholder</th>
                        <th>Type</th>
                        <th>Attribute <div class="detail-tooltip"> ex:opt1,opt2 </div></th>
                        <th>Extra class</th>
                        <th>Required</th>
                        <th>Remove</th>  
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($options['shipping'])) :
                        for ($i = 0; $i < count($options['shipping']); $i++) :
                            if (!isset($options['shipping'][$i]))
                                break;
                            ?>            
                            <tr valign="top" class="wcfe-row">       
                                <td><input type="text" name="wcfe_settings[shipping][<?php echo $i; ?>][name]"  
                                           value="<?php echo esc_attr($options['shipping'][$i]['name']); ?>" /></td>
                                <td><input type="text" name="wcfe_settings[shipping][<?php echo $i; ?>][label]"  
                                           value="<?php echo esc_attr($options['shipping'][$i]['label']); ?>" /></td>
                                <td><input type="text" name="wcfe_settings[shipping][<?php echo $i; ?>][placeholder]"  
                                           value="<?php echo esc_attr($options['shipping'][$i]['placeholder']); ?>" /></td>                        
                                <td>
                                    <select name="wcfe_settings[shipping][<?php echo $i; ?>][type]" class="wcfe_select_type">  <!--Call run() function-->
                                        <option value="" <?php selected($options['billing'][$i]['type'], ''); ?>>Select type</option>
                                        <option value="text" <?php selected($options['shipping'][$i]['type'], 'text'); ?>>Text Input</option>
                                        <option value="select" <?php selected($options['shipping'][$i]['type'], 'select'); ?>>Select Options</option>
                                        <option value="multiselect" <?php selected($options['shipping'][$i]['type'], 'multiselect'); ?>>Multiple select</option>
                                        <option value="date" <?php selected($options['shipping'][$i]['type'], 'date'); ?>>Date Picker</option>
                                        <option value="radio" <?php selected($options['shipping'][$i]['type'], 'radio'); ?>>Radio Selection</option>
                                        <option value="checkbox" <?php selected($options['shipping'][$i]['type'], 'checkbox'); ?>>Checkbox</option>    
                                        <option value="textbox" <?php selected($options['shipping'][$i]['type'], 'textbox'); ?>>Teaxarea Input</option>    
                                    </select>
                                </td>      
                                <td><input type="text" name="wcfe_settings[shipping][<?php echo $i; ?>][attributes]"  
                                           value="<?php echo esc_attr($options['shipping'][$i]['attributes']); ?>" class="wcfe_attributes_input" />
                                </td>
                                <td><input class="" type="text" name="wcfe_settings[shipping][<?php echo $i; ?>][extra_class]"  
                                           value="<?php echo esc_attr($options['shipping'][$i]['extra_class']); ?>" />
                                </td> 
                                <td style="text-align: center;"><input class="" type="checkbox" name="wcfe_settings[shipping][<?php echo $i; ?>][required]"  
                                           value="true" <?php echo (isset($options['shipping'][$i]['required'])) ? "checked='checked'" : ""; ?> />
                                </td>   
                                <td class="wcfe-remove">
                                    <a class="wcfe-remove-button" title="Remove Field" href="javascript:;" style="color: #a00 !important;"><img src="<?php echo plugins_url('/images/ico-delete.png', __FILE__); ?>" alt="Remove image"></a>
                                </td>
                            </tr>
                            <?php
                        endfor;
                    endif;
                    $i = 999;
                    ?>       
                <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
                <tr valign="top" class="wcfe_clone" >                 
                    <td><input type="text" name="wcfe_settings[shipping][999][name]"  
                               title="<?php esc_attr_e('Name of field', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input type="text" name="wcfe_settings[shipping][999][label]"  
                               title="<?php esc_attr_e('Label of the New Field', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input type="text" name="wcfe_settings[shipping][999][placeholder]"  
                               title="<?php esc_attr_e('Placeholder - Preview of Data to Input', 'wc-field-editor'); ?>" value="" /></td>                
                    <td>
                        <select name="wcfe_settings[shipping][<?php echo $i; ?>][type]" class="wcfe_select_type">  <!--Call run() function-->
                            <option value="" >Select type</option>
                            <option value="text" >Text Input</option>
                            <option value="select" >Select Options</option>
                            <option value="multiselect">Multiple select</option>
                            <option value="date">Date Picker</option>
                            <option value="radio" >Radio Selection</option>
                            <option value="checkbox" >Checkbox</option>    
                            <option value="textbox" >Teaxarea Input</option>    
                        </select>
                    </td>
                    <td><input type="text" name="wcfe_settings[shipping][999][attributes]" class="wcfe_attributes_input"  
                               title="<?php esc_attr_e('Attributes - Preview of Data to Input', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input class="" type="text" name="wcfe_settings[shipping][999][extra_class]"  
                               title="<?php esc_attr_e('Extra class - Add extra class', 'wc-field-editor'); ?>" value="" /></td>                    
                    <td style="text-align: center;"><input type="checkbox" value="" title="Add/Remove Required Attribute" name="wcfe_settings[shipping][999][required]" style="float:none;"></td>
                    <td class="wcfe-remove">
                        <a class="wcfe-remove-button" title="Remove Field" href="javascript:;" style="color: #a00 !important;"><img src="<?php echo plugins_url('/images/ico-delete.png', __FILE__); ?>" alt="Remove image"></a>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="wccfe-table-footer">
                <a href="javascript:;" id="wcfe-shipping-field" class="button-secondary"><?php _e('+ Add New Field', 'wc-field-editor'); ?></a>
            </div>
        </div>

        <div class="additional-semi">

            <table class="widefat wccfe-table">
                <thead>
                    <tr>
                        <th>	
                            <span style="width:5%"><?php _e('Section Name', 'wc-field-editor'); ?></span>
                            <input style="width:74%" type="text" name="wcfe_settings[replace][add_information]" value="<?php echo esc_attr($options['replace']['add_information']); ?>" /></th>	
                        <th style="text-align:center;"><input style="float: center;margin-left: 0;" name="wcfe_settings[checkness][checkbox1]" type="checkbox" value="true" <?php echo (isset($options['checkness']['checkbox1'])) ? "checked='checked'" : ""; ?> /></th>
                        <th><?php _e('Checkout Details and Email Receipt', 'wc-field-editor'); ?></th>
                    </tr>
                </thead>
            </table>

            <table class="widefat additional_edit_field_table">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Label</th>
                        <th>Placeholder</th>
                        <th>Type</th>
                        <th>Attribute <div class="detail-tooltip"> ex:opt1,opt2 </div></th>
                        <th>Extra class</th>
                        <th>Required</th>
                        <th>Remove</th>                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($options['buttons'])) :
                        for ($i = 0; $i < count($options['buttons']); $i++) :
                            if (!isset($options['buttons'][$i]))
                                break;
                            ?>            
                            <tr valign="top" class="wcfe-row">       
                                <td><input type="text" name="wcfe_settings[buttons][<?php echo $i; ?>][name]"  
                                           value="<?php echo esc_attr($options['buttons'][$i]['name']); ?>" /></td>
                                <td><input type="text" name="wcfe_settings[buttons][<?php echo $i; ?>][label]"  
                                           value="<?php echo esc_attr($options['buttons'][$i]['label']); ?>" /></td>
                                <td><input type="text" name="wcfe_settings[buttons][<?php echo $i; ?>][placeholder]"  
                                           value="<?php echo esc_attr($options['buttons'][$i]['placeholder']); ?>" /></td>                        
                                <td>
                                    <select name="wcfe_settings[buttons][<?php echo $i; ?>][type]" class="wcfe_select_type">  <!--Call run() function-->
                                        <option value="" <?php selected($options['buttons'][$i]['type'], ''); ?>>Select type</option>
                                        <option value="text" <?php selected($options['buttons'][$i]['type'], 'text'); ?>>Text Input</option>
                                        <option value="select" <?php selected($options['buttons'][$i]['type'], 'select'); ?>>Select Options</option>
                                        <option value="multiselect" <?php selected($options['buttons'][$i]['type'], 'multiselect'); ?>>Multiple Select</option>
                                        <option value="date" <?php selected($options['buttons'][$i]['type'], 'date'); ?>>Date Picker</option>
                                        <option value="radio" <?php selected($options['buttons'][$i]['type'], 'radio'); ?>>Radio Selection</option>
                                        <option value="checkbox" <?php selected($options['buttons'][$i]['type'], 'checkbox'); ?>>Checkbox</option>    
                                        <option value="textarea" <?php selected($options['buttons'][$i]['type'], 'textarea'); ?>>Teaxarea Input</option>
                                    </select>
                                </td>      
                                <td><input class="wcfe_attributes_input" type="text" name="wcfe_settings[buttons][<?php echo $i; ?>][attributes]"  
                                           value="<?php echo esc_attr($options['buttons'][$i]['attributes']); ?>" />
                                </td> 
                                <td><input class="" type="text" name="wcfe_settings[buttons][<?php echo $i; ?>][extra_class]"  
                                           value="<?php echo esc_attr($options['buttons'][$i]['extra_class']); ?>" />
                                </td> 
                                <td style="text-align: center;"><input class="" type="checkbox" name="wcfe_settings[buttons][<?php echo $i; ?>][required]"  
                                           value="true" <?php echo (isset($options['buttons'][$i]['required'])) ? "checked='checked'" : ""; ?> />
                                </td>   
                                <td class="wcfe-remove">
                                    <a class="wcfe-remove-button" title="Remove Field" href="javascript:;" style="color: #a00 !important;"><img src="<?php echo plugins_url('/images/ico-delete.png', __FILE__); ?>" alt="Remove image"></a>
                                </td>
                            </tr>
                            <?php
                        endfor;
                    endif;
                    $i = 999;
                    ?>       
                <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
                <tr valign="top" class="wcfe_clone" >                 
                    <td><input type="text" name="wcfe_settings[buttons][999][name]"  
                               title="<?php esc_attr_e('Name of field', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input type="text" name="wcfe_settings[buttons][999][label]"  
                               title="<?php esc_attr_e('Label of the New Field', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input type="text" name="wcfe_settings[buttons][999][placeholder]"  
                               title="<?php esc_attr_e('Placeholder - Preview of Data to Input', 'wc-field-editor'); ?>" value="" /></td>                
                    <td>
                        <select name="wcfe_settings[buttons][<?php echo $i; ?>][type]" class="wcfe_select_type">  <!--Call run() function-->
                            <option value="" >Select type</option>
                            <option value="text" >Text Input</option>
                            <option value="select" >Select Options</option>
                            <option value="multiselect" >Multiple Selection</option>
                            <option value="date" >Date Picker</option>
                            <option value="radio" >Radio Selection</option>
                            <option value="checkbox" >Checkbox</option>    
                            <option value="textbox" >Teaxarea Input</option>    
                        </select>
                    </td>
                    <td><input class="wcfe_attributes_input" type="text" name="wcfe_settings[buttons][999][attributes]"  
                               title="<?php esc_attr_e('Attributes - Preview of Data to Input', 'wc-field-editor'); ?>" value="" /></td>
                    <td><input class="" type="text" name="wcfe_settings[buttons][999][extra_class]"  
                               title="<?php esc_attr_e('Extra class - Add extra class', 'wc-field-editor'); ?>" value="" /></td>                    
                    <td style="text-align: center;"><input type="checkbox" value="" title="Add/Remove Required Attribute" name="wcfe_settings[buttons][999][required]" style="float:none;"></td>
                    <td class="wcfe-remove">
                                    <a class="wcfe-remove-button" title="Remove Field" href="javascript:;" style="color: #a00 !important;"><img src="<?php echo plugins_url('/images/ico-delete.png', __FILE__); ?>" alt="Remove image"></a>
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="wccfe-table-footer">
                <a href="javascript:;" id="wcfe-additional-field" class="button-secondary"><?php _e('+ Add New Field', 'wc-field-editor'); ?></a>
            </div>
        </div>
    </form>
    <?php
}

// Build array of links for rendering in installed plugins list
function wcfe_admin_plugin_actions($links) {
    $wccs_plugin_links = array(
        '<a href="admin.php?page=wc-checkout-field-editor/woocommerce-checkout-field-editor.php">' . __('Settings') . '</a>',
    );
    return array_merge($wccs_plugin_links, $links);
}

function wcfe_scripts() {
    if (is_admin()) {
        wp_enqueue_style('wooccm-style', plugins_url('/admin/css/admin_style.css', __FILE__));
        wp_enqueue_script('script_wccs', plugins_url('/admin/js/admin_script.js', __FILE__), array('jquery'), '1.2');
        if (!wp_script_is('jquery-ui-sortable', 'queue')) {
            wp_enqueue_script('jquery-ui-sortable');
        }
    }
}

function wccs_override_checkout_fields($fields) {
    $options = get_option('wcfe_settings');  
    if (1 == ($options['checkness']['wcfe_opt_1'] )) {
        unset($fields['billing']['billing_first_name']);
    }
    if (!empty($options['replace']['placeholder'])) {
        $fields['billing']['billing_first_name']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder']);
    }
    if (!empty($options['replace']['label'])) {
        $fields['billing']['billing_first_name']['label'] = wcfe_wpml_string($options['replace']['label']);
    }
    if (1 == ($options['checkness']['wcfe_opt_2'] )) {
        unset($fields['billing']['billing_last_name']);
    }
    if (!empty($options['replace']['placeholder1'])) {
        $fields['billing']['billing_last_name']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder1']);
    }
    if (!empty($options['replace']['label1'])) {
        $fields['billing']['billing_last_name']['label'] = wcfe_wpml_string($options['replace']['label1']);
    }
    if (1 == ($options['checkness']['wcfe_opt_3'] )) {
        unset($fields['billing']['billing_company']);
    }
    if (!empty($options['replace']['placeholder2'])) {
        $fields['billing']['billing_company']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder2']);
    }
    if (!empty($options['replace']['label5'])) {
        $fields['billing']['billing_company']['label'] = wcfe_wpml_string($options['replace']['label5']);
    }
    if (1 == ($options['checkness']['wcfe_opt_4'] )) {
        unset($fields['billing']['billing_address_1']);
    }
    if (1 == ($options['checkness']['wcfe_opt_5'] )) {
        unset($fields['billing']['billing_address_2']);
    }
    if (1 == ($options['checkness']['wcfe_opt_6'] )) {
        unset($fields['billing']['billing_city']);
    }
    if (1 == ($options['checkness']['wcfe_opt_7'] )) {
        unset($fields['billing']['billing_postcode']);
    }
    if (1 == ($options['checkness']['wcfe_opt_8'] )) {
        unset($fields['billing']['billing_country']);
    }
    if (1 == ($options['checkness']['wcfe_opt_9'] )) {
        unset($fields['billing']['billing_state']);
    }
    if (1 == ($options['checkness']['wcfe_opt_10'] )) {
        unset($fields['billing']['billing_phone']);
    }
    if (!empty($options['replace']['label2'])) {
        $fields['billing']['billing_country']['label'] = wcfe_wpml_string($options['replace']['label2']);
    }
    if (!empty($options['replace']['placeholder3'])) {
        $fields['billing']['billing_phone']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder3']);
    }
    if (!empty($options['replace']['label3'])) {
        $fields['billing']['billing_phone']['label'] = wcfe_wpml_string($options['replace']['label3']);
    }
    if (1 == ($options['checkness']['wcfe_opt_11'] )) {
        unset($fields['billing']['billing_email']);
    }
    if (!empty($options['replace']['placeholder4'])) {
        $fields['billing']['billing_email']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder4']);
    }
    if (!empty($options['replace']['label4'])) {
        $fields['billing']['billing_email']['label'] = wcfe_wpml_string($options['replace']['label4']);
    }
    if (1 == ($options['checkness']['wcfe_opt_12'] )) {
        unset($fields['order']['order_comments']);
    }
    if (!empty($options['replace']['placeholder11'])) {
        $fields['order']['order_comments']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder11']);
    }
    if (!empty($options['replace']['label11'])) {
        $fields['order']['order_comments']['label'] = wcfe_wpml_string($options['replace']['label11']);
    }
    if (1 == ($options['check']['wcfe_opt_1_s'] )) {
        unset($fields['shipping']['shipping_first_name']);
    }
    if (!empty($options['replace']['placeholder_s'])) {
        $fields['shipping']['shipping_first_name']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder_s']);
    }
    if (!empty($options['replace']['label_s'])) {
        $fields['shipping']['shipping_first_name']['label'] = wcfe_wpml_string($options['replace']['label_s']);
    }
    if (1 == ($options['check']['wcfe_opt_2_s'] )) {
        unset($fields['shipping']['shipping_last_name']);
    }
    if (!empty($options['replace']['placeholder_s1'])) {
        $fields['shipping']['shipping_last_name']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder_s1']);
    }
    if (!empty($options['replace']['label_s1'])) {
        $fields['shipping']['shipping_last_name']['label'] = wcfe_wpml_string($options['replace']['label_s1']);
    }
    if (1 == ($options['check']['wcfe_opt_3_s'] )) {
        unset($fields['shipping']['shipping_company']);
    }
    if (!empty($options['replace']['placeholder_s2'])) {
        $fields['shipping']['shipping_company']['placeholder'] = wcfe_wpml_string($options['replace']['placeholder_s2']);
    }
    if (!empty($options['replace']['label_s2'])) {
        $fields['shipping']['shipping_company']['label'] = wcfe_wpml_string($options['replace']['label_s2']);
    }
    if (1 == ($options['check']['wcfe_opt_4_s'] )) {
        unset($fields['shipping']['shipping_address_1']);
    }
    if (1 == ($options['check']['wcfe_opt_5_s'] )) {
        unset($fields['shipping']['shipping_address_2']);
    }
    if (1 == ($options['check']['wcfe_opt_6_s'] )) {
        unset($fields['shipping']['shipping_city']);
    }
    if (1 == ($options['check']['wcfe_opt_7_s'] )) {
        unset($fields['shipping']['shipping_postcode']);
    }
    if (1 == ($options['check']['wcfe_opt_8_s'] )) {
        unset($fields['shipping']['shipping_country']);
    }
    if (!empty($options['replace']['label_s7'])) {
        $fields['shipping']['shipping_country']['label'] = wcfe_wpml_string($options['replace']['label_s7']);
    }
    if (1 == ($options['check']['wcfe_opt_9_s'] )) {
        unset($fields['shipping']['shipping_state']);
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'wccs_override_checkout_fields');


/*------- Requird field for billing check coding-----------------*/
function billing_override_required_fields($address_fields) {
    $options = get_option('wcfe_settings');
    //echo '<pre>';
    //print_r($options['checkness']);
    if (1 == ($options['checkness']['wcfe_rq_2'] )) {
        $address_fields['billing_last_name']['required'] = false;
    }
    if (1 == ($options['checkness']['wcfe_rq_1'] )) {
        $address_fields['billing_first_name']['required'] = false;
    }
    if (1 == ($options['checkness']['wcfe_rq_8'] )) {
        $address_fields['billing_country']['required'] = false;
    }
    if (1 == ($options['checkness']['wcfe_rq_10'] )) {
        $address_fields['billing_phone']['required'] = false;
    }
    if (1 == ($options['checkness']['wcfe_rq_11'] )) {
        $address_fields['billing_email']['required'] = false;
    }
    return $address_fields;
}
add_filter('woocommerce_billing_fields', 'billing_override_required_fields', 10, 1);
/*------- Requird field check coding-----------------*/


/*------- Requird field for shipping check coding-----------------*/
function shipping_override_required_fields($address_fields) {
    $options = get_option('wcfe_settings');
    if (1 == ($options['check']['wcfe_rq_1_s'] )) {
        $address_fields['shipping_first_name']['required'] = false;
    }
    if (1 == ($options['check']['wcfe_rq_2_s'] )) {
        $address_fields['shipping_last_name']['required'] = false;
    }
    return $address_fields;
}
add_filter('woocommerce_shipping_fields', 'shipping_override_required_fields', 10, 1);
/*------- Requird field for shipping check coding-----------------*/


function wcfe_add_title() {
    $options = get_option('wcfe_settings');    
    echo '<div class="addition_information_header"><br><h3>' . esc_attr($options['replace']['add_information']) . '</h3></div>';
}

// =============== Add the field to the checkout =====================
function wcfe_custom_checkout_field($checkout) {
    $options = get_option('wcfe_settings');   
    if (count($options['buttons']) > 0) :
        foreach ($options['buttons'] as $btn) :
            if (!empty($btn['label']) && ($btn['type'] == 'text')) {
                if(isset($btn['required']) && $btn['required'] != ''){
                    $required = $btn['required'];
                }else{
                    $required = FALSE;
                }
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'text',
                    'class' => array($btn['extra_class']),
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'required' => $required,
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                        ), $checkout->get_value('' . $btn['name'] . ''));
            }
            if (!empty($btn['label']) && ($btn['type'] == 'textarea')) {
                if(isset($btn['required']) && $btn['required'] != ''){
                    $required = $btn['required'];
                }else{
                    $required = FALSE;
                }
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'textarea',
                    'class' => array($btn['extra_class']),
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'required' => $required,
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                        ), $checkout->get_value('' . $btn['name'] . ''));
            }
            if (!empty($btn['label']) && ($btn['type'] == 'select')) {
                if(isset($btn['required']) && $btn['required'] != ''){
                    $required = $btn['required'];
                }else{
                    $required = FALSE;
                }
                $single_attribute = explode(',',$btn['attributes'] ); 
                $single_attribute = array_combine($single_attribute, $single_attribute);
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'select',
                    'class' => array($btn['extra_class']),
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'options' => $single_attribute,
                    'required' => $required,
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                        ), $checkout->get_value('' . $btn['name'] . ''));
            }
            if (!empty($btn['label']) && ($btn['type'] == 'radio')) {
                if(isset($btn['required']) && $btn['required'] != ''){
                    $required = $btn['required'];
                }else{
                    $required = FALSE;
                }
                $single_attribute = explode(',',$btn['attributes'] );
                $single_attribute = array_combine($single_attribute, $single_attribute);
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'radio',
                    'class' => array($btn['extra_class']),
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'options' => $single_attribute,
                    'required' => $required,
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                        ), $checkout->get_value('' . $btn['name'] . ''));
            }
            if (!empty($btn['label']) && ($btn['type'] == 'date')) {
                echo '<script type="text/javascript">
                                jQuery(document).ready(function() {
                                    jQuery(".MyDate-' . $btn['name'] . ' #' . $btn['name'] . '").datepicker({
                                        dateFormat : "dd-mm-yy"
                                    });
                                });
                                </script>';
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'text',
                    'class' => array('wcfe-field-class MyDate-' . $btn['name'] . ' wccs-form-row-wide'),
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'required' => $btn['required'],
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                        ), $checkout->get_value('' . $btn['name'] . ''));
            }
            if (!empty($btn['label']) && ($btn['type'] == 'checkbox')) {
                if(isset($btn['required']) && $btn['required'] != ''){
                    $required = $btn['required'];
                }else{
                    $required = FALSE;
                }
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'checkbox',
                    'class' => array($btn['extra_class']),
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'required' => $required,
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                        ), $checkout->get_value('' . $btn['name'] . ''));
            }
            if (!empty($btn['label']) && ($btn['type'] == 'multiselect')) {
                if(isset($btn['required'])){
                    $required = TRUE;
                }else{
                    $required = FALSE;
                }
                $single_attribute = explode(',',$btn['attributes'] ); 
                $single_attribute = array_combine($single_attribute, $single_attribute);
                woocommerce_form_field('' . $btn['name'] . '', array(
                    'type' => 'multiselect',
                    'class' => array($btn['extra_class']),
                    'options' => $single_attribute,
                    'label' => wcfe_wpml_string('' . $btn['label'] . ''),
                    'required' => $required,
                    'required_multiselect' => $required,
                    'placeholder' => wcfe_wpml_string('' . $btn['placeholder'] . ''),
                ),$checkout->get_value('' . $btn['name'] . ''));
            }
        endforeach;
    endif;
}    

// ============================== Update the order meta with field value ==============================
function wcfe_custom_checkout_field_update_order_meta($order_id) {
    $options = get_option('wcfe_settings');
    if (count($options['buttons']) > 0) :
        foreach ($options['buttons'] as $btn) :
            if (!empty($btn['name']))
                if ($_POST['' . $btn['name'] . ''])
                    update_post_meta($order_id, '' . $btn['name'] . '', esc_attr($_POST['' . $btn['name'] . '']));
        endforeach;
    endif;
}

// =============== Add to email (working) =====================
add_filter('woocommerce_email_order_meta_keys', 'wcfe_custom_checkout_field_order_meta_keys');

function wcfe_custom_checkout_field_order_meta_keys($keys) {
    $options = get_option('wcfe_settings');
    if (count($options['buttons']) > 0) :
        foreach ($options['buttons'] as $btn) :
            if (!empty($btn['name']))
                $keys['' . wcfe_wpml_string($btn['label']) . ''] = '' . $btn['name'] . '';
        endforeach;
        return $keys;
    endif;
}

// ================ Style the Email =======================
function wcfe_custom_style_checkout_email() {
    $options = get_option('wcfe_settings');
    if (true == ($options['checkness']['checkbox1']))
        echo '<h2>' . esc_attr($options['replace']['add_information']) . '</h2>';
}

// ============== Process the checkout (if needed activate) ==================
function wcfe_custom_checkout_field_process() {
    global $woocommerce;
    $options = get_option('wcfe_settings');
   
     if (count($options['buttons']) > 0) :
        foreach ($options['buttons'] as $btn) :            
         if(isset($btn['required']) && $btn['required'] != ''){
                    $required = $btn['required'];
                }else{
                    $required = FALSE;
                }
            if ((!$_POST['' . $btn['name'] . ''] ) && (true == ($required) ))
                wc_add_notice('<strong>' . $btn['label'] . '</strong> ' . __('is a required field', 'wc-field-editor') . ' ', 'error');
        endforeach;
    endif;
    if (count($options['billing']) > 0) :
        foreach ($options['billing'] as $btn) :     
        if($btn['type'] == 'multiselect')            
            {
                if(isset($btn['required']) && $btn['required'] != ''){
                    $required = TRUE;
                }else{
                    $required = FALSE;
                }                
                if ((!$_POST['billing_' . $btn['name'] . ''] ) && (true == ($required) ))
                    wc_add_notice('<strong>' . $btn['label'] . '</strong> ' . __('is a required field', 'wc-field-editor') . ' ', 'error');
            }
        endforeach;
    endif;
    if(isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'] != ''){
        if (count($options['shipping']) > 0) :
                foreach ($options['shipping'] as $btn) :     
                if($btn['type'] == 'multiselect')            
                    {
                        if(isset($btn['required']) && $btn['required'] != ''){
                            $required = TRUE;
                        }else{
                            $required = FALSE;
                        }                
                        if ((!$_POST['shipping_' . $btn['name'] . ''] ) && (true == ($required) ))
                            wc_add_notice('<strong>' . $btn['label'] . '</strong> ' . __('is a required field', 'wc-field-editor') . ' ', 'error');
                    }
                endforeach;
            endif;
    }
}
 

function wcfe_options_validate($input) {
    
    $options = get_option('wcfe_settings');
    foreach ($input['buttons'] as $i => $btn) :
       
        if (empty($btn['name']) && empty($btn['label']) && empty($btn['placeholder'])) {
            unset($input['buttons'][$i], $btn);
        }

        if (empty($btn['name']) && (!empty($btn['label']) || !empty($btn['placeholder']))) {
            $newNum = $i + 1;
            $input['buttons'][$i]['name'] = 'myfield' . $newNum . '';
        }

    endforeach;

    $input['buttons'] = array_values($input['buttons']);
    return $input;
}

function wcfe_custom_checkout_details($order_id) {
    $options = get_option('wcfe_settings');

    if (!empty($options['checkness']['checkbox1'])) {
        echo '<h2>' . esc_attr($options['replace']['add_information']) . '</h2>';
    
    if (count($options['buttons']) > 0) :
        foreach ($options['buttons'] as $btn) :
            echo '<dt>' . wcfe_wpml_string($btn['label']) . ':</dt><dd>' . get_post_meta($order_id->id, '' . $btn['name'] . '', true) . '</dd>';
        endforeach;
    endif;
    }
}


function wcfe_display_front() {
    echo '<style type="text/css">       
        .addition_information_header {
            clear: both;
        }
      </style>';
}

// =============== Make compatible with WooCommerce Delivery Notes ===========
function wcfe_woocommerce_delivery_notes_compat($fields, $order) {
    $options = get_option('wcfe_settings');
    $new_fields = array();

    if (count($options['buttons']) > 0) :
        foreach ($options['buttons'] as $btn) :
            if (get_post_meta($order->id, '' . $btn['name'] . '', true)) {
                $new_fields['' . $btn['name'] . ''] = array(
                    'label' => '' . wcfe_wpml_string($btn['label']) . '',
                    'value' => get_post_meta($order->id, '' . $btn['name'] . '', true)
                );
            }
        endforeach;
    endif;

    return array_merge($fields, $new_fields);
}

// ================ Add field names to WPML String Translation ===============
function wcfe_wpml_string($input) {
    if (function_exists('icl_t')) {
        return icl_t('Woocommerce Checkout Field Editor', '' . $input . '', '' . $input . '');
    } else {
        return $input;
    }
}

// Function Hook
Function rs_custom_billing_fields($fields) {

    $options = get_option('wcfe_settings');
    $filter_arrays = $options['billing'];
    if ($filter_arrays) {
        foreach ($filter_arrays as $filter_array) {
            if (array_filter($filter_array)) {
                if ($filter_array['type'] == 'select' || $filter_array['type'] == 'radio') {
                    $single_attribute = explode(',', $filter_array['attributes']);
                    $single_attribute = array_combine($single_attribute, $single_attribute);                    
                    if (isset($filter_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    $fields['billing_' . $filter_array['name']] = array(
                        'label' => __($filter_array['label'], 'woocommerce'),
                        'placeholder' => _x($filter_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($filter_array['extra_class']),
                        'clear' => false,
                        'type' => $filter_array['type'],
                        'options' => $single_attribute
                    );
                } else if ($filter_array['type'] == 'checkbox') {
                    if (isset($filter_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    $fields['billing_' . $filter_array['name']] = array(
                        'label' => __($filter_array['label'], 'woocommerce'),
                        'placeholder' => _x($filter_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($filter_array['extra_class']),
                        'clear' => false,
                        'type' => 'checkbox'
                    );
                }else if ($filter_array['type'] == 'multiselect' ) {
                    $single_attribute = explode(',', $filter_array['attributes']);
                    $single_attribute = array_combine($single_attribute, $single_attribute);
                    if (isset($filter_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                  $fields['billing_' . $filter_array['name']] = array(
                        'label' => __($filter_array['label'], 'woocommerce'),
                        'placeholder' => _x($filter_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => FALSE,
                        'class' => array($filter_array['extra_class']),
                        'clear' => false,
                        'type' => $filter_array['type'],
                        'options' => $single_attribute,
                        'required_multiselect' => $required
                  );                    
                }else if ($filter_array['type'] == 'date' ){
                    if (isset($filter_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    echo '<script type="text/javascript">
                                jQuery(document).ready(function() {
                                    jQuery("#billing_' . $filter_array['name'] . '").datepicker({
                                        dateFormat : "dd-mm-yy"
                                    });
                                });
                                </script>';
                    $fields['billing_' . $filter_array['name']] = array(
                        'label' => __($filter_array['label'], 'woocommerce'),
                        'placeholder' => _x($filter_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($filter_array['extra_class']),
                        'clear' => false,
                        'type' => 'text'
                    );
                    
                }else {         
                    if (isset($filter_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    $fields['billing_' . $filter_array['name']] = array(
                        'label' => __($filter_array['label'], 'woocommerce'),
                        'placeholder' => _x($filter_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($filter_array['extra_class']),
                        'clear' => false,
                        'type' => $filter_array['type']
                    );
                }
            }
        }
    }
    return $fields;
}

// Function for add costom shipping field
function rs_custom_shipping_fields($fields) {
    $options = get_option('wcfe_settings');
    $shipping_arrays = $options['shipping'];
    if ($shipping_arrays) {
        foreach ($shipping_arrays as $shipping_array) {
            if (array_filter($shipping_array)) {
                if ($shipping_array['type'] == 'select' || $shipping_array['type'] == 'radio') {
                    $single_attribute = explode(',', $shipping_array['attributes']);
                    $single_attribute = array_combine($single_attribute, $single_attribute);
                    if (isset($shipping_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    $fields['shipping_' . $shipping_array['name']] = array(
                        'label' => __($shipping_array['label'], 'woocommerce'),
                        'placeholder' => _x($shipping_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($shipping_array['extra_class']),
                        'clear' => false,
                        'type' => $shipping_array['type'],
                        'options' => $single_attribute
                    );
                } else if ($shipping_array['type'] == 'checkbox') {
                    if (isset($shipping_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    $fields['shipping_' . $shipping_array['name']] = array(
                        'label' => __($shipping_array['label'], 'woocommerce'),
                        'placeholder' => _x($shipping_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($shipping_array['extra_class']),
                        'clear' => false,
                        'type' => 'checkbox'
                    );
                }else if ($shipping_array['type'] == 'multiselect' ) {
                    $single_attribute = explode(',', $shipping_array['attributes']);
                    $single_attribute = array_combine($single_attribute, $single_attribute);
                    if (isset($shipping_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                  $fields['shipping_' . $shipping_array['name']] = array(
                        'label' => __($shipping_array['label'], 'woocommerce'),
                        'placeholder' => _x($shipping_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => FALSE,
                        'class' => array($shipping_array['extra_class']),
                        'clear' => false,
                        'type' => $shipping_array['type'],
                        'options' => $single_attribute,
                        'required_multiselect' => $required
                  );                    
                }else if ($shipping_array['type'] == 'date' ){                    
                    if (isset($shipping_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    echo '<script type="text/javascript">
                                jQuery(document).ready(function() {
                                    jQuery("#shipping_' . $shipping_array['name'] . '").datepicker({
                                        dateFormat : "dd-mm-yy"
                                    });
                                });
                                </script>';
                    $fields['shipping_' . $shipping_array['name']] = array(
                        'label' => __($shipping_array['label'], 'woocommerce'),
                        'placeholder' => _x($shipping_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($shipping_array['extra_class']),
                        'clear' => false,
                        'type' => 'text'
                    );
                    
                } else {
                    if (isset($shipping_array['required'])) {
                        $required = TRUE;
                    } else {
                        $required = FALSE;
                    }
                    $fields['shipping_' . $shipping_array['name']] = array(
                        'label' => __($shipping_array['label'], 'woocommerce'),
                        'placeholder' => _x($shipping_array['placeholder'], 'placeholder', 'woocommerce'),
                        'required' => $required,
                        'class' => array($shipping_array['extra_class']),
                        'clear' => false,
                        'type' => $shipping_array['type']
                    );
                }
            }
        }
    }
    return $fields;
}


/*  --------------multiple select handler------------------ */
add_filter( 'woocommerce_form_field_multiselect', 'custom_multiselect_handler', 10, 4 );
function custom_multiselect_handler( $field, $key, $args, $value ) {
    $options = '';
    if ( ! empty( $args['options'] ) ) {
        foreach ( $args['options'] as $option_key => $option_text ) {
            $options .= '<option value="' . $option_key . '" '. selected( $value, $option_key, false ) . '>' . $option_text .'</option>';
        }
        if ( $args['required_multiselect'] ) {
		$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce'  ) . '">*</abbr>';
	} else {
		$required = '';
	}
        if ( ( ! empty( $args['clear'] ) ) ) {
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
	}
        $field = '<p class="form-row ' . implode( ' ', $args['class'] ) .'" id="' . $key . '_field">
            <label for="' . $key . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label'].' '.$required .'</label>
            <select name="' . $key . '" id="' . $key . '" class="select" multiple="multiple">
                ' . $options . '
            </select>
        </p>' . $after;
    }
    return $field;
}
/*  --------------multiple select handler------------------ */
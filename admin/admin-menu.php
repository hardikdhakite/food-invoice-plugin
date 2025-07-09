<?php

add_action('admin_menu', function () {
    if (!current_user_can('manage_options')) return ;

    add_menu_page(
        'Create Invoice',
        'Food Invoices',
        'manage_options',
        'food-invoice-add',
        'food_invoice_add_page',
        'dashicons-media-spreadsheet',
        26
    );
    add_submenu_page(
        'food-invoice-add',
        'Create Invoice',
        'Create Invoice',
        'manage_options',
        'food-invoice-add',
        'food_invoice_add_page'
    );
    add_submenu_page(
        'food-invoice-add',
        'Invoice History',
        'Invoice History',
        'manage_options',
        'food-invoice-list',
        'food_invoice_list_page'
    );
    add_submenu_page(
        'food-invoice-add',
        'Settings',
        'Settings',
        'manage_options',
        'food-invoice-settings',
        'food_invoice_settings_page'
    );
});

function food_invoice_list_page()
{
    require_once plugin_dir_path(__FILE__) . 'invoice-list.php';
}

function food_invoice_add_page()
{
    require_once plugin_dir_path(__FILE__) . 'invoice-form.php';
}

function food_invoice_settings_page()
{
    require_once plugin_dir_path(__FILE__) . 'settings.php';
}

<?php

add_action('init', function() {
    register_post_type('food_invoice', [
        'labels' => [
            'name' => 'Food Invoices',
            'singular_name' => 'Food Invoice',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'supports' => ['title', 'custom-fields'],
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'manage_options',
        ],
        'map_meta_cap' => true,
    ]);
}); 
<?php
if (!current_user_can('manage_options')) wp_die('Unauthorized');
if (isset($_POST['clear_invoice_history']) && check_admin_referer('clear_invoice_history_action')) {
    $invoices = get_posts([
        'post_type' => 'food_invoice',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);
    foreach ($invoices as $id) {
        wp_delete_post($id, true);
    }
    echo '<div class="notice notice-success"><p>All invoice history has been cleared.</p></div>';
}
?>
<div class="wrap">
    <h1>Food Invoice Settings</h1>
    <form method="post">
        <?php wp_nonce_field('clear_invoice_history_action'); ?>
        <p><strong>Danger Zone:</strong> This will permanently delete all invoice history from the database. This action cannot be undone.</p>
        <p><input type="submit" name="clear_invoice_history" class="button button-danger" value="Clear All Invoice History" onclick="return confirm('Are you sure you want to delete all invoice history? This cannot be undone.');"></p>
    </form>
</div>
<style>
.button-danger { background: #dc2626 !important; color: #fff !important; border: none; }
.button-danger:hover, .button-danger:focus { background: #a61b1b !important; color: #fff !important; }
</style> 
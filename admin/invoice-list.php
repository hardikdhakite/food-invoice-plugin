<?php
if (!current_user_can('manage_options')) wp_die('Unauthorized');

if (isset($_POST['bulk_action']) && isset($_POST['invoice_ids']) && is_array($_POST['invoice_ids'])) {
    $action = $_POST['bulk_action'];
    $ids = array_map('intval', $_POST['invoice_ids']) ;
    if ($action === 'delete') {
        foreach ($ids as $id) {
            wp_delete_post($id, true);
        }
        echo '<div class="notice notice-success"><p>Selected invoices deleted.</p></div>';
    }
}

$search = isset($_GET['invoice_search']) ? sanitize_text_field($_GET['invoice_search']) : '';
$args = [
    'post_type' => 'food_invoice',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
];
if ($search) {
    $args['meta_query'] = [
        [
            'key' => 'client_name',
            'value' => $search,
            'compare' => 'LIKE',
        ]
    ];
}
$invoices = get_posts($args);
$site_url = get_site_url();
?>
<div class="wrap">
    <h1>Food Invoice History</h1>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <form method="post" id="bulk-invoice-form" style="margin:0;display:flex;align-items:center;gap:8px;">
            <select name="bulk_action" required>
                <option value="">Bulk Actions</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="button" onclick="return confirmBulkAction();">Apply</button>
        </form>
        <form method="get" style="margin:0;display:flex;align-items:center;gap:6px;">
            <input type="hidden" name="page" value="food-invoice-list">
            <input type="text" name="invoice_search" placeholder="Search by Client Name" value="<?= esc_attr($search) ?>" style="padding:4px 8px;">
            <button type="submit" class="button">Search</button>
            <?php if ($search): ?><a href="admin.php?page=food-invoice-list" class="button">Clear</a><?php endif; ?>
        </form>
    </div>
    <table class="widefat">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all-invoices"></th>
                <th>Client Name</th>
                <th>Date</th>
                <th>Function</th>
                <th>Venue</th>
                <th>Total (₹)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv):
                $items = get_post_meta($inv->ID, 'items', true);
                $total = 0;
                if ($items && is_array($items['qty'])) {
                    foreach ($items['qty'] as $i => $qty) {
                        $rate = floatval($items['rate'][$i]);
                        $discount = isset($items['discount'][$i]) && is_numeric($items['discount'][$i]) ? floatval($items['discount'][$i]) : 0;
                        $total += ($qty * $rate) - ($qty * $discount);
                    }
                }
                $view_url = $site_url . "/invoice/{$inv->ID}";
                $print_url = $view_url . "?print=1";
            ?>
                <tr>
                    <td><input type="checkbox" name="invoice_ids[]" value="<?= $inv->ID ?>"></td>
                    <td><?= esc_html(get_post_meta($inv->ID, 'client_name', true)) ?></td>
                    <td><?= esc_html(get_post_meta($inv->ID, 'event_date', true)) ?></td>
                    <td><?= esc_html(get_post_meta($inv->ID, 'function', true)) ?></td>
                    <td><?= esc_html(get_post_meta($inv->ID, 'venue', true)) ?></td>
                    <td><?= number_format($total, 2) ?></td>
                    <td>
                        <a href="<?= $view_url ?>" class="button" target="_blank">View</a>
                        <a href="<?= $print_url ?>" class="button" target="_blank">Print</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </form>
    <style>
        .bulk-checkbox,
        #select-all-invoices {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin: 0 auto;
            display: block;
        }

        th:first-child,
        td:first-child {
            text-align: center;
            width: 40px;
        }
    </style>
    <script>
        document.getElementById('select-all-invoices').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('input[name="invoice_ids[]"]').forEach(cb => cb.checked = checked);
        });
        document.getElementById('select-all-invoices').classList.add('bulk-checkbox');
        document.querySelectorAll('input[name="invoice_ids[]"]').forEach(cb => cb.classList.add('bulk-checkbox'));

        function confirmBulkAction() {
            const action = document.querySelector('select[name="bulk_action"]').value;
            if (action === 'delete') {
                return confirm('Are you sure you want to delete the selected invoices? This cannot be undone.');
            }
            return true;
        }
    </script>
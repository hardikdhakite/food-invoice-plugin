<?php
if (!current_user_can('manage_options')) wp_die('Unauthorized') ;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['food_invoice_nonce']) && wp_verify_nonce($_POST['food_invoice_nonce'], 'add_food_invoice')) {
    $event_date = !empty($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : date('Y-m-d');
    $title = sanitize_text_field($_POST['client_name']) . ' - ' . $event_date;
    $post_id = wp_insert_post([
        'post_type' => 'food_invoice',
        'post_title' => $title,
        'post_status' => 'publish',
    ]);
    if ($post_id) {
        $site_url = get_site_url();
        echo '<div class="notice notice-success"><p>Invoice created! <a href="' . $site_url . '/invoice/' . $post_id . '" target="_blank">View Invoice</a></p></div>';
        update_post_meta($post_id, 'client_name', sanitize_text_field($_POST['client_name']));
        update_post_meta($post_id, 'event_date', $event_date);
        update_post_meta($post_id, 'function', sanitize_text_field($_POST['function']));
        update_post_meta($post_id, 'venue', sanitize_text_field($_POST['venue']));
        update_post_meta($post_id, 'items', $_POST['items']);
        update_post_meta($post_id, 'advanced_payment', floatval($_POST['advanced_payment']));
    }
}
$today = date('Y-m-d');
?>
<div class="wrap">
    <h1>Add New Food Invoice</h1>
    <form method="post">
        <?php wp_nonce_field('add_food_invoice', 'food_invoice_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>Client Name</th>
                <td><input type="text" name="client_name" required></td>
            </tr>
            <tr>
                <th>Event Date</th>
                <td><input type="date" name="event_date" value="<?= esc_attr($today) ?>" required></td>
            </tr>
            <tr>
                <th>Function</th>
                <td><input type="text" name="function"></td>
            </tr>
            <tr>
                <th>Venue</th>
                <td><input type="text" name="venue"></td>
            </tr>
        </table>
        <h2>Items</h2>
        <table id="invoice-items" class="widefat">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Rate (₹)</th>
                    <th>Discount (₹/qty)</th>
                    <th>Amount (₹)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" onclick="addItemRow()" class="button button-primary" style="margin:10px 0 10px 0;">Add Item</button>
        <h2>Advanced Payment (₹)</h2>
        <input type="number" name="advanced_payment" id="advanced_payment" value="" min="0" step="0.01">
        <h2>Total: <span id="total_amount">0.00</span> ₹</h2>
        <h2>Final Amount Due: <span id="final_amount">0.00</span> ₹</h2>
        <p><input type="submit" class="button button-primary" value="Create Invoice"></p>
    </form>
</div>
<script>
    function addItemRow() {
        const tbody = document.querySelector('#invoice-items tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
        <td class="sno"></td>
        <td><input type="text" name="items[desc][]"></td>
        <td><input type="number" name="items[qty][]" min="1" value="1" required oninput="calcAmounts()"></td>
        <td><input type="number" name="items[rate][]" min="0" value="0" required oninput="calcAmounts()"></td>
        <td><input type="number" name="items[discount][]" min="0" value="" oninput="calcAmounts()"></td>
        <td class="item-amount">0.00</td>
        <td><button type="button" onclick="this.closest('tr').remove(); updateSno(); calcAmounts();">Remove</button></td>
    `;
        tbody.appendChild(row);
        updateSno();
    }

    function updateSno() {
        document.querySelectorAll('#invoice-items tbody tr').forEach((row, idx) => {
            row.querySelector('.sno').textContent = idx + 1;
        });
    }

    function calcAmounts() {
        let total = 0;
        document.querySelectorAll('#invoice-items tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('[name="items[qty][]"]').value) || 0;
            const rate = parseFloat(row.querySelector('[name="items[rate][]"]').value) || 0;
            const discount = parseFloat(row.querySelector('[name="items[discount][]"]').value) || 0;
            const amount = (qty * rate) - (qty * discount);
            row.querySelector('.item-amount').textContent = amount.toFixed(2);
            total += amount;
        });
        document.getElementById('total_amount').textContent = total.toFixed(2);
        const adv = parseFloat(document.getElementById('advanced_payment').value) || 0;
        document.getElementById('final_amount').textContent = (total - adv).toFixed(2);
    }
    document.getElementById('advanced_payment').addEventListener('input', calcAmounts);
</script>
<style>
    #invoice-items input {
        width: 100px;
    }

    #invoice-items th,
    #invoice-items td {
        text-align: center;
    }
</style>
<script>
    addItemRow();
</script>
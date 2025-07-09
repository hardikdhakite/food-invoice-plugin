<?php

add_action('admin_menu', function () {
    if (!current_user_can('manage_options')) return;
    add_submenu_page(null, 'View Invoice', 'View Invoice', 'manage_options', 'food-invoice-view', 'food_invoice_view_page');
});

add_action('init', function () {
    add_rewrite_rule('^invoice/([0-9]+)(?:/)?$', 'index.php?food_invoice_id=$matches[1]', 'top');
});
add_filter('query_vars', function ($vars) {
    $vars[] = 'food_invoice_id';
    return $vars;
});
add_action('template_redirect', function () {
    $id = get_query_var('food_invoice_id');
    if ($id) {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_die('You are not allowed to view this invoice.');
        }
        food_invoice_render_public($id);
        exit;
    }
});

function food_invoice_render_public($id)
{
    $post = get_post(intval($id)) ;
    if (!$post) {
        echo '<div class="notice notice-error"><p>Invoice not found.</p></div>';
        return;
    }
    $client = get_post_meta($post->ID, 'client_name', true);
    $date = get_post_meta($post->ID, 'event_date', true);
    $function = get_post_meta($post->ID, 'function', true);
    $venue = get_post_meta($post->ID, 'venue', true);
    $items = get_post_meta($post->ID, 'items', true);
    $adv = floatval(get_post_meta($post->ID, 'advanced_payment', true));
    $total = 0;
    $auto_print = isset($_GET['print']) && $_GET['print'] == '1';
    $show_discount = false;
    if ($items && is_array($items['discount'])) {
        foreach ($items['discount'] as $discount) {
            if (is_numeric($discount) && floatval($discount) > 0) {
                $show_discount = true;
                break;
            }
        }
    }
    $show_advanced = ($adv && floatval($adv) > 0);
    $logo_url = plugins_url('assets/logo.png', dirname(__FILE__));
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Invoice - <?= esc_html($client) ?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            html,
            body {
                height: 100%;
                margin: 0;
                padding: 0;
                background: #f5f5f5;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 15px;
            }

            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }

            #invoice-print-area {
                background: #fff;
                margin: auto;
                padding: 32px 48px 36px 48px;
                max-width: 900px;
                width: 100%;
                box-sizing: border-box;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
                display: flex;
                flex-direction: column;
                min-height: 90vh;
            }

            .invoice-header {
                text-align: center;
                font-weight: bold;
                font-size: 1.5em;
                margin-bottom: 0.2em;
                margin-top: 36px;
            }

            .invoice-subheader {
                text-align: center;
                font-size: 1em;
                margin-bottom: 6px;
            }

            .invoice-contact {
                text-align: center;
                font-size: 1em;
                margin-bottom: 6px;
            }

            .invoice-address {
                text-align: center;
                font-size: 1em;
                margin-bottom: 18px;
            }

            hr {
                border: none;
                border-top: 2px solid #222;
                margin: 16px 0 18px 0;
            }

            .invoice-meta {
                width: 100%;
                margin-bottom: 12px;
                font-size: 1em;
            }

            .invoice-meta td {
                padding: 2px 8px 2px 0;
            }

            table.invoice-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 18px;
            }

            table.invoice-table th,
            table.invoice-table td {
                border: 1px solid #222;
                padding: 6px 10px;
                text-align: center;
                font-size: 1em;
            }

            table.invoice-table th {
                background: #f2f2f2;
                font-weight: bold;
            }

            .totals-table {
                width: 100%;
                margin-top: 18px;
                font-size: 1em;
            }

            .totals-table td {
                padding: 4px 8px;
                text-align: right;
            }

            .signature {
                margin-top: auto;
                text-align: right;
                font-size: 1em;
                padding-top: 48px;
            }

            .invoice-header-row {
                display: block;
                position: relative;
                margin-top: 12px;
                margin-bottom: 0.4em;
                height: 80px;
            }

            .invoice-logo {
                width: 80px;
                height: 80px;
                object-fit: contain;
                position: absolute;
                left: 0;
                top: 18px;
            }

            .invoice-header-text {
                text-align: center;
                position: absolute;
                left: 0;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                margin: 0 auto;
            }

            @media print {

                html,
                body {
                    background: #fff !important;
                    height: auto;
                }

                body {
                    display: block;
                    margin: 0;
                }

                #invoice-print-area {
                    box-shadow: none;
                    margin: 0 auto;
                    padding-left: 48px;
                    padding-right: 48px;
                    max-width: 900px;
                    width: 100%;
                    min-height: 100vh;
                }

                #invoice-print-area button {
                    display: none !important;
                }

                .invoice-header {
                    margin-top: 36px !important;
                }

                .invoice-header-row {
                    margin-top: 12px !important;
                }
            }
        </style>
    </head>
    <body<?= $auto_print ? ' onload="window.print()"' : '' ?>>
        <div class="wrap" id="invoice-print-area">
            <div class="invoice-header-row">
                <img src="<?= esc_attr($logo_url) ?>" class="invoice-logo" alt="Logo">
                <div class="invoice-header-text">
                    <div class="invoice-header">The Celebrations Resort</div>
                </div>
            </div>
            <div class="invoice-subheader" style="margin-bottom:6px;">(Unit of Sagar Axis Hospitality LLP)</div>
            <div class="invoice-contact" style="margin-bottom:6px;"><b>Contact No. </b>8827722256, 9098662386</div>
            <div class="invoice-address" style="margin-bottom:18px;"><b>Add: </b>The Celebrations Resort Behind Maanshiva Complex, Near CI Heights, Kolar Road Bhopal</div>
            <div class="invoice-title">Invoice</div>
            <hr>
            <table class="invoice-meta">
                <tr>
                    <td><strong>Name:</strong> <?= esc_html($client) ?></td>
                    <td><strong>Date:</strong> <?= date('d/m/Y', strtotime($date)) ?></td>
                </tr>
                <tr>
                    <td><strong>Function:</strong> <?= esc_html($function) ?></td>
                    <td><strong>Venue:</strong> <?= esc_html($venue) ?></td>
                </tr>
            </table>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Rate (₹)</th><?php if ($show_discount): ?><th>Discount (₹/qty)</th><?php endif; ?><th>Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items && is_array($items['qty'])): foreach ($items['qty'] as $i => $qty):
                            $desc = esc_html($items['desc'][$i]);
                            $rate = floatval($items['rate'][$i]);
                            $discount = isset($items['discount'][$i]) && is_numeric($items['discount'][$i]) ? floatval($items['discount'][$i]) : 0;
                            $amount = ($qty * $rate) - ($qty * $discount);
                            $total += $amount;
                    ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td style="text-align:left;"> <?= $desc ?> </td>
                                <td><?= $qty ?></td>
                                <td><?= number_format($rate, 2) ?></td>
                                <?php if ($show_discount): ?><td><?= $discount > 0 ? number_format($discount, 2) : '' ?></td><?php endif; ?>
                                <td><?= number_format($amount, 2) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
            <table class="totals-table">
                <tr>
                    <td style="text-align:right;"><strong>Total Amount:</strong></td>
                    <td style="width:150px;text-align:right;">₹ <?= number_format($total, 2) ?></td>
                </tr>
                <?php if ($show_advanced): ?><tr>
                        <td style="text-align:right;"><strong>Advanced Payment:</strong></td>
                        <td style="width:150px;text-align:right;">₹ <?= number_format($adv, 2) ?></td>
                    </tr><?php endif; ?>
                <tr>
                    <td style="text-align:right;"><strong>Final Amount Due:</strong></td>
                    <td style="width:150px;text-align:right;"><strong>₹ <?= number_format($total - floatval($adv), 2) ?></strong></td>
                </tr>
            </table>
            <div class="signature">Authorised Signatory</div>
            <button onclick="window.print()" class="invoice-print-btn">🖨 Print Invoice</button>
        </div>
        <style>
            .invoice-print-btn {
                display: block;
                margin: 28px auto 0 auto;
                background: #2563eb;
                color: #fff;
                border: none;
                border-radius: 6px;
                padding: 10px 22px;
                font-size: 1.08em;
                font-weight: 600;
                box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
                cursor: pointer;
                transition: background 0.2s, box-shadow 0.2s;
                letter-spacing: 0.5px;
                width: auto;
                min-width: 0;
            }

            .invoice-print-btn:hover,
            .invoice-print-btn:focus {
                background: #1742a0;
                box-shadow: 0 4px 16px rgba(37, 99, 235, 0.15);
            }

            .invoice-title {
                text-align: center;
                font-size: 1.7em;
                font-weight: bold;
                margin-bottom: 0.5px;
                letter-spacing: 1px;
                font-family: Arial, Helvetica, sans-serif;
            }

            .invoice-header {
                text-align: center;
                font-weight: bold;
                font-size: 2.3em;
                margin-bottom: 0.4em;
                margin-top: 36px;
            }

            .invoice-subheader,
            .invoice-contact,
            .invoice-address {
                text-align: center;
                font-size: 1.18em;
                margin-bottom: 8px;
            }

            .invoice-subheader {
                font-size: 1.25em;
                text-decoration: underline;
                text-underline-offset: 4px;
                font-weight: 600;
            }
        </style>
        </body>

    </html>
<?php
}

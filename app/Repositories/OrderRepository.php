<?php

declare(strict_types=1);

namespace App\Repositories;

final class OrderRepository extends Repository
{
    /**
     * Get all orders for a user with their line items.
     *
     * @return list<array<string,mixed>>
     */
    public function forUser(int $userId): array
    {
        $orders = $this->fetchAll(
            'SELECT * FROM `orders`
             WHERE `user_id` = :uid
             ORDER BY `id` DESC',
            ['uid' => $userId]
        );

        if (empty($orders)) {
            return [];
        }

        $orderIds = array_column($orders, 'id');
        $inClause = implode(',', array_map('intval', $orderIds));

        $items = $this->fetchAll(
            "SELECT * FROM `order_items` WHERE `order_id` IN ({$inClause}) ORDER BY `id` ASC"
        );

        $itemsByOrder = [];
        foreach ($items as $item) {
            $itemsByOrder[$item['order_id']][] = $item;
        }

        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[$order['id']] ?? [];
        }
        unset($order);

        return $orders;
    }

    /**
     * Find a specific order with its line items.
     *
     * @return array<string,mixed>|null
     */
    public function findWithItems(int|string $orderIdentifier, int $userId): ?array
    {
        $field = is_numeric($orderIdentifier) ? 'id' : 'order_number';
        $order = $this->fetchOne(
            "SELECT * FROM `orders` WHERE `{$field}` = :oid AND `user_id` = :uid",
            ['oid' => $orderIdentifier, 'uid' => $userId]
        );

        if ($order === null) {
            return null;
        }

        $order['items'] = $this->fetchAll(
            'SELECT * FROM `order_items` WHERE `order_id` = :oid ORDER BY `id` ASC',
            ['oid' => $order['id']]
        );

        return $order;
    }

    /**
     * Create an order from customer cart items and shipping details.
     *
     * @param array<string,mixed> $shipping
     * @param list<array<string,mixed>> $items
     * @return array<string,mixed>
     */
    public function createFromCart(int $userId, array $items, array $shipping, string $paymentMethod = 'UPI'): array
    {
        return $this->transaction(function () use ($userId, $items, $shipping, $paymentMethod): array {
            $subtotal = 0.0;
            $totalPieces = 0;

            foreach ($items as $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (float) ($item['price'] ?? 0);
                $pcs = max(1, (int) ($item['pcs'] ?? 1));
                $subtotal += ($price * $qty);
                $totalPieces += ($pcs * $qty);
            }

            // Indian GST (18% standard across packaging: 9% CGST + 9% SGST intra-state)
            $isInterstate = ($shipping['state_code'] ?? '20') !== '20';
            $taxableValue = round($subtotal / 1.18, 2);
            $taxTotal = round($subtotal - $taxableValue, 2);

            $cgst = $isInterstate ? 0.00 : round($taxTotal / 2, 2);
            $sgst = $isInterstate ? 0.00 : round($taxTotal / 2, 2);
            $igst = $isInterstate ? $taxTotal : 0.00;
            $grandTotal = $subtotal;

            $uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $year = date('y');
            $nextYear = date('y', strtotime('+1 year'));
            $randomNum = mt_rand(1000, 9999);
            $orderNumber = "SK-{$year}{$nextYear}-00{$randomNum}";

            // Insert Order
            $this->run(
                'INSERT INTO `orders` (
                    `uuid`, `order_number`, `user_id`, `customer_group_id`,
                    `status`, `payment_status`, `fulfilment_status`, `channel`,
                    `seller_gstin`, `seller_state_code`, `place_of_supply`, `is_interstate`, `buyer_gstin`,
                    `items_subtotal`, `taxable_value`, `cgst_total`, `sgst_total`, `igst_total`, `tax_total`, `grand_total`,
                    `total_base_units`, `payment_method`, `shipping_method`, `customer_note`,
                    `placed_at`, `confirmed_at`
                ) VALUES (
                    :uuid, :order_number, :user_id, 1,
                    :status, :payment_status, :fulfilment_status, "web",
                    "20AAGCS1234F1Z5", "20", :place_of_supply, :is_interstate, :buyer_gstin,
                    :items_subtotal, :taxable_value, :cgst_total, :sgst_total, :igst_total, :tax_total, :grand_total,
                    :total_base_units, :payment_method, "Standard Express Ground", :customer_note,
                    NOW(), NOW()
                )',
                [
                    'uuid'               => $uuid,
                    'order_number'       => $orderNumber,
                    'user_id'            => $userId,
                    'status'             => 'out_for_delivery',
                    'payment_status'     => 'paid',
                    'fulfilment_status'  => 'fulfilled',
                    'place_of_supply'    => $shipping['state_code'] ?? '20',
                    'is_interstate'      => $isInterstate ? 1 : 0,
                    'buyer_gstin'        => $shipping['gstin'] ?? null,
                    'items_subtotal'     => $subtotal,
                    'taxable_value'      => $taxableValue,
                    'cgst_total'         => $cgst,
                    'sgst_total'         => $sgst,
                    'igst_total'         => $igst,
                    'tax_total'          => $taxTotal,
                    'grand_total'        => $grandTotal,
                    'total_base_units'   => $totalPieces,
                    'payment_method'     => $paymentMethod,
                    'customer_note'      => $shipping['landmark'] ?? 'Deliver to store address',
                ]
            );

            $orderId = (int) $this->pdo->lastInsertId();

            // Insert Order Line Items
            foreach ($items as $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (float) ($item['price'] ?? 0);
                $pcs = max(1, (int) ($item['pcs'] ?? 1));
                $lineSubtotal = $price * $qty;
                $lineTaxable = round($lineSubtotal / 1.18, 2);
                $lineTax = round($lineSubtotal - $lineTaxable, 2);

                $this->run(
                    'INSERT INTO `order_items` (
                        `order_id`, `sku`, `product_name`, `pack_label`, `pieces_per_pack`,
                        `image_path`, `pack_qty`, `base_qty`, `list_unit_price`, `unit_price`,
                        `pack_price`, `line_subtotal`, `taxable_value`, `hsn_code`, `gst_rate`,
                        `cgst_amount`, `sgst_amount`, `igst_amount`, `line_total`
                    ) VALUES (
                        :order_id, :sku, :product_name, :pack_label, :pieces_per_pack,
                        :image_path, :pack_qty, :base_qty, :list_unit_price, :unit_price,
                        :pack_price, :line_subtotal, :taxable_value, "48191000", 18.00,
                        :cgst_amount, :sgst_amount, :igst_amount, :line_total
                    )',
                    [
                        'order_id'        => $orderId,
                        'sku'             => (string) ($item['id'] ?? 'SKU-' . mt_rand(100, 999)),
                        'product_name'    => (string) ($item['name'] ?? 'Commercial Product'),
                        'pack_label'      => (string) ($item['pack'] ?? "Pack of {$pcs} pcs"),
                        'pieces_per_pack' => $pcs,
                        'image_path'      => (string) ($item['image'] ?? '/assets/images/placeholder/cups.svg'),
                        'pack_qty'        => $qty,
                        'base_qty'        => $pcs * $qty,
                        'list_unit_price' => $pcs > 0 ? $price / $pcs : $price,
                        'unit_price'      => $pcs > 0 ? $price / $pcs : $price,
                        'pack_price'      => $price,
                        'line_subtotal'   => $lineSubtotal,
                        'taxable_value'   => $lineTaxable,
                        'cgst_amount'     => $isInterstate ? 0.00 : round($lineTax / 2, 2),
                        'sgst_amount'     => $isInterstate ? 0.00 : round($lineTax / 2, 2),
                        'igst_amount'     => $isInterstate ? $lineTax : 0.00,
                        'line_total'      => $lineSubtotal,
                    ]
                );
            }

            return [
                'id'           => $orderId,
                'order_number' => $orderNumber,
                'grand_total'  => $grandTotal,
                'items_count'  => count($items),
            ];
        });
    }
}

<?php
declare(strict_types=1);

/**
 * Shop management helpers.
 *
 * Staff access is controlled by SHOP_ADMIN_EMAILS (comma/semicolon separated).
 * For local XAMPP only, storage/admin-emails.txt may be used as a private
 * fallback. That file is ignored by Git and must never be placed in public assets.
 */
function shop_admin_emails(): array
{
    $configured = trim((string)(getenv('SHOP_ADMIN_EMAILS') ?: ''));
    if ($configured === '') {
        $localFile = dirname(__DIR__, 2) . '/storage/admin-emails.txt';
        if (is_file($localFile) && is_readable($localFile)) {
            $configured = (string)file_get_contents($localFile);
        }
    }
    $values = preg_split('/[\s,;]+/', strtolower($configured), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $emails = [];
    foreach ($values as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[$email] = true;
        }
    }
    return array_keys($emails);
}

function shop_is_admin(array $user): bool
{
    $email = strtolower(trim((string)($user['email'] ?? '')));
    return $email !== '' && in_array($email, shop_admin_emails(), true);
}

function require_shop_admin(array $user): void
{
    if (!shop_is_admin($user)) {
        fail('This area is for authorised shop staff only.', 403);
    }
}

function shop_default_stock(): int
{
    return 12;
}

function shop_stock(array $store, string $productId): int
{
    $stock = $store['inventory'][$productId] ?? shop_default_stock();
    return max(0, min(5000, (int)$stock));
}

function shop_products_for_store(array $store): array
{
    $result = [];
    foreach (products() as $product) {
        $stock = shop_stock($store, (string)$product['id']);
        $product['stock'] = $stock;
        $product['available'] = $stock > 0;
        $product['stockLabel'] = $stock < 1 ? 'Sold out' : ($stock <= 3 ? 'Only ' . $stock . ' left' : 'In stock');
        $result[] = $product;
    }
    return $result;
}

function shop_favorites(array $store, string $userId): array
{
    $valid = array_column(products(), 'id');
    $saved = $store['wishlists'][$userId] ?? [];
    if (!is_array($saved)) {
        return [];
    }
    return array_values(array_unique(array_filter(
        $saved,
        static fn($id): bool => is_string($id) && in_array($id, $valid, true)
    )));
}

function shop_admin_dashboard_data(array $store): array
{
    $usersById = [];
    foreach ($store['users'] ?? [] as $user) {
        if (isset($user['id'])) {
            $usersById[$user['id']] = $user;
        }
    }

    $orders = array_values($store['orders'] ?? []);
    usort($orders, static fn(array $a, array $b): int => strcmp((string)($b['createdAt'] ?? ''), (string)($a['createdAt'] ?? '')));

    $openOrders = 0;
    $deliveredOrders = 0;
    $cancelledOrders = 0;
    $salesTotal = 0;
    foreach ($orders as &$order) {
        $status = (string)($order['status'] ?? 'Received');
        if ($status === 'Delivered') {
            $deliveredOrders++;
        } elseif ($status === 'Cancelled') {
            $cancelledOrders++;
        } else {
            $openOrders++;
        }
        if ($status !== 'Cancelled') {
            $salesTotal += (int)($order['total'] ?? 0);
        }
        $customer = $usersById[$order['userId'] ?? ''] ?? [];
        $order['customerName'] = (string)($customer['name'] ?? 'Customer');
        $order['customerEmail'] = (string)($customer['email'] ?? '');
    }
    unset($order);

    $products = shop_products_for_store($store);
    $lowStock = count(array_filter($products, static fn(array $product): bool => $product['stock'] <= 3));

    return [
        'summary' => [
            'orders' => count($orders),
            'openOrders' => $openOrders,
            'deliveredOrders' => $deliveredOrders,
            'cancelledOrders' => $cancelledOrders,
            'salesTotal' => $salesTotal,
            'lowStock' => $lowStock,
            'customers' => count($usersById),
        ],
        'orders' => array_slice($orders, 0, 100),
        'products' => $products,
    ];
}

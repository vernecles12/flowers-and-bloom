<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/lib/catalog.php';
require_once __DIR__ . '/lib/newsletter.php';
require_once __DIR__ . '/lib/management.php';

function is_vercel_runtime(): bool
{
    return getenv('VERCEL') === '1' || isset($_SERVER['VERCEL']);
}

function shop_storage_path(): string
{
    return dirname(__DIR__) . '/storage/shop.json';
}

function blob_credentials(): array
{
    // This PHP backend uses Vercel's Blob REST endpoints directly. Prefer the
    // store's configured read/write token; use the invocation OIDC token as a
    // fallback when a project has been migrated to OIDC-only access.
    $tokens = [];
    $candidates = [
        trim((string)(getenv('BLOB_READ_WRITE_TOKEN') ?: '')),
        trim((string)($_SERVER['HTTP_X_VERCEL_OIDC_TOKEN'] ?? '')),
        trim((string)(getenv('VERCEL_OIDC_TOKEN') ?: '')),
    ];
    foreach ($candidates as $candidate) {
        if ($candidate !== '' && !in_array($candidate, $tokens, true)) {
            $tokens[] = $candidate;
        }
    }

    $storeId = trim((string)(getenv('BLOB_STORE_ID') ?: ''));
    if (!$tokens || $storeId === '') {
        throw new RuntimeException('Private Blob credentials are not configured for this project.');
    }
    if (str_starts_with($storeId, 'store_')) {
        $storeId = substr($storeId, 6);
    }
    return [$tokens, $storeId];
}

function blob_request(string $method, string $url, array $headers = [], ?string $body = null): array
{
    if (!function_exists('curl_init')) {
        fail('Persistent storage transport is unavailable.', 503);
    }
    $responseHeaders = [];
    $handle = curl_init($url);
    $options = [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
            $length = strlen($line);
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
            return $length;
        },
    ];
    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = $body;
    }
    curl_setopt_array($handle, $options);
    $responseBody = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $failed = $responseBody === false;
    $curlError = $failed ? curl_errno($handle) : 0;
    unset($handle);
    if ($failed) {
        throw new RuntimeException('Blob request failed (cURL ' . $curlError . ').');
    }
    return ['status' => $status, 'headers' => $responseHeaders, 'body' => (string)$responseBody];
}

function blob_authenticated_request(string $method, string $url, array $headers, ?string $body, array $tokens): array
{
    $lastResult = null;
    foreach ($tokens as $token) {
        $authenticatedHeaders = array_values(array_filter(
            $headers,
            static fn(string $header): bool => stripos($header, 'Authorization:') !== 0
        ));
        $authenticatedHeaders[] = 'Authorization: Bearer ' . $token;
        $lastResult = blob_request($method, $url, $authenticatedHeaders, $body);

        // A project can still be completing an OIDC migration. Try the next
        // configured credential only for an authentication/authorization error.
        if (!in_array($lastResult['status'], [401, 403], true)) {
            return $lastResult;
        }
    }
    return $lastResult ?? ['status' => 503, 'headers' => [], 'body' => 'No Blob credential was available.'];
}

function blob_object_path(string $path): string
{
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}

function blob_read_object(string $path): array
{
    [$tokens, $storeId] = blob_credentials();
    $url = 'https://' . $storeId . '.private.blob.vercel-storage.com/' . blob_object_path($path) . '?cache=0';
    $result = blob_authenticated_request('GET', $url, [], null, $tokens);
    if ($result['status'] === 404) {
        return ['found' => false, 'body' => '', 'etag' => null];
    }
    if ($result['status'] !== 200) {
        $details = json_decode($result['body'], true);
        $reason = is_array($details) ? ($details['error']['message'] ?? $details['message'] ?? '') : '';
        error_log('Flowers and Bloom Blob GET HTTP ' . $result['status'] . ': ' . substr((string)$reason, 0, 240));
        throw new RuntimeException(
            $result['status'] === 401 || $result['status'] === 403
                ? 'Private Blob access was denied. Check that this project uses a token for its configured Blob store.'
                : 'Blob read failed with HTTP ' . $result['status'] . '.'
        );
    }
    return ['found' => true, 'body' => $result['body'], 'etag' => $result['headers']['etag'] ?? null];
}

function blob_write_object(string $path, string $body, ?string $etag = null, bool $allowOverwrite = false, int $ttlDays = 0): array
{
    [$tokens, $storeId] = blob_credentials();
    $query = http_build_query(['pathname' => $path], '', '&', PHP_QUERY_RFC3986);
    $requestId = $storeId . ':' . (string)round(microtime(true) * 1000) . ':' . bin2hex(random_bytes(6));
    $headers = [
        'x-api-version: 13',
        'x-api-blob-request-id: ' . $requestId,
        'x-vercel-blob-store-id: ' . $storeId,
        'x-api-blob-request-attempt: 0',
        'x-content-length: ' . strlen($body),
        'x-content-type: application/json',
        'Content-Type: application/json',
        'x-vercel-blob-access: private',
        'x-allow-overwrite: ' . (($etag !== null || $allowOverwrite) ? '1' : '0'),
    ];
    if ($etag !== null) {
        $headers[] = 'x-if-match: ' . $etag;
    }
    if ($ttlDays > 0) {
        $headers[] = 'x-ttl-days: ' . $ttlDays;
    }
    return blob_authenticated_request('PUT', 'https://blob.vercel-storage.com/?' . $query, $headers, $body, $tokens);
}

function empty_shop_store(): array
{
    return [
        'users' => [],
        'carts' => [],
        'orders' => [],
        'newsletter' => [],
        'wishlists' => [],
        'inventory' => [],
    ];
}

function vercel_shop_store_snapshot(): array
{
    try {
        $snapshot = blob_read_object('flowers-and-bloom/shop.json');
    } catch (Throwable $error) {
        error_log('Flowers and Bloom private shop storage read failed: ' . $error->getMessage());
        fail('The shop could not reach its private storage. Please try again in a moment.', 503);
    }
    if (!$snapshot['found']) {
        return ['store' => empty_shop_store(), 'etag' => null];
    }
    $store = json_decode($snapshot['body'], true);
    if (!is_array($store)) {
        fail('Persistent shop storage contains invalid data.', 500);
    }
    return ['store' => array_merge(empty_shop_store(), $store), 'etag' => $snapshot['etag']];
}

final class VercelBlobSessionHandler implements SessionHandlerInterface
{
    private array $etags = [];
    private bool $readFailed = false;

    public function hasReadFailure(): bool { return $this->readFailed; }
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }

    private function pathFor(string $id): string
    {
        return 'flowers-and-bloom/sessions/' . hash('sha256', $id) . '.json';
    }

    public function read(string $id): string|false
    {
        $this->readFailed = false;
        try {
            $snapshot = blob_read_object($this->pathFor($id));
        } catch (Throwable $error) {
            $this->readFailed = true;
            error_log('Flowers and Bloom session read failed: ' . $error->getMessage());
            return false;
        }
        if (!$snapshot['found']) {
            $this->etags[$id] = null;
            return '';
        }
        $this->etags[$id] = $snapshot['etag'];
        return $snapshot['body'];
    }

    public function write(string $id, string $data): bool
    {
        $etag = $this->etags[$id] ?? null;
        try {
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $result = blob_write_object($this->pathFor($id), $data, $etag, $etag === null, 14);
                if ($result['status'] >= 200 && $result['status'] < 300) {
                    $this->etags[$id] = $result['headers']['etag'] ?? null;
                    return true;
                }
                if ($result['status'] !== 412) {
                    return false;
                }
                $latest = blob_read_object($this->pathFor($id));
                $etag = $latest['etag'];
            }
        } catch (Throwable $error) {
            error_log('Flowers and Bloom session write: ' . $error->getMessage());
            return false;
        }
        return false;
    }

    public function destroy(string $id): bool
    {
        try {
            $result = blob_write_object($this->pathFor($id), '', null, true, 1);
            return $result['status'] >= 200 && $result['status'] < 300;
        } catch (Throwable $error) {
            error_log('Flowers and Bloom session destroy: ' . $error->getMessage());
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false { return 0; }
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$skipSessionStart = $action === 'catalog' && $method === 'GET';

$blobSessionHandler = null;
if (is_vercel_runtime() && !$skipSessionStart) {
    ini_set('display_errors', '0');
    $blobSessionHandler = new VercelBlobSessionHandler();
    session_set_save_handler($blobSessionHandler, true);
}
session_set_cookie_params([
    'httponly' => true,
    'secure' => is_vercel_runtime() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'samesite' => 'Lax',
]);
if (!$skipSessionStart) {
    $sessionStarted = @session_start();
    if (!$sessionStarted || ($blobSessionHandler !== null && $blobSessionHandler->hasReadFailure())) {
        error_log('Flowers and Bloom could not restore the private PHP session.');
        http_response_code(503);
        echo json_encode([
            'ok' => false,
            'message' => 'Your secure shop session could not be restored. Please refresh and try again.',
        ]);
        exit;
    }
}

function respond(array $payload, int $status = 200): never
{
    if (session_status() === PHP_SESSION_ACTIVE && !@session_write_close()) {
        http_response_code(503);
        echo json_encode(['ok' => false, 'message' => 'Your session could not be saved. Please try again.']);
        exit;
    }
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $message, int $status = 400): never
{
    respond(['ok' => false, 'message' => $message], $status);
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function store_read(): array
{
    if (is_vercel_runtime()) {
        return vercel_shop_store_snapshot()['store'];
    }
    $handle = fopen(shop_storage_path(), 'c+');
    if (!$handle) {
        fail('The shop storage could not be opened.', 500);
    }
    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    $store = json_decode($contents ?: '', true);
    return is_array($store) ? array_merge(empty_shop_store(), $store) : empty_shop_store();
}

function store_update(callable $callback): mixed
{
    if (is_vercel_runtime()) {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $snapshot = vercel_shop_store_snapshot();
            $store = $snapshot['store'];
            $result = $callback($store);
            $json = json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                fail('Shop data could not be saved.', 500);
            }
            $write = blob_write_object('flowers-and-bloom/shop.json', $json, $snapshot['etag']);
            if ($write['status'] >= 200 && $write['status'] < 300) {
                return $result;
            }
            if ($write['status'] !== 412) {
                $details = json_decode($write['body'], true);
                $reason = is_array($details) ? ($details['error']['message'] ?? $details['message'] ?? '') : '';
                error_log('Flowers and Bloom Blob PUT HTTP ' . $write['status'] . ': ' . substr((string)$reason, 0, 240));
                fail('Your shop data could not be saved. Please try again in a moment.', 503);
            }
        }
        fail('The shop is busy right now. Please try again.', 503);
    }
    $handle = fopen(shop_storage_path(), 'c+');
    if (!$handle) {
        fail('The shop storage could not be opened.', 500);
    }
    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        fail('The shop storage is busy. Please try again.', 503);
    }
    rewind($handle);
    $contents = stream_get_contents($handle);
    $store = json_decode($contents ?: '', true);
    if (!is_array($store)) {
        $store = empty_shop_store();
    }
    $store = array_merge(empty_shop_store(), $store);
    $result = $callback($store);
    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $result;
}

function public_user(array $user): array
{
    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'createdAt' => $user['createdAt'],
        'profileImage' => $user['profileImage'] ?? null,
        'isAdmin' => shop_is_admin($user),
    ];
}

function current_user_from_store(array $store): ?array
{
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        return null;
    }
    foreach ($store['users'] as $user) {
        if (($user['id'] ?? null) === $id) {
            return $user;
        }
    }
    unset($_SESSION['user_id']);
    return null;
}

function require_user(array $store): array
{
    $user = current_user_from_store($store);
    if (!$user) {
        fail('Please sign in to continue.', 401);
    }
    return $user;
}

function cart_summary(array $store, string $userId): array
{
    $items = [];
    $total = 0;
    foreach (($store['carts'][$userId] ?? []) as $id => $quantity) {
        foreach (shop_products_for_store($store) as $product) {
            if ($product['id'] === $id && (int)$quantity > 0) {
                $product['quantity'] = (int)$quantity;
                $product['lineTotal'] = $product['price'] * $product['quantity'];
                $total += $product['lineTotal'];
                $items[] = $product;
                break;
            }
        }
    }
    return ['items' => $items, 'total' => $total, 'count' => array_sum(array_column($items, 'quantity'))];
}

$rawBody = file_get_contents('php://input') ?: '';
$input = json_decode($rawBody ?: '[]', true);
$input = is_array($input) ? $input : [];

// The product catalogue is public and does not depend on account storage.
// This keeps flower cards available if the private account store is briefly unavailable.
if ($action === 'catalog' && $method === 'GET') {
    respond(['ok' => true, 'products' => products()]);
}

if ($action === 'state' && $method === 'GET') {
    $store = store_read();
    $user = current_user_from_store($store);
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    $cart = $user ? cart_summary($store, $user['id']) : ['items' => [], 'total' => 0, 'count' => 0];
    $orders = $user ? array_values(array_filter($store['orders'], fn($order) => $order['userId'] === $user['id'])) : [];
    foreach ($orders as &$customerOrder) {
        if (isset($customerOrder['statusHistory']) && is_array($customerOrder['statusHistory'])) {
            foreach ($customerOrder['statusHistory'] as &$historyEntry) {
                if (isset($historyEntry['updatedBy'])) {
                    $historyEntry['updatedBy'] = 'Shop team';
                }
            }
            unset($historyEntry);
        }
    }
    unset($customerOrder);
    respond([
        'ok' => true,
        'csrf' => $_SESSION['csrf'],
        'user' => $user ? public_user($user) : null,
        'products' => shop_products_for_store($store),
        'cart' => $cart,
        'orders' => array_reverse($orders),
        'favorites' => $user ? shop_favorites($store, $user['id']) : [],
    ]);
}

if ($action === 'admin_dashboard' && $method === 'GET') {
    $adminStore = store_read();
    $adminUser = require_user($adminStore);
    require_shop_admin($adminUser);
    respond(['ok' => true] + shop_admin_dashboard_data($adminStore));
}

if ($method !== 'POST') {
    fail('Unsupported request.', 405);
}
if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)($input['csrf'] ?? ''))) {
    fail('Your session has expired. Refresh the page and try again.', 419);
}

if ($action === 'newsletter_subscribe') {
    $email = strtolower(trim((string)($input['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || text_length($email) > 254) {
        fail('Please enter a valid email address.');
    }
    $added = save_newsletter_subscription($email);
    respond([
        'ok' => true,
        'message' => $added ? 'You’re on the list. We’ll send the occasional studio note your way.' : 'You’re already on the list. We’ll be in touch when there’s something lovely to share.',
    ]);
}

if ($action === 'register') {
    $name = trim((string)($input['name'] ?? ''));
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $password = (string)($input['password'] ?? '');
    if (text_length($name) < 2 || text_length($name) > 70) {
        fail('Please enter a name between 2 and 70 characters.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || text_length($email) > 254) {
        fail('Please enter a valid email address.');
    }
    if (strlen($password) < 8) {
        fail('Choose a password with at least 8 characters.');
    }

    // If a previous registration response was lost after the account was
    // committed, retrying the same email/password signs that customer in
    // instead of creating a duplicate or trapping them at the registration form.
    $registration = store_update(function (&$store) use ($name, $email, $password) {
        foreach ($store['users'] as $index => $existing) {
            if (strtolower((string)($existing['email'] ?? '')) !== $email) {
                continue;
            }
            if (!password_verify($password, (string)($existing['password'] ?? ''))) {
                return ['error' => 'An account already exists for this email. Sign in or reset its password.'];
            }
            $welcomeType = !empty($store['users'][$index]['hasLoggedIn']) ? 'back' : 'first';
            $store['users'][$index]['hasLoggedIn'] = true;
            return [
                'user' => $store['users'][$index],
                'welcomeType' => $welcomeType,
                'created' => false,
            ];
        }

        $user = [
            'id' => bin2hex(random_bytes(12)),
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'hasLoggedIn' => true,
            'createdAt' => date(DATE_ATOM),
            'reset' => null,
        ];
        $store['users'][] = $user;
        return ['user' => $user, 'welcomeType' => 'first', 'created' => true];
    });

    if (isset($registration['error'])) {
        fail($registration['error'], 409);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $registration['user']['id'];
    $authenticatedStore = store_read();
    $orders = array_values(array_filter(
        $authenticatedStore['orders'],
        fn($order) => $order['userId'] === $registration['user']['id']
    ));

    respond([
        'ok' => true,
        'created' => $registration['created'],
        'message' => $registration['created'] ? 'Your account is ready.' : 'Welcome back. You are signed in.',
        'user' => public_user($registration['user']),
        'welcomeType' => $registration['welcomeType'],
        'cart' => cart_summary($authenticatedStore, $registration['user']['id']),
        'orders' => array_reverse($orders),
        'favorites' => shop_favorites($authenticatedStore, $registration['user']['id']),
    ]);
}

if ($action === 'login') {
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $password = (string)($input['password'] ?? '');
    $result = store_update(function (&$store) use ($email, $password) {
        foreach ($store['users'] as &$user) {
            if ($user['email'] === $email) {
                if (!password_verify($password, $user['password'])) {
                    return ['error' => 'That email and password don’t match.'];
                }
                $welcomeType = !empty($user['hasLoggedIn']) ? 'back' : 'first';
                $user['hasLoggedIn'] = true;
                return ['user' => $user, 'welcomeType' => $welcomeType];
            }
        }
        return ['error' => 'That email and password don’t match.'];
    });
    if (isset($result['error'])) {
        fail($result['error'], 401);
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $result['user']['id'];
    $authenticatedStore = store_read();
    $orders = array_values(array_filter($authenticatedStore['orders'], fn($order) => $order['userId'] === $result['user']['id']));
    respond([
        'ok' => true,
        'user' => public_user($result['user']),
        'welcomeType' => $result['welcomeType'],
        'cart' => cart_summary($authenticatedStore, $result['user']['id']),
        'orders' => array_reverse($orders),
        'favorites' => shop_favorites($authenticatedStore, $result['user']['id']),
    ]);
}

if ($action === 'request_reset') {
    $email = strtolower(trim((string)($input['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail('Please enter a valid email address.');
    }
    $code = (string)random_int(100000, 999999);
    $found = store_update(function (&$store) use ($email, $code) {
        foreach ($store['users'] as &$user) {
            if ($user['email'] === $email) {
                $user['reset'] = ['hash' => password_hash($code, PASSWORD_DEFAULT), 'expires' => time() + 600];
                return true;
            }
        }
        return false;
    });
    if (!$found) {
        fail('No account was found for that email address.', 404);
    }
    $subject = 'Your Flowers and Bloom password reset code';
    $body = "Your password reset code is {$code}.\n\nIt expires in 10 minutes. If you did not request this, you can ignore this email.";
    $headers = "From: Flowers and Bloom <no-reply@localhost>\r\nContent-Type: text/plain; charset=UTF-8";
    $emailSent = function_exists('mail') && @mail($email, $subject, $body, $headers);
    respond(['ok' => true, 'demoCode' => $code, 'emailSent' => (bool)$emailSent, 'message' => 'Your one-time reset code is ready.']);
}

if ($action === 'reset_password') {
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $code = trim((string)($input['code'] ?? ''));
    $password = (string)($input['password'] ?? '');
    if (strlen($password) < 8) {
        fail('Choose a new password with at least 8 characters.');
    }
    $result = store_update(function (&$store) use ($email, $code, $password) {
        foreach ($store['users'] as &$user) {
            if ($user['email'] === $email) {
                $reset = $user['reset'] ?? null;
                if (!$reset || $reset['expires'] < time() || !password_verify($code, $reset['hash'])) {
                    return ['error' => 'That code is incorrect or has expired. Request a fresh one and try again.'];
                }
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                $user['reset'] = null;
                return ['ok' => true];
            }
        }
        return ['error' => 'No account was found for that email address.'];
    });
    if (isset($result['error'])) {
        fail($result['error'], 400);
    }
    respond(['ok' => true, 'message' => 'Your password has been updated. You can sign in now.']);
}

if ($action === 'logout') {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    respond(['ok' => true, 'csrf' => $_SESSION['csrf']]);
}

$store = store_read();
$user = require_user($store);
$userId = $user['id'];

if ($action === 'toggle_favorite') {
    $productId = (string)($input['productId'] ?? '');
    $validProductIds = array_column(products(), 'id');
    if (!in_array($productId, $validProductIds, true)) {
        fail('That flower could not be found.', 404);
    }
    $favorites = store_update(function (&$store) use ($userId, $productId) {
        $saved = shop_favorites($store, $userId);
        if (in_array($productId, $saved, true)) {
            $saved = array_values(array_filter($saved, static fn(string $id): bool => $id !== $productId));
        } else {
            $saved[] = $productId;
        }
        $store['wishlists'][$userId] = $saved;
        return $saved;
    });
    respond(['ok' => true, 'favorites' => $favorites]);
}

if ($action === 'admin_update_order_status') {
    require_shop_admin($user);
    $orderId = trim((string)($input['orderId'] ?? ''));
    $nextStatus = trim((string)($input['status'] ?? ''));
    $transitions = [
        'Awaiting manual payment' => ['Confirmed', 'Cancelled'],
        'Received' => ['Confirmed', 'Preparing', 'Cancelled'],
        'Confirmed' => ['Preparing', 'Cancelled'],
        'Preparing' => ['Out for delivery', 'Cancelled'],
        'Out for delivery' => ['Delivered'],
        'Delivered' => [],
        'Cancelled' => [],
    ];
    if (!isset($transitions[$nextStatus]) && $nextStatus !== 'Awaiting manual payment') {
        fail('Choose a valid order status.');
    }
    $updated = store_update(function (&$store) use ($orderId, $nextStatus, $transitions, $user) {
        foreach ($store['orders'] as &$order) {
            if (($order['id'] ?? '') !== $orderId) {
                continue;
            }
            $currentStatus = (string)($order['status'] ?? 'Received');
            if (!in_array($nextStatus, $transitions[$currentStatus] ?? [], true)) {
                return ['error' => 'That status change is not allowed from the current order stage.'];
            }
            if ($nextStatus === 'Cancelled' && !empty($order['inventoryReserved'])) {
                // Return stock only for orders created after inventory tracking
                // was enabled. Older orders never reserved stock at checkout.
                foreach (($order['items'] ?? []) as $item) {
                    $productId = (string)($item['id'] ?? '');
                    $quantity = max(0, (int)($item['quantity'] ?? 0));
                    if ($productId !== '' && $quantity > 0) {
                        $store['inventory'][$productId] = min(5000, shop_stock($store, $productId) + $quantity);
                    }
                }
                $order['inventoryReserved'] = false;
            }
            $order['status'] = $nextStatus;
            if ($nextStatus === 'Confirmed' && str_contains(strtolower((string)($order['paymentMethod'] ?? '')), 'gcash')) {
                $order['paymentStatus'] = 'Payment confirmed manually';
            }
            if (!isset($order['statusHistory']) || !is_array($order['statusHistory'])) {
                $order['statusHistory'] = [['status' => $currentStatus, 'at' => $order['createdAt'] ?? date(DATE_ATOM), 'note' => 'Order placed']];
            }
            $order['statusHistory'][] = [
                'status' => $nextStatus,
                'at' => date(DATE_ATOM),
                'note' => 'Updated by shop staff',
                'updatedBy' => 'Shop team',
            ];
            $order['updatedAt'] = date(DATE_ATOM);
            return ['order' => $order];
        }
        return ['error' => 'That order could not be found.'];
    });
    if (isset($updated['error'])) {
        fail($updated['error'], 409);
    }
    respond(['ok' => true, 'order' => $updated['order'], 'message' => 'Order status updated.']);
}

if ($action === 'admin_update_inventory') {
    require_shop_admin($user);
    $productId = trim((string)($input['productId'] ?? ''));
    $quantity = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT);
    $validProductIds = array_column(products(), 'id');
    if (!in_array($productId, $validProductIds, true) || $quantity === false || $quantity < 0 || $quantity > 5000) {
        fail('Choose a valid flower and a stock number between 0 and 5,000.');
    }
    store_update(function (&$store) use ($productId, $quantity) {
        $store['inventory'][$productId] = $quantity;
        return true;
    });
    $latest = store_read();
    $product = null;
    foreach (shop_products_for_store($latest) as $entry) {
        if ($entry['id'] === $productId) {
            $product = $entry;
            break;
        }
    }
    respond(['ok' => true, 'product' => $product, 'message' => 'Inventory updated.']);
}

if ($action === 'add_to_cart' || $action === 'update_cart' || $action === 'remove_from_cart') {
    $id = (string)($input['productId'] ?? '');
    $productExists = false;
    foreach (products() as $product) {
        if ($product['id'] === $id) {
            $productExists = true;
            break;
        }
    }
    if (!$productExists) {
        fail('That flower is no longer available.', 404);
    }
    $quantity = (int)($input['quantity'] ?? 1);
    $result = store_update(function (&$store) use ($userId, $id, $quantity, $action) {
        if (!isset($store['carts'][$userId])) {
            $store['carts'][$userId] = [];
        }
        if ($action === 'remove_from_cart' || $quantity <= 0) {
            unset($store['carts'][$userId][$id]);
            return ['ok' => true];
        }
        $nextQuantity = min(20, max(1, $action === 'add_to_cart' ? (int)($store['carts'][$userId][$id] ?? 0) + 1 : $quantity));
        $available = shop_stock($store, $id);
        if ($nextQuantity > $available) {
            return ['error' => $available < 1 ? 'This bouquet is currently sold out.' : 'Only ' . $available . ' of this bouquet are available.'];
        }
        $store['carts'][$userId][$id] = $nextQuantity;
        return ['ok' => true];
    });
    if (isset($result['error'])) {
        fail($result['error'], 409);
    }
    $store = store_read();
    respond(['ok' => true, 'cart' => cart_summary($store, $userId)]);
}

if ($action === 'update_profile') {
    $name = trim((string)($input['name'] ?? ''));
    $currentPassword = (string)($input['currentPassword'] ?? '');
    $newPassword = (string)($input['newPassword'] ?? '');
    $profileImage = $input['profileImage'] ?? null;
    $removeProfileImage = !empty($input['removeProfileImage']);
    if (text_length($name) < 2 || text_length($name) > 70) {
        fail('Please enter a name between 2 and 70 characters.');
    }
    if ($newPassword !== '' && strlen($newPassword) < 8) {
        fail('Your new password needs at least 8 characters.');
    }
    if ($profileImage !== null) {
        if (!is_string($profileImage) || strlen($profileImage) > 850000 || !preg_match('#^data:image/(jpeg|png|webp);base64,([A-Za-z0-9+/]+={0,2})$#', $profileImage, $imageParts)) {
            fail('Choose a JPG, PNG, or WebP image under 600 KB.');
        }
        if (!function_exists('getimagesizefromstring')) {
            fail('Photo uploads are not available on this server yet.', 503);
        }
        $imageBytes = base64_decode($imageParts[2], true);
        $imageInfo = $imageBytes !== false ? @getimagesizefromstring($imageBytes) : false;
        if ($imageBytes === false || strlen($imageBytes) > 600000 || !$imageInfo || (int)$imageInfo[0] > 6000 || (int)$imageInfo[1] > 6000 || !in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)) {
            fail('That photo could not be read. Please choose a JPG, PNG, or WebP image under 600 KB.');
        }
    }
    $result = store_update(function (&$store) use ($userId, $name, $currentPassword, $newPassword, $profileImage, $removeProfileImage) {
        foreach ($store['users'] as &$entry) {
            if ($entry['id'] === $userId) {
                if ($newPassword !== '') {
                    if (!password_verify($currentPassword, $entry['password'])) {
                        return ['error' => 'Your current password is not correct.'];
                    }
                    $entry['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                $entry['name'] = $name;
                if (is_string($profileImage)) $entry['profileImage'] = $profileImage;
                elseif ($removeProfileImage) $entry['profileImage'] = null;
                return ['user' => $entry];
            }
        }
        return ['error' => 'Please sign in again.'];
    });
    if (isset($result['error'])) {
        fail($result['error']);
    }
    respond(['ok' => true, 'user' => public_user($result['user']), 'message' => 'Your profile has been saved.']);
}

if ($action === 'checkout') {
    $recipient = trim((string)($input['recipient'] ?? ''));
    $address = trim((string)($input['address'] ?? ''));
    $phone = trim((string)($input['phone'] ?? ''));
    $paymentMethod = (string)($input['paymentMethod'] ?? '');
    if (text_length($recipient) < 2 || text_length($address) < 8 || text_length($phone) < 7) {
        fail('Please fill in your recipient, delivery address and contact number.');
    }
    if (!in_array($paymentMethod, ['cod', 'gcash', 'card'], true)) {
        fail('Choose a payment method to continue.');
    }
    $snapshotSummary = cart_summary($store, $userId);
    if (!$snapshotSummary['items']) {
        fail('Your cart is empty. Add a bouquet before checking out.');
    }
    $orderId = 'FB-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $paymentName = ['cod' => 'Cash on delivery', 'gcash' => 'GCash transfer (manual)', 'card' => 'Debit card at delivery'][$paymentMethod];
    $paymentStatus = ['cod' => 'Pay on delivery', 'gcash' => 'Payment not made', 'card' => 'Due at delivery'][$paymentMethod];
    $initialStatus = $paymentMethod === 'gcash' ? 'Awaiting manual payment' : 'Received';
    $result = store_update(function (&$store) use ($userId, $recipient, $address, $phone, $paymentMethod, $paymentName, $paymentStatus, $orderId, $snapshotSummary, $initialStatus) {
        $summary = cart_summary($store, $userId);
        if (!$summary['items'] || $summary['total'] !== $snapshotSummary['total'] || $summary['count'] !== $snapshotSummary['count']) {
            return ['error' => 'Your cart changed while placing the order. Please review it and try again.'];
        }
        foreach ($summary['items'] as $item) {
            $available = shop_stock($store, (string)$item['id']);
            if ((int)$item['quantity'] > $available) {
                return ['error' => 'Stock changed while checking out. Only ' . $available . ' of ' . $item['name'] . ' are available. Please review your cart.'];
            }
        }
        $now = date(DATE_ATOM);
        $order = [
            'id' => $orderId,
            'userId' => $userId,
            'items' => $summary['items'],
            'total' => $summary['total'],
            'recipient' => $recipient,
            'address' => $address,
            'phone' => $phone,
            'paymentMethod' => $paymentName,
            'paymentStatus' => $paymentStatus,
            'status' => $initialStatus,
            'inventoryReserved' => true,
            'statusHistory' => [['status' => $initialStatus, 'at' => $now, 'note' => 'Order placed']],
            'createdAt' => $now,
            'updatedAt' => $now,
        ];
        foreach ($summary['items'] as $item) {
            $productId = (string)$item['id'];
            $store['inventory'][$productId] = shop_stock($store, $productId) - (int)$item['quantity'];
        }
        $store['orders'][] = $order;
        $store['carts'][$userId] = [];
        return ['order' => $order];
    });
    if (isset($result['error'])) {
        fail($result['error'], 409);
    }
    respond([
        'ok' => true,
        'order' => $result['order'],
        'cart' => ['items' => [], 'total' => 0, 'count' => 0],
        'message' => 'Your order and payment preference have been saved. No payment was taken.',
    ]);
}

fail('That shop action was not found.', 404);


<?php
declare(strict_types=1);

function save_newsletter_subscription(string $email): bool
{
    return store_update(function (&$store) use ($email): bool {
        foreach (($store['newsletter'] ?? []) as $entry) {
            if (($entry['email'] ?? '') === $email) {
                return false;
            }
        }

        $store['newsletter'][] = ['email' => $email, 'subscribedAt' => date(DATE_ATOM)];
        return true;
    });
}

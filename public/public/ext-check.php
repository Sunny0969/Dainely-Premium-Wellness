<?php
/**
 * Temporary hosting check — upload to public/ (or site docroot), open in browser, then DELETE.
 * Does not print secrets or connect to databases.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

echo json_encode([
    'php_version' => PHP_VERSION,
    'sapi' => PHP_SAPI,
    'pdo_pgsql' => extension_loaded('pdo_pgsql'),
    'pgsql' => extension_loaded('pgsql'),
    'ok_for_supabase_admin' => extension_loaded('pdo_pgsql'),
    'message' => extension_loaded('pdo_pgsql')
        ? 'pdo_pgsql is enabled — Admin CMS can talk to Supabase.'
        : 'pdo_pgsql is MISSING — ask hosting to enable pgsql + pdo_pgsql for this PHP version, then delete this file.',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

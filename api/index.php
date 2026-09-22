<?php

// Vercel Serverless Function Entry Point for CodeIgniter 4
// Define document root for Vercel execution environment
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../public');

// Environment bootstrap for Vercel
putenv('VERCEL=1');

// Setup ephemeral writable directory and SQLite database in /tmp for Vercel
$tmpDb = '/tmp/materiax_db.sqlite';
$seedDb = __DIR__ . '/../writable/materiax_db.sqlite';
if (!file_exists($tmpDb) && file_exists($seedDb)) {
    @copy($seedDb, $tmpDb);
}

// Forward execution to CodeIgniter 4 Front Controller
require __DIR__ . '/../public/index.php';

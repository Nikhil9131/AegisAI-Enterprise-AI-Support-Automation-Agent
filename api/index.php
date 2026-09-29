<?php
/**
 * AegisAI - Vercel Serverless Function Root Bridge
 * Bridges root Vercel serverless requests to web-app CodeIgniter 3 front controller
 */

// Set working directory to web-app so system/ and application/ resolve correctly
chdir(__DIR__ . '/../web-app');

// Require the CodeIgniter front controller
require __DIR__ . '/../web-app/index.php';

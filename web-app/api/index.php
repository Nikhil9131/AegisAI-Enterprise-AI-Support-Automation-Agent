<?php
/**
 * AegisAI - Vercel Serverless Function Bridge
 * Bridges Vercel serverless requests to CodeIgniter 3 front controller
 */

// Set working directory to web-app root so system/ and application/ resolve correctly
chdir(dirname(__DIR__));

// Require the main CodeIgniter front controller
require __DIR__ . '/../index.php';

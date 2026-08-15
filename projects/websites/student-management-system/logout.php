<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

session_unset();
session_destroy();
redirect('home.php');

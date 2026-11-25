<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

require_csrf();
customer_sign_out();
flash('success', 'You have signed out.');
redirect('index.php');

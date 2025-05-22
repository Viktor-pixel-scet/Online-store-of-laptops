<?php


session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function safeRedirect(string $url): void {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    echo "<script>window.location.href = " . json_encode($url) . "</script>";
    exit;
}

function formatProductPrice($price): string {
    return number_format(max(0, floatval($price)), 2, '.', ' ');
}

function getProductAvailabilityClass(int $stock): string {
    if ($stock > 10)   return 'text-success';
    if ($stock > 0)    return 'text-warning';
    return 'text-danger';
}

function renderProductBadges(array $product): string {
    $badges = [];
    if (!empty($product['is_new'])) {
        $badges[] = '<span class="badge bg-success me-2">Новинка</span>';
    }
    if (!empty($product['discount'])) {
        $badges[] = '<span class="badge bg-danger me-2">-' 
            . intval($product['discount']) . '%</span>';
    }
    return implode('', $badges);
}

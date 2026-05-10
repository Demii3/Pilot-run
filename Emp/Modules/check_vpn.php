<?php
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['login']) || $_SESSION['type'] != 'Emp') {
    echo json_encode([
        'success' => false,
        'vpnDetected' => false,
        'msg' => 'Unauthorized'
    ]);
    exit();
}

function hasValue($value) {
    return isset($value) && trim((string) $value) !== '';
}

function detectVpnOrProxySignals() {
    $signals = [];

    if (hasValue($_SERVER['HTTP_VIA'] ?? null)) {
        $signals[] = 'HTTP_VIA';
    }

    if (hasValue($_SERVER['HTTP_FORWARDED'] ?? null)) {
        $signals[] = 'HTTP_FORWARDED';
    }

    if (hasValue($_SERVER['HTTP_X_FORWARDED_FOR'] ?? null)) {
        $signals[] = 'HTTP_X_FORWARDED_FOR';
    }

    if (hasValue($_SERVER['HTTP_CLIENT_IP'] ?? null)) {
        $signals[] = 'HTTP_CLIENT_IP';
    }

    if (hasValue($_SERVER['HTTP_X_REAL_IP'] ?? null)) {
        $signals[] = 'HTTP_X_REAL_IP';
    }

    return $signals;
}

$signals = detectVpnOrProxySignals();
$vpnDetected = count($signals) > 0;

echo json_encode([
    'success' => true,
    'vpnDetected' => $vpnDetected,
    'signals' => $signals,
    'msg' => $vpnDetected
        ? 'VPN or proxy connection detected. Please turn off VPN/proxy before tapping in.'
        : 'No VPN/proxy signal detected.'
]);
?>
#!/usr/local/bin/php
<?php

// Run ifconfig command
$output = shell_exec('/sbin/ifconfig');

// Check if output is available
if (!$output) {
    die(json_encode(["error" => "Failed to execute ifconfig"], JSON_PRETTY_PRINT));
}

// Initialize result array
$interfaces = [];
$blocks = preg_split('/\n(?=[^\s])/', $output); // Splits each interface block

foreach ($blocks as $block) {
    $lines = explode("\n", trim($block));
    $interface = [];
    $carpDetails = [];

    // Extract interface name
    if (preg_match('/^(\S+):/', $lines[0], $matches)) {
        $iface = $matches[1];
        $interface['interface'] = $iface;
    } else {
        continue;
    }

    // Process each line for details
    foreach ($lines as $line) {
        if (preg_match('/description:\s(.+)/', $line, $matches)) {
            $interface['description'] = trim($matches[1]);
        }
        if (preg_match('/ether\s([a-f0-9:]+)/', $line, $matches)) {
            $interface['mac'] = $matches[1];
        }
        if (preg_match('/inet\s(\d+\.\d+\.\d+\.\d+)\snetmask\s0x[a-f0-9]+\sbroadcast\s(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            $interface['address'] = $matches[1];
            $interface['broadcast'] = $matches[2];
        }
        if (preg_match('/inet6\s([a-f0-9:]+)%?\S*\sprefixlen\s\d+/', $line, $matches)) {
            $interface['ipv6'][] = $matches[1];
        }
        if (preg_match('/media:\s+(.+)/', $line, $matches)) {
            $interface['media'] = trim($matches[1]);
        }
        if (preg_match('/status:\s+(.+)/', $line, $matches)) {
            $interface['status'] = trim($matches[1]);
        }
        if (preg_match('/flags=.*<([^>]+)>/', $line, $matches)) {
            $interface['flags'] = explode(',', $matches[1]);
        }
        if (preg_match('/options=.*<([^>]+)>/', $line, $matches)) {
            $interface['options'] = explode(',', $matches[1]);
        }
    }

    $interfaces[] = $interface;
}

// Print JSON output
echo json_encode($interfaces, JSON_PRETTY_PRINT) . PHP_EOL;

?>

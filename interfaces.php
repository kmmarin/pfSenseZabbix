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

    // Extract interface name
    if (preg_match('/^(\S+):/', $lines[0], $matches)) {
        $iface = $matches[1];
        $interface['interface'] = $iface;
    } else {
        continue; // Skip blocks that don't have a valid interface
    }

    // Initialize variables for the addresses and vhid
    $addressFound = false;
    $carpAddressFound = false;
    $vhid = null;
    $carpStatus = null;

    // Process each line for details
    foreach ($lines as $line) {
        if (preg_match('/description:\s(.+)/', $line, $matches)) {
            $interface['description'] = trim($matches[1]);
        }
        if (preg_match('/ether\s([a-f0-9:]+)/', $line, $matches)) {
            $interface['mac'] = $matches[1];
        }
        if (preg_match('/inet\s(\d+\.\d+\.\d+\.\d+)\snetmask\s0x[a-f0-9]+\sbroadcast\s(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            if (!$addressFound) {
                // First inet line - set as the main address
                $interface['address'] = $matches[1];
                $interface['broadcast'] = $matches[2];
                $addressFound = true;
            }
        }
        if (preg_match('/inet\s(\d+\.\d+\.\d+\.\d+)\snetmask\s0x[a-f0-9]+\sbroadcast\s(\d+\.\d+\.\d+\.\d+)\svhid\s(\d+)/', $line, $matches)) {
            if (!$carpAddressFound) {
                // Second inet line with vhid - set as carp address
                $interface['carpaddress'] = $matches[1];
                $vhid = $matches[3];  // Set the vhid
                $carpAddressFound = true;
            }
        }
        if (preg_match('/carp:\s+(\S+)\s+vhid\s+\d+/', $line, $matches)) {
            // Capture carp status (e.g., "MASTER")
            $carpStatus = $matches[1];
        }
        if (preg_match('/status:\s+(.+)/', $line, $matches)) {
            $interface['status'] = trim($matches[1]);
        }
    }

    // Add vhid and carp status if available
    if ($vhid) {
        $interface['vhid'] = $vhid;
    }
    if ($carpStatus) {
        $interface['carp'] = $carpStatus;
    }

    // Exclude interfaces with "no carrier" status or no inet address
    if ((isset($interface['status']) && $interface['status'] === 'no carrier') || !isset($interface['address'])) {
        continue;
    }

    // Add the interface to the result array (not grouped by interface name)
    $interfaces[] = $interface;
}

// Print JSON output as a flat list
echo json_encode($interfaces, JSON_PRETTY_PRINT) . PHP_EOL;

?>

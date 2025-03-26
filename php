/usr/local/sbin/gateway.php
#!/usr/local/bin/php
<?php

// Run the pfSense command and capture its output
$output = shell_exec('/usr/local/sbin/pfSsh.php playback gatewaystatus');

// Split the output into lines
$lines = explode("\n", trim($output));

// Initialize an array to hold the JSON data
$gatewayData = array();

// Process each line and convert it to an associative array
foreach ($lines as $line) {
    // Split the line into columns
    $columns = preg_split('/\s+/', $line);
    
    // Skip the header line (first row)
    if ($columns[0] == 'Name') {
        continue;
    }

    // Create an associative array for each entry
    $gatewayData[] = array(
        'Name'      => $columns[0],
        'Monitor'   => $columns[1],
        'Source'    => $columns[2],
        'Delay'     => $columns[3],
        'StdDev'    => $columns[4],
        'Loss'      => $columns[5],
        'Status'    => $columns[6],
        'Substatus' => $columns[7]
    );
}

// Output the data as JSON
echo json_encode($gatewayData, JSON_PRETTY_PRINT);
?>

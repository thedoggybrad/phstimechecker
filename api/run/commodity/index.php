<?php
// ntp_time.php

function getNTPTime($host = 'ntp.pagasa.dost.gov.ph') {
    $ntpServer = $host;
    $port = 123; // NTP operates on port 123
    $timeout = 1; // Timeout in seconds

    // Create a socket
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);

    // NTP packet
    $msg = "\010" . str_repeat("\0", 47); // NTP request packet
    socket_sendto($socket, $msg, strlen($msg), 0, $ntpServer, $port);
    socket_recvfrom($socket, $recvBuffer, 48, 0, $ntpServer, $port);
    
    // Unpack the received NTP time
    $data = unpack('N12', $recvBuffer);
    $timestamp = $data[9] - 2208988800; // Convert to Unix timestamp

    // Close the socket
    socket_close($socket);

    return date('Y-m-d H:i:s', $timestamp);
}

$ntpTime = getNTPTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NTP Time</title>
    <script>
        // Get the initial NTP time from PHP
        const initialTime = new Date("<?= $ntpTime ?>").getTime();
        const offset = new Date().getTime() - initialTime;

        function updateClock() {
            const currentTime = new Date(Date.now() - offset);
            document.getElementById('clock').textContent = currentTime.toLocaleString();
        }

        setInterval(updateClock, 1000); // Update every second
    </script>
</head>
<body>
    <h1>NTP Time</h1>
    <div id="clock"><?= $ntpTime ?></div>
</body>
</html>

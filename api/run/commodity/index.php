<?php
// ntp_time.php

function getNTPTime($host = 'ntp.pagasa.dost.gov.ph') {
    $ntpServer = $host;
    $port = 123; 
    $timeout = 1; 

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

    // Convert to UTC+8
    return $timestamp + 8 * 3600; // Return the Unix timestamp adjusted to UTC+8
}

$ntpTimestamp = getNTPTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Philippine Standard Time</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #282c34; 
            color: #fff; 
            margin: 0; 
        }
        #clock {
            text-align: center; 
            font-size: 4rem; 
            padding: 20px; 
            border: 2px solid #fff; 
            border-radius: 10px; 
            background-color: #61dafb; 
            color: #282c34; 
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); 
            transition: background-color 0.3s; 
        }
    </style>
    <script>
        // Get the initial NTP timestamp from PHP
        const initialTimestamp = <?= $ntpTimestamp ?> * 1000; // Already in UTC+8

        function updateClock() {
            const currentTime = new Date(initialTimestamp + (Date.now() - initialTimestamp));
            const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const formattedTime = currentTime.toLocaleString('en-US', optionsTime);
            const optionsDate = { month: 'long', day: '2-digit', year: 'numeric' };
            const formattedDate = currentTime.toLocaleDateString('en-US', optionsDate);
            const formattedDay = currentTime.toLocaleString('en-US', { weekday: 'long' });

            document.getElementById('clock').innerHTML = formattedTime + '<br>' + formattedDate + '<br>' + formattedDay;
        }

        // Function to cycle background color
        const colors = ['#61dafb', '#21a1f1', '#282c34', '#fff', '#61dafb']; // Add any colors you want to cycle through
        let currentColorIndex = 0;

        function cycleColors() {
            const clockElement = document.getElementById('clock');
            clockElement.style.backgroundColor = colors[currentColorIndex];
            currentColorIndex = (currentColorIndex + 1) % colors.length;
        }

        setInterval(updateClock, 1000); 
        setInterval(cycleColors, 1000); // Change color every second
        updateClock(); 
    </script>
</head>
<body>
    <h1>Philippine Standard Time</h1>
    <div id="clock">Please Wait.....</div>
    <p style="margin-top: 30px;">Time brought to you by Time Service Unit of the Philippine Atmospheric, Geophysical and Astronomical Services Administration via its NTP server.</p>
</body>
</html>

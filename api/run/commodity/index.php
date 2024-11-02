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

    // Convert to UTC+8
    return date('Y-m-d H:i:s', $timestamp + 8 * 3600);
}

$ntpTime = getNTPTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NTP Time</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #282c34; /* Darker background */
            color: #fff; /* White text color */
            margin: 0; /* Remove default margin */
        }
        #clock {
            text-align: center; /* Center the text */
            font-size: 2rem; /* Large font size for the clock */
            padding: 20px; /* Padding around the clock */
            border: 2px solid #fff; /* White border around the clock */
            border-radius: 10px; /* Rounded corners */
            background-color: #61dafb; /* Light blue background */
            color: #282c34; /* Dark text color */
            font-weight: bold; /* Make text bold */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Soft shadow effect */
            transition: background-color 0.3s; /* Smooth background transition */
        }
        #clock:hover {
            background-color: #21a1f1; /* Change background color on hover */
        }
    </style>
    <script>
        // Get the initial NTP time from PHP
        const initialTime = new Date("<?= $ntpTime ?>").getTime();
        const offset = new Date().getTime() - initialTime;

        function updateClock() {
            const currentTime = new Date(Date.now() - offset);
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const formattedTime = currentTime.toLocaleString('en-US', options);
            const formattedDate = currentTime.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });
            const formattedDay = currentTime.toLocaleString('en-US', { weekday: 'long' });

            document.getElementById('clock').innerHTML = formattedTime + '<br>' + formattedDate + '<br>' + formattedDay;
        }

        setInterval(updateClock, 1000); // Update every second
    </script>
</head>
<body>
    <h1>NTP Time</h1>
    <div id="clock"><?= $ntpTime ?></div>
</body>
</html>

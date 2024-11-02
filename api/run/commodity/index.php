<?php
error_reporting(0);
function getNTPTime($host = 'ntp.pagasa.dost.gov.ph') {
    $ntpServer = $host;
    $port = 123; 
    $timeout = 1; 
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
    $msg = "\010" . str_repeat("\0", 47); 
    socket_sendto($socket, $msg, strlen($msg), 0, $ntpServer, $port);
    socket_recvfrom($socket, $recvBuffer, 48, 0, $ntpServer, $port);
    $data = unpack('N12', $recvBuffer);
    $timestamp = $data[9] - 2208988800;
    socket_close($socket);
    return $timestamp + 8 * 3600; 
}

$ntpTimestamp = getNTPTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Philippine Standard Time (PhST) by TheDoggyBrad Software Labs</title>
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

h1 {
    margin-bottom: 20px; 
}

#clock {
    flex-shrink: 0; 
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


a:link, a:visited, a:active, a:hover {
    color: #fff;
}

h1 {
    font-size: 2rem;
    text-align: center; 
}

@media (max-width: 600px) {
    #clock {
        font-size: 2rem; 
    }

    h1 {
        font-size: 1.5rem; 
    }
}

@media (orientation: landscape) and (max-width: 600px) {
    h1 {
        font-size: 1.8rem;
        margin-bottom: 10px; 
    }
}

    </style>
    <script>
        const initialTimestamp = <?= $ntpTimestamp ?> * 1000;

        function updateClock() {
            const currentTime = new Date(initialTimestamp + (Date.now() - initialTimestamp));
            const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const formattedTime = currentTime.toLocaleString('en-US', optionsTime);
            const optionsDate = { month: 'long', day: '2-digit', year: 'numeric' };
            const formattedDate = currentTime.toLocaleDateString('en-US', optionsDate);
            const formattedDay = currentTime.toLocaleString('en-US', { weekday: 'long' });

            document.getElementById('clock').innerHTML = formattedTime + '<br>' + formattedDate + '<br>' + formattedDay;
        }
        
        const colors = ['#61dafb', '#21a1f1', '#ffffff', '#f39c12', '#e74c3c'];
        let currentColorIndex = 0;

        function cycleColors() {
            const clockElement = document.getElementById('clock');
            clockElement.style.backgroundColor = colors[currentColorIndex];
            currentColorIndex = (currentColorIndex + 1) % colors.length;
        }

        setInterval(updateClock, 1000); 
        setInterval(cycleColors, 1000); 
        updateClock(); 
    </script>
</head>
<body>
    <h1 style="text-align: center;">Philippine Standard Time (PhST)</h1>
    <div id="clock">Please Wait.....</div>
    <p style="margin-top: 30px; margin-bottom: 20px;  text-align: center;">The time brought to you by <a href="https://www.pagasa.dost.gov.ph/astronomy">TSU-PAGASA</a> via their NTP server.<br>
    ©TheDoggyBrad Software Labs. Licensed under the <a href="https://github.com/thedoggybrad/pagasatimechecker/blob/main/LICENSE">MIT-0 License</a>.
    </p>
</body>
</html>

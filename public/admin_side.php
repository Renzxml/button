<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Locker System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .status {
            margin-top: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>RFID Locker System</h2>
    
    <button id="startScanReg" class="button">Start Registering RFID</button>
    <button id="startLockerIdentify" class="button">Start Locker Identify</button>
    
    <div class="status" id="statusMessage"></div>
</div>

<script>
    let ws = new WebSocket('ws://localhost:8080');
    
    // Handle WebSocket events
    ws.onopen = () => {
        console.log('WebSocket connected');
    };
    
    ws.onmessage = (event) => {
        const message = event.data;
        const statusMessage = document.getElementById('statusMessage');
        
        // Update the status message on the webpage
        statusMessage.innerHTML = message;
    };
    
    ws.onclose = () => {
        console.log('WebSocket disconnected');
    };
    
    // Start RFID Registration scan
    document.getElementById('startScanReg').onclick = () => {
        ws.send('START_SCANNING_REGISTER');
        document.getElementById('statusMessage').innerHTML = 'Waiting for RFID scan...';
    };
    
    // Start Locker Identification scan
    document.getElementById('startLockerIdentify').onclick = () => {
        ws.send('START_LOCKER_IDENTIFY');
        document.getElementById('statusMessage').innerHTML = 'Waiting for RFID scan...';
    };
</script>

</body>
</html>

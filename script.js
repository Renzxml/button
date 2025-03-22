const ws = new WebSocket("ws://localhost:8080");

ws.onopen = () => console.log("Connected to WebSocket Server");

ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log("Received:", data);

    if (data.type === "REGISTERED") {
        document.getElementById("status").innerHTML = "RFID Registered: " + data.uid;
    } else if (data.type === "ALREADY_REGISTERED") {
        document.getElementById("status").innerHTML = "Already Registered: " + data.uid;
    }
};

document.getElementById("scanButton").addEventListener("click", () => {
    ws.send(JSON.stringify({ type: "SCAN_REG" }));
});

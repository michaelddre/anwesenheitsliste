// server.js
const express = require("express");
const http = require("http");
const cors = require("cors");
const { Server } = require("socket.io");

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: "*" }
});

// Wenn ein Client verbunden ist
io.on("connection", (socket) => {
  console.log("✅ Client verbunden:", socket.id);

  // Wenn jemand eine Anwesenheitsänderung sendet
  socket.on("attendanceChanged", (data) => {
    console.log("📢 Änderung empfangen:", data);
    // Sende an alle anderen Nutzer
    socket.broadcast.emit("attendanceUpdate", data);
  });

  // Optional: Trennung anzeigen
  socket.on("disconnect", () => {
    console.log("⛔️ Client getrennt:", socket.id);
  });
});

// Server starten
server.listen(3000, () => {
  console.log("🚀 Socket.io-Server läuft auf http://localhost:3000");
});

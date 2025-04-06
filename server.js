const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const cors = require("cors");

const app = express();
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: "*" }
});

const onlineUsers = new Map();

io.on("connection", (socket) => {
  console.log("🔌 Benutzer verbunden");

  socket.on("userJoined", (user) => {
    console.log("👤 userJoined:", user);
    if (user?.name && user?.image) {
      onlineUsers.set(socket.id, user);
      io.emit("onlineUsers", Array.from(onlineUsers.values()));
    }
  });

  socket.on("attendanceChanged", (data) => {
    console.log("🔄 Änderung empfangen:", data);
    socket.broadcast.emit("attendanceUpdate", data);
  });

  socket.on("disconnect", () => {
    onlineUsers.delete(socket.id);
    io.emit("onlineUsers", Array.from(onlineUsers.values()));
  });
});

// 🔥 Render stellt dir den Port über process.env.PORT zur Verfügung
const PORT = process.env.PORT || 10000;
server.listen(PORT, () => {
  console.log(`🚀 Server läuft auf Port ${PORT}`);
});

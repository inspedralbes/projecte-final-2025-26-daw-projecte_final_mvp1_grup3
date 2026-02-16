'use strict';

var http = require('http');
var socketIo = require('socket.io');
var Server = socketIo.Server;
var feedbackSubscriber = require('./subscribers/feedbackSubscriber');

var PORT = process.env.PORT || 3001;
var server = http.createServer(function (req, res) {
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ service: 'loopy-node', status: 'ok' }));
});

var io = new Server(server, {
  cors: { origin: '*' }
});

feedbackSubscriber.attach(io);

io.on('connection', function (socket) {
  console.log('Client connected:', socket.id);
  socket.on('disconnect', function () {
    console.log('Client disconnected:', socket.id);
  });
});

server.listen(PORT, '0.0.0.0', function () {
  console.log('Backend Node listening on port', PORT);
});

/** 
 * @copyright Constantin Galbenu <xprt64@gmail.com>
 * @link        https://github.com/xprt64/php-css
 * @license     https://opensource.org/licenses/MIT MIT License
 * 
 * EventManager app
 */


var dgram	= require("dgram");
var net		= require("net");

/**
 * The list with all registered event listeners
 * 
 * @type object
 */
var clients	=	{};
var i = 0;	//debug variable

/**
 * The TCP Server that handles/registers new event listeners
 * 
 * @type Server.prototype.listen.self
 */
var listenersManager;

/**
 * The UDP server that receive triggered events and then dispatch the events to
 * all registered clients
 * 
 * @type Socket
 */
var receiver;

/********************************************
 * The event receiver (catch all triggered events)
 */
receiver = dgram.createSocket("udp4");

var UDP_PORT	=	6969;
var TCP_PORT	=	6970;

receiver.on("error", function (err) {
  console.log("receiver error:\n" + err.stack);
  receiver.close();
});

receiver.on("message", function (msg, rinfo) {
  console.log( (i++) + ": receiver got: " + msg.length + " bytes from " + rinfo.address + ":" + rinfo.port);
  
  //forward the event to all registered clients
  for(var j in clients)
  {
	  var sock = clients[j];
	  
	   sock.write(msg);
	   
	    console.log("forwarded to client: " + j);
  }
});

receiver.on("listening", function () {
  var address = receiver.address();
  console.log("UDP receiver listening on " +
      address.address + ":" + address.port);
});

receiver.bind(UDP_PORT, '127.0.0.1');

/**************************
 * The event listeners manager
 */
listenersManager = net.createServer(function(sock){
	console.log("new client registered:" + sock.remoteAddress + ':' + sock.remotePort);
	
	var client_id	=	sock.remoteAddress + ':' + sock.remotePort;
	
	clients[client_id]	=	sock;
	
	sock.on('close', function(){
		console.log("client closed:" + client_id);
		
		delete clients[client_id];
	});
	
	sock.on('end', function(){
		console.log("client ended:" + client_id);
		
		delete clients[client_id];
	});
	
	sock.on('error', function(){
		console.log("error event:" + client_id);
	});
}).listen(TCP_PORT, '0.0.0.0');
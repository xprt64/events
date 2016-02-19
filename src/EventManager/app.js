/** 
 * @copyright Constantin Galbenu <xprt64@gmail.com>
 * @link        https://github.com/xprt64/php-css
 * @license     https://opensource.org/licenses/MIT MIT License
 * 
 * EventManager app
 */
var dgram	= require("dgram");
var http	= require("http");
var url		= require('url');

/**
 * The list with all registered event listeners
 * 
 * @type object
 */
var clients	=	{};
var clientsLength	=	0;

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
  
  var json	=	JSON.parse(msg);
   
   if(clientsLength <= 0)
	   console.log("no clients registered!");
   
  //forward the event to all registered clients
  for(var j in clients)
  {
	  if(!clients.hasOwnProperty(j))
		  continue;
	  
	  var client = clients[j];
	  
	  if(!json.userId || client.userId == json.userId)
	  {
		 client.response.write('event: ' + (json.type || 'ping') + "\n");
		 if(json.id)
			 client.response.write('id: ' + json.id + "\n");
		 client.response.write('data: ' + msg + "\n\n");
	   
	    console.log("forwarded to client #" + j);
	}
	else//we don't forward event that are for other user
	    console.log("not forwarded to client: " + j);
 }
});

setInterval(function() {
	for (var j in clients)
	{
		if (!clients.hasOwnProperty(j))
			continue;
		
		var client = clients[j];

		client.response.write("event: ping\n");
		client.response.write('data: ' + 'stai alive' + "\n\n");

		console.log("pinged client #" + j);
	}
}, 60*10 * 1000);

receiver.on("listening", function () {
  var address = receiver.address();
  console.log("UDP receiver listening on " +
      address.address + ":" + address.port);
});

receiver.bind(UDP_PORT, '127.0.0.1');

/**************************
 * The event listeners manager
 */
listenersManager = http.createServer();

listenersManager.on('request', function(req, response){

	var sock		=	req.connection;
	var client_id	=	sock.remoteAddress + ':' + sock.remotePort + '#' + Math.random();
	var url_parts	=	url.parse(req.url, true);
	
	console.log("new client request registration:" + sock.remoteAddress + ':' + sock.remotePort);
	
	// add client to clients list
	clients[client_id]	=	{
		response:	response,
		userId:		url_parts.query.userId
	};

	clientsLength++;

	console.log("clientsLength:" + clientsLength);
	
	
	response.writeHead(200, {
		'Content-Type'					:	'text/event-stream', 
		'Access-Control-Allow-Origin'	:	'*'
	});
	
	sock.on('close', function(){
		console.log("client closed:" + client_id);
	});
	
	sock.on('end', function(){
		console.log("client ended:" + client_id);
		
		delete clients[client_id];
		
		clientsLength--;
	});
	
	sock.on('error', function(){
		console.log("error event:" + client_id);
	});
}).listen(TCP_PORT, '0.0.0.0');


function pecho(s)
{
	console.log(s);
}
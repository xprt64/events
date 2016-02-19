# events
Inter process events in PHP, with help from NodeJS

# description
Library used to send events from php scripts (running outside the web server,for example in a cron job)
to active web browser connections (like server sent events connections)

# example
see examples/ directory_

open a terminal on the server and start the nodejs application: nodejs src/EventManager/app.js

open http://yourserver/examples/index.html in a browser, in multiple tabs/windows_
open a terminal on yourserver machine and run_
`php -f examples/sse-backend-trigger.php`
You should see the test events displayed in the browser_

# note
Initialy I implemented the sse frontent as a php script under apache but apache keeps creating
process for every new sse request and don't close old ones; it should kill the script once the client closes the connection
but is doesn't._
So I implemented the sse server on nodejs, along with the event listener. 

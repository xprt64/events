# events
Inter process events in PHP, with help from NodeJS

# description
Library used to send events from php scripts (running outside the web server,for example in a cron job)
to active web browser connections (like server sent events connections)

# example
see examples/ directory_
open http://localhost/examples/index.html in a browser, in multiple tabs/windows_
open a terminal on the localhost machine and run_
`php -f examples/sse-backend-trigger.php`
You should see the test events displayed in the browser_
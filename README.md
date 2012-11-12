REST_Pong_server
================

The REST_Pong_server is (as the name suggests) a REST-based implementation of a Pong-server written in PHP.

Reqirements
-----------
PHP>=5.4
mod_rewrite enabled
gettimeofday()-system-call needs to be supported

Installation
------------
Copy contents of this repo in a folder named 'game' on your server without removing any files and make sure they are accessible from wherever yout want to use them. It should be located under <adress of your server>/game/ .

License
-------
This Project is published unter the MIT License (see LICENSE.txt)

API
---
all single-status (e.g. 'ok', error codes) arer returned in HTTP-statuscodes and (for better compatibility and console-logging) short verbal messages. Longer returns with more than one submitted value are JSON-encoded arrays. Calling any URI not listed below will result in a returned 404-error.
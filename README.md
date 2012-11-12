REST_Pong_server
================

The REST_Pong_server is (as the name suggests) a REST-based implementation of a Pong-server written in PHP as a submission to the 4th CodingContest (http://www.coding-contest.de).

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
I tried to keep the API as close as possible to the given sample (https://github.com/leipzigjs/pongengine) since it was the 
All single-status (e.g. 'ok', error codes) arer returned in HTTP-statuscodes and (for better compatibility and console-logging) short verbal messages. Longer returns with more than one submitted value are JSON-encoded arrays. Calling any URI not listed below will result in a returned 404-error.

### GET game/:key/config
Returned array:
* (int) FIELD_WIDTH
* (int) FIELD_HEIGHT
* (int) BALL_RADIUS
* (int) PADDLE_HEIGHT (top border, counting from the top)
* (int) PADDLE_WIDTH (witdh of the paddles. NOTE: the paddels are completely inside the field)
* (int) PADDLE_STEP (distance covered in a single paddle move)
* (int) NUMBER_OF_PADDLE_MOVES (number of allowed steps per second)
* (int) ACCELERATOR (horizontal acceleration of the ball per hit by a paddle)
* (int) INITIAL_BALL_SPEED (initial horizontal speed in pixels per second)
* (int) WAIT_BEFORE_START (delay of the start of the game in seconds after login of both players)
* (int) SCORE_TO_WIN
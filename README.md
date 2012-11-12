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
Returns basic configuration of the game (key ist not realy necessary but shouldn't be empty).
Returned array:
* (int) FIELD_WIDTH
* (int) FIELD_HEIGHT
* (int) BALL_RADIUS
* (int) PADDLE_HEIGHT (top border, counting from the top)
* (int) PADDLE_WIDTH (witdh of the paddles. NOTE: the paddles are completely inside the field)
* (int) PADDLE_STEP (distance covered in a single paddle move)
* (int) NUMBER_OF_PADDLE_MOVES (number of allowed steps per second)
* (int) ACCELERATOR (horizontal acceleration of the ball per hit by a paddle)
* (int) INITIAL_BALL_SPEED (initial horizontal speed in pixels per second)
* (int) WAIT_BEFORE_START (delay of the start of the game in seconds after login of both players)
* (int) SCORE_TO_WIN

### GET game/:key/status
Returned array:
* players{(string) 'left', (string) 'right'} (Nicknames of both players)
* (int) leftMoveCounter (left players moves this second)
* (int) rightPlayersCounter
* (int) scoreLeft (left players score)
* (int) scoreRight
* ball{(int) 0, (int) 1} (x(=0) and y(=1)-position of the ball)
* ballDelta{(int) 0, (int) 1} ((x(=0) and y(=1)-distance per second)
* (int) paddleLeft (top position of left paddle)
* (int) paddleRight
* (string) status (status of the game, possible values: 'login', 'ready', 'started');

### PUT game/:key/player/:playername
* return (string) secret and status 200 if successfull
* return status 510 if playername==''
* return status 423 on login-attempts if server is full

### POST game/:key/start
* return (string) 'ok' if two players logged in and game started
* return status 404 if key doesn't exist

### POST game/:key/player/:playername/:secret/up
* move the paddle up
* return status 200 if successfull
* return status 400 and (string) 'Wrong secret code.' if playername/secret-combination is invalid
* return status 404 if key doesn't exist or game didn't start yet
* return status 500 and (string) 'Too many moves.' if player tried to make more moves than allowed

### POST game/:key/player/:playername/:secret/down
* move the paddle down
* returns the same as POST game/:key/player/:playername/:secret/up
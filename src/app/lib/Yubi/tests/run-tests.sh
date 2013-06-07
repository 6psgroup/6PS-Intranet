#!/bin/bash
rm results.js
phpunit --log-json results.js AllTests.php > results.txt
php process.php results.js > test-results.html
# the following is for OS X
open -a firefox test-results.html


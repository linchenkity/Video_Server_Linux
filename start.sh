#!/bin/bash
#
# Video Encode System Start Script
# Version 0.2-alpha
#
screen -dmS main_thread
screen -x -S main_thread -p 0 -X stuff "php main.php"
screen -x -S main_thread -p 0 -X stuff $'\n'

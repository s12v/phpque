#!/bin/bash

git clone https://github.com/antirez/disque.git
cd disque
make install
./src/disque-server ./disque.conf &

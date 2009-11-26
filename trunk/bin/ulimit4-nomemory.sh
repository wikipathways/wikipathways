#!/bin/bash

ulimit -t $1 -f $2
eval "$3"

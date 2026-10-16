#!/bin/bash
git rm --cached js/twemoji
git rm --cached js/wPaint
git submodule init && git submodule update

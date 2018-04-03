#!/usr/bin/env bash
## =========================================================================
#   This script should be launched under root (or those, who has permissions
#   to change rights on '/var/lib/php/sessions' dir)
## =========================================================================
DIR_SESSIONS="/var/lib/php/sessions"
FILE_LOG="/home/user/fl32botsess.log"
FILE_REPORT="/home/user/fl32botsess.report"
GROUP_ORIG="root"
GROUP_WEB="www-data"
URL="http://your.shop.com/fl32botsess/clean/files"

# add scan permissions to sessions folder for web-server
sudo chgrp ${GROUP_WEB} ${DIR_SESSIONS}
sudo chmod g+r ${DIR_SESSIONS}

# call to cleanup service to be performed under web-server rights
wget --no-check-certificate --output-file=${FILE_LOG} --output-document=${FILE_REPORT} ${URL}

# restore scan permissions to sessions folder
sudo chmod g-r ${DIR_SESSIONS}
sudo chgrp ${GROUP_ORIG} ${DIR_SESSIONS}

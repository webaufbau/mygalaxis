<?php
include_once('/home/famajynu/www/my_offertenschweiz_ch/git_deploy_token.php');

define("REMOTE_REPOSITORY", "git@github.com:webaufbau/mygalaxis.git"); // The SSH URL to your repository
define("DIR", "/home/famajynu/www/my_offertenschweiz_ch/");                          // The path to your repostiroy; this must begin with a forward slash (/)
define("BRANCH", "main");                                 // The branch route
define("LOGFILE", "/home/famajynu/my_offertenschweiz_ch_deploy.log");                                       // The name of the file you want to log to.
define("GIT", "/usr/bin/git");                                         // The path to the git executable
define("MAX_EXECUTION_TIME", 180);                                     // Override for PHP's max_execution_time (may need set in php.ini)
define("BEFORE_PULL", "");                                             // A command to execute before pulling
define("AFTER_PULL", "");                                              // A command to execute after successfully pulling

require_once("/home/famajynu/www/my_offertenschweiz_ch/public/git-deploy/deployer.php");

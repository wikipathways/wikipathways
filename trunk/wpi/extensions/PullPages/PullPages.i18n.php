<?php
$messages['en'] = array(
	'pullpages' => 'Pull Pages',
	'pullpage-intro' => "Please specify the wiki you want to pull pages from.  If a username and password isn't given, then the pull will be done anonymously and imgauth will not be able to be used.",
	'pullpage-source-wiki' => "Base URL of source wiki.  (e.g. http://example.com/index.php):",
	'pullpage-source-page' => "Page to use on the source wiki to find a list of pages to pull:",
	'pullpage-source-user' => "Username on source wiki",
	'pullpage-source-pass' => "Password on source wiki",
	'pullpage-use-imgauth' => "Pull images using img_auth.php",
	'pullpage-submit' => 'Submit',
	'pullpage-progress-start' => "Starting to pull Pages...",
	'pullpage-progress-page-good'  => "Pulling $1... <span style='color: green'>ok</span><br>",
	'pullpage-progress-page-error'  => "Pulling $1... <span style='color: red'>$2</span><br>",
	'pullpage-progress-end'   => "Finished pulling pages (current memory: $2M, max mem: $3M: ).

[[$1|See the pagelist on this wiki]].",
	'pullpage-no-pages'       => "Couldn't get a list of pages to pull: $1",
	'pullpage-no-wiki'        => "You must provide a wiki url and a page on that wiki that will provide the page list.",
	'pullpage-login-failed'   => "The login failed.",
	'pagepuller-fetch-error'  => "Error fetching page: $1",
	'pagepuller-tmp-create-error'  => "Error creating tmp file: $1",
	'pagepuller-save-tmp-error'  => "Error saving tmp file: $1",
	'pagepuller-upload-error'  => "Errors during upload: $1",
);
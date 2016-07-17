<?php
/**
 * Takes a Trello board JSON file and creates a CSV file.
 * Usage:  php trell-to-csv.php thefile.json [list_title]
 * Would output thefile.json.csv
 * If list_title is specified only prints lists whose titles contain that string.
 */

require_once 'vendor/autoload.php';
require_once('classes/TrelloBoardToCsv.class.php');

//error_reporting(E_ALL);

$client = new \Github\Client();

$PERSONAL_TOKEN = '';
$COMMITER_NAME = 'Budi Prakosa';
$COMMITER_EMAIL = 'iyok@deadmediafm.org';
$REPO_USER = 'badgeek';
$REPO_NAME = 'testapi';
$REPO_BRANCH = 'master';

$TRELLO_JSON_URL = 'https://trello.com/b/OBb3lHF9.json';
$TRELLO_FILENAME = 'readme.md';
$TRELLO_MARKDOWN_CONTENT = '';

$COMMIT_MSG = 'update trello';

$committer = array('name' => $COMMITER_NAME, 'email' => $COMMITER_EMAIL);

$client->authenticate(
	$PERSONAL_TOKEN, 
	'', 
	Github\Client::AUTH_URL_TOKEN
);

// print_r($fileInfo);

try {
  $t = new TrelloBoardToCsv($TRELLO_JSON_URL, $list_title);
  $TRELLO_MARKDOWN_CONTENT = $t->getMarkdown();
  print 'Success!' . "\n";
}
catch (Exception $e) {
  print 'Exception thrown: ' . $e->getMessage();
}

$oldFile = $client->api('repo')->contents()->show($REPO_USER, $REPO_NAME, $TRELLO_FILENAME, $branch);

$fileInfo = $client->api('repo')->contents()->update($REPO_USER, $REPO_NAME, $TRELLO_FILENAME, $TRELLO_MARKDOWN_CONTENT, $COMMIT_MSG, $oldFile['sha'], $REPO_BRANCH, $committer);


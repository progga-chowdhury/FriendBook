<?php
session_start();

//Controller for the homepage. It only sets the page title and loads the view as there's
//no data to fetch.
$view = new stdClass();
$view->pageTitle = 'Homepage';
require_once('Views/index.phtml');

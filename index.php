<?php

require_once('config.php');
require_once('classes/rParser.php');

$feed = new rParser(ROMATSA, JSON);
$feed->check();

?>
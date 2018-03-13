<?php

include __DIR__ . "/../../vendor/autoload.php";


$source = new \Rx\React\FromFileObservable("source.txt");
$dest   = new \Rx\React\ToFileObserver("dest.txt");

$source
->cut()
->subscribe($dest);


<?php

foreach (glob(dirname(__FILE__) . "/*.php") as $filename) {
    $filename = realpath($filename);
    if ($filename !== __FILE__) {
        include $filename;
    }
}

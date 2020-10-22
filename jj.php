<?php
clearstatcache();
$files  = (glob('*'));
foreach ($files as $file){
    echo $file ." has a size of " .human_filesize(filesize($file),2);
    echo "\n";
}

function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

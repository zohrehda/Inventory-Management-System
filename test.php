<?php

//ini_set('memory_limit', '-1');
function mb($bytes)
{
    return round($bytes / 1024 / 1024, 2) . ' MB';
}
 
function genrator(int $n)
{
    for ($i = 0; $i < $n; $i++) {
        yield $i;
    }
}
function withArray($n)
{
    $rows = [];
    for ($i = 0; $i < $n; $i++) {
        $rows[] = $i;
    }
    return $rows;
}

$start = memory_get_usage();

$n = 500000;
$sum = 0;

foreach (withArray($n) as $v) {
    $sum   += $v;
}

$end = memory_get_peak_usage();

echo mb($end - $start);

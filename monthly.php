<?php
$start_date = date('01-m-Y');

$start_time = strtotime($start_date);
$end_time = strtotime("+1 month", $start_time);

for ($i = $start_time; $i < $end_time; $i += 60 * 60 * 24) {
   echo date('d l', $i) . "\n";
}

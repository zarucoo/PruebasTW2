<?php

require __DIR__ . '/vendor/autoload.php';

use SebastianBergmann\Timer\Timer;

$timer = new Timer;

$timer->start();

$i_1 = 0;
$i_2 = 1;
$i_new = 0;

foreach (range(0, 10) as $i) {
    // ...
    $i_new = $i_1 + $i_2;
    $i_1 = $i_2;
    $i_2 = $i_new;
}

$duration = $timer->stop();

echo "Last: " . $i_new;
echo "<br>";

var_dump(get_class($duration));
echo "<br>";
var_dump($duration->asString());
echo "<br>";
var_dump($duration->asSeconds());
echo "<br>";
var_dump($duration->asMilliseconds());
echo "<br>";
var_dump($duration->asMicroseconds());
echo "<br>";
var_dump($duration->asNanoseconds());

?>
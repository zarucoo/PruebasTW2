<?php
// Ejemplo 1: Sintaxis de arrays

$array = array(
    "foo" => "bar",
    "bar" => "foo",
);

// A partir de PHP 5.4 puedes usar la sintaxis corta
$array = [
    "foo" => "bar",
    "bar" => "foo",
];


// Ejemplo 2: claves con distintos tipos
$array = array(
    1 => "a",
    "1" => "b",
    1.5 => "c",
    true => "d",
);

var_dump($array);
?>

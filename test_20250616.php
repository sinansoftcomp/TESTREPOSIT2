<?php
function add($a, $b) {
    $result = $a + $b;
    var_dump([
        'a' => $a,
        'b' => $b,
        'result' => $result
    ]);
    return $result;
}

$x = 10;
$y = 20;
$sum = add($x, $y);

echo "гу╟Х: {$sum}\n";
?>
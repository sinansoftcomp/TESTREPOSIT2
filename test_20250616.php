<?php
// test_20250616.php
// PHP 디버깅 테스트 샘플
function add($a, $b) {
    $result = $a + $b;
    // 변수 값 출력 (디버깅)
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

echo "합계: {$sum}\n";
?>
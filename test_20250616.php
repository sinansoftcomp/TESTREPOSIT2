<?php
// test_20250616.php
// PHP ����� �׽�Ʈ ����
function add($a, $b) {
    $result = $a + $b;
    // ���� �� ��� (�����)
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

echo "�հ�: {$sum}\n";
?>
<?php
// ���� PHP ��ũ��Ʈ - ����� �׽�Ʈ��

// ������ �Լ� ����
function sayHello($name) {
    return "Hello, " . $name . "!";
}

// �迭 ���� �� �۾�
$fruits = ["Apple", "Banana", "Cherry"];
array_push($fruits, "Date");

// ���ǹ� �׽�Ʈ
$today = date("l");
if ($today == "Monday") {
    $message = "Start of the week!";
} else {
    $message = "It's " . $today . "!";
}

// �Լ� ȣ��
$greeting = sayHello("World");

// ���
echo $greeting . "\n";
echo "Fruits: " . implode(", ", $fruits) . "\n";
echo $message . "\n";

// ������� ���� ����
$debug_var = "This is a debug variable.";
echo $debug_var . "\n";
echo "";
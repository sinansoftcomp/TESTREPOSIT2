<?php
// 샘플 PHP 스크립트 - 디버깅 테스트용

// 간단한 함수 정의
function sayHello($name) {
    return "Hello, " . $name . "!";
}

// 배열 정의 및 작업
$fruits = ["Apple", "Banana", "Cherry"];
array_push($fruits, "Date");

// 조건문 테스트
$today = date("l");
if ($today == "Monday") {
    $message = "Start of the week!";
} else {
    $message = "It's " . $today . "!";
}

// 함수 호출
$greeting = sayHello("World");

// 출력
echo $greeting . "\n";
echo "Fruits: " . implode(", ", $fruits) . "\n";
echo $message . "\n";

// 디버깅을 위한 변수
$debug_var = "This is a debug variable.";
echo $debug_var . "\n";
echo "";
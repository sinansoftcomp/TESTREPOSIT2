<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>간단한 화면 샘플</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 32px; }
        h2 { text-align: center; color: #333; }
        button { width: 100%; padding: 12px; background: #0078d7; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #005fa3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>간단한 화면 샘플</h2>
        <p>이곳은 샘플 화면입니다.</p>
        <button onclick="alert('버튼이 클릭되었습니다!!!~~~')">확인</button>
        <button onclick="alert('새로운 버튼이 추가되었습니다!')">새 버튼</button>
        <button onclick="alert('세 번째 버튼입니다!')">세 번째 버튼</button>
        <form method="post" action="">
            <input type="text" name="username" placeholder="이름을 입력하세요" style="width:100%;padding:10px;margin:12px 0 8px 0;border:1px solid #ccc;border-radius:4px;">
            <button type="submit">제출</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username'])) {
            $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
            echo "<p style='color:green;text-align:center;'>안녕하세요~~~~, {$username}님!</p>";
        }
        ?>
    </div>
</body>
</html>

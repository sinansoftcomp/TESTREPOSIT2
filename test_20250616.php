<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>������ ȭ�� ����</title>
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
        <h2>������ ȭ�� ����</h2>
        <p>�̰��� ���� ȭ���Դϴ�.</p>
        <button onclick="alert('��ư�� Ŭ���Ǿ����ϴ�!!!~~~')">Ȯ��</button>
        <button onclick="alert('���ο� ��ư�� �߰��Ǿ����ϴ�!')">�� ��ư</button>
        <button onclick="alert('�� ��° ��ư�Դϴ�!')">�� ��° ��ư</button>
        <form method="post" action="">
            <input type="text" name="username" placeholder="�̸��� �Է��ϼ���" style="width:100%;padding:10px;margin:12px 0 8px 0;border:1px solid #ccc;border-radius:4px;">
            <button type="submit">����</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username'])) {
            $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
            echo "<p style='color:green;text-align:center;'>�ȳ��ϼ���~~~~, {$username}��!</p>";
        }
        ?>
    </div>
</body>
</html>

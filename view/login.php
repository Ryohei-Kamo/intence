<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
</head>
<body>
<h1>ログイン</h1>
<!-- 入力エリア -->
<div class="input_area">
    <form action="./index.php" method="post" id="contact_form">
        <dl class="member_id">
            <dt>会員ID</dt>
            <dd><input type="number" name="member_id" value="" step="0.1"></dd>
        </dl>
        <dl class="password">
            <dt>パスワード</dt>
            <dd><input type="number" name="password" value="" step="0.1"></dd>
        </dl>
        <input type="hidden" value="login">
        <input type="submit" value="ログイン">
        <input type="hidden" value="login_first">
        <input type="submit" value="初回ログイン">
    </form>
</div>
</body>
</html>

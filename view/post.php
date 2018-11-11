<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>フィジカルデータ</title>
</head>
<body>
<h1>フィジカルデータ</h1>
<!-- 入力エリア -->
<div class="input_area">
    <form action="./index.php" method="post" id="contact_form">
        <dl class="weight">
            <dt>体重</dt>
            <dd><input type="number" name="weight" value="" step="0.1"></dd>
        </dl>
        <dl class="fat_percentage">
            <dt>体脂肪</dt>
            <dd><input type="number" name="fat_percentage" value="" step="0.1"></dd>
        </dl>
        <dl class="muscle_mass">
            <dt>筋肉量</dt>
            <dd><input type="number" name="muscle_mass" value="" step="0.1"></dd>
        </dl>
        <dl class="water_content">
            <dt>体水分量</dt>
            <dd><input type="number" name="water_content" value="" step="0.1"></dd>
        </dl>
        <dl class="visceral_fat">
            <dt>内臓脂肪</dt>
            <dd><input type="number" name="visceral_fat" value="" step="0.1"></dd>
        </dl>
        <dl class="basal_metabolic_rate">
            <dt>基礎代謝量</dt>
            <dd><input type="number" name="basal_metabolic_rate" value="" step="0.1"></dd>
        </dl>
        <dl class="bmi">
            <dt>BMI</dt>
            <dd><input type="number" name="bmi" value="" step="0.1"></dd>
        </dl>
        <input type="hidden" name="user_Id" value="save">
        <input type="submit" value="送信">
    </form>
</div>
<!-- //入力エリア -->
<hr>
<form action="./index.php" method="post">
    <input type="hidden" name="user_id" value="data_list">
    <input type="submit" value="確認">
</form>
</body>
</html>

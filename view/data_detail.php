<?php

// 登録データ取得
$post_data = $action->getPhysicalData($data_id);
?>
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
        <dl class="name">
            <dt>体重</dt>
            <dd><?php echo $post_data[0]["weight"]; ?>" step="0.1"></dd>
        </dl>
        <dl class="name">
            <dt>体脂肪</dt>
            <dd><?php echo $post_data[0]["fat_percentage"]; ?>" step="0.1"></dd>
        </dl>
        <dl class="name">
            <dt>筋肉量</dt>
            <dd><?php echo $post_data[0]["muscle_mass"]; ?>" step="0.1"></dd>
        </dl>
        <dl class="name">
            <dt>体水分量</dt>
            <dd><?php echo $post_data[0]["water_content"]; ?>" step="0.1"></dd>
        </dl>
        <dl class="name">
            <dt>内臓脂肪</dt>
            <dd><?php echo $post_data[0]["visceral_fat"]; ?>" step="0.1"></dd>
        </dl>
        <dl class="name">
            <dt>基礎代謝量</dt>
            <dd><?php echo $post_data[0]["basal_metabolic_rate"]; ?>" step="0.1""></dd>
        </dl>
        <dl class="name">
            <dt>BMI</dt>
            <dd><?php echo $post_data[0]["bmi"]; ?>" step="0.1"></dd>
        </dl>
        <!--		<input type="hidden" name="id" value="--><?php //echo $data_id;?><!--">-->
        <!--		<input type="hidden" name="user_id" value="edit_save">-->
        <!--		<input type="submit" value="送信">-->
    </form>
</div>
<!-- //入力エリア -->
<hr>
</body>
</html>

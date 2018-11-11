<?php

// 登録データ取得
$data_list = $action->getPhysicalDataList($user_id);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>フィジカルデータ一覧</title>
</head>
<body>
<h1>フィジカルデータ一覧</h1>
<!-- 投稿表示エリア -->
<?php if (!empty($data_list)) { ?>
    <table width="100%" border="1">
        <tr>
        </tr>
        <?php foreach ($data_list as $post) { ?>
            <tr>
                <td><?php echo nl2br($post["weight"]); ?></td>
                <td><?php echo nl2br($post["fat_percentage"]); ?></td>
                <td><?php echo nl2br($post["muscle_mass"]); ?></td>
                <td><?php echo nl2br($post["water_content"]); ?></td>
                <td><?php echo nl2br($post["visceral_fat"]); ?></td>
                <td><?php echo nl2br($post["basal_metabolic_rate"]); ?></td>
                <td><?php echo nl2br($post["bmi"]); ?></td>
                <td><?php echo $post["created_at"]; ?></td>
                <td align="center" valign="middle">
                    <form action="./index.php" method="post">
                        <input type="hidden" name="user_id" value="edit">
                        <input type="hidden" name="data_id" value="<?php echo $post["data_id"]; ?>">
                        <input type="submit" value="確認">
                    </form>
                </td>
                <!--				<td align="center" valign="middle">-->
                <!--					<form action="./index.php" method="post">-->
                <!--						<input type="hidden" name="user_id" value="delete">-->
                <!--						<input type="hidden" name="data_id" value="-->
                <?php //echo $post["data_id"];?><!--">-->
                <!--						<input type="submit" value="削除">-->
                <!--					</form>-->
                <!--				</td>-->
            </tr>
        <?php } ?>
    </table>
<?php } ?>
<!-- // 投稿表示エリア -->
<hr>
<p><a href="./">投稿に戻る</a></p>
</body>
</html>

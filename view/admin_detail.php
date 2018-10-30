<?php

// 登録データ取得

$post_data = $action->getPhysicalDataList($id);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>管理モードやんなー</title>
</head>
<body>
<h1>管理モードやんなーー</h1>
<!-- 入力エリア -->
<div class="input_area">
	<form action="./index.php" method="post" id="contact_form">
		<dl class="name">
			<dt>名前</dt>
			<dd><input type="text" name="name" value="<?php echo $post_data[0]["name"];?>"></dd>
		</dl>
		<dl class="email">
			<dt>メールアドレス</dt>
			<dd><input type="text" name="email" value="<?php echo $post_data[0]["email"];?>"></dd>
		</dl>
		<dl class="body">
			<dt>本文</dt>
			<dd><textarea name="body"><?php echo $post_data[0]["body"];?></textarea></dd>
		</dl>
		<input type="hidden" name="id" value="<?php echo $id;?>">
		<input type="hidden" name="eventId" value="edit_save">
		<input type="submit" value="送信">
	</form>
</div>
<!-- //入力エリア -->
<hr>
</body>
</html>

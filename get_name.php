<?php
if (isset($_POST['username']) && strlen($_POST['username']) <= 15) {
    setcookie('name', $_POST['username'], time() * 4, '/');
    header('Location: nieuw-spel');
}
?>
<form method="post">
    <label for="username"><h1>Hallo vreemdeling!</h1><p>Ik ken jou nog niet.. Hoe mag ik je noemen?</p></label>
    <div>
        <input autofocus type="text" name="username" autocomplete="off" maxlength="15">
        <button type="submit"><i class="fa-duotone fa-arrow-right-long"></i></button>
    </div>
</form>
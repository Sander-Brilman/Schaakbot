<form method="post">
    <label for="username"><h1>Hallo vreemdeling!</h1><p>Ik ken jou nog niet.. Hoe mag ik je noemen?</p></label>
    <div>
        <input autofocus type="text" name="username" autocomplete="off" maxlength="15">
        <button name="get_name" value="<?= create_form_id('get_name') ?>" type="submit"><i class="fa-duotone fa-arrow-right-long"></i></button>
    </div>
</form>
<?php
if (!isset($errorMsg)){
    $errorMsg = "Неизвестная ошибка!";
}
?>
<div class="error-message">
    <h3>
        Упс!
    </h3>
    <p>
        <?php echo $errorMsg; ?>
    </p>
</div>

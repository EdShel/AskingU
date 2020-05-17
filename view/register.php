<div class="modal fade" id="registerModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Регистрация</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="register" method="post">

                    <!-- User's name -->

                    <div class="form-group">
                        <label>Фамилия, инициалы</label><i class="required">*</i>
                        <input id="tName" type="text" name="name" placeholder="Иванов И.И."
                               class="form-control" required>
                    </div>


                    <!-- User's email -->

                    <div class="form-group">
                        <label>Электронная почта</label><i class="required">*</i>
                        <input type="email" name="email" placeholder="example@mail.com"
                               class="form-control" required>
                    </div>


                    <!-- User's password -->

                    <div class="form-group">
                        <label>Пароль</label><i class="required">*</i>
                        <input type="password" name="password" placeholder="От 6-ти символов"
                               class="form-control" required>
                    </div>


                    <!-- User's year of birth -->

                    <div class="form-group">
                        <label for="tYear">Год рождения</label>
                        <select id="tYear" class="form-control" name="year">
                            <?php

                            // Generate 100 years

                            $year = date("Y");
                            echo "<option selected>$year</option>";
                            for ($i = $year - 1; $i >= $year - 100; --$i) {
                                echo "<option>$i</option>";
                            }
                            ?>
                        </select>
                    </div>


                    <!-- User's gender -->

                    <div class="form-group">
                        <label>Пол</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       id="rbMale" type="radio" name="gender" value="0" checked>
                                <label class="form-check-label" for="rbMale">Мужской</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       id="rbFemale" type="radio" name="gender" value="1">
                                <label class="form-check-label" for="rbFemale">Женский</label>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation/cancellation buttons -->

                    <div>
                        <input type="submit" name="submit" value="Зарегистрироваться" class="btn btn-primary">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                    <hr>
                    <p>Обязательные для заполнения поля:
                    <?php

                    // Get all the label text which if followed by a required input
                    $regEx = "~<label.*?>(.+?)</label>(?:\s*<.+?>.*?</.+?>\s*)?<input.+?required.*?>~s";

                    // In this very file
                    $thisFile = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/view/register.php");
                    preg_match_all($regEx, $thisFile, $res);

                    $first = true;
                    // Go through the first group (the text in the label)
                    foreach ($res[1] as $k => $m) {

                        if ($first){
                            $first = false;
                        }
                        else{
                            echo ", ";
                        }
                        // And print field names
                        echo $m;
                    }

                    ?>
                    </p>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
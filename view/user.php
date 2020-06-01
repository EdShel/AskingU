<?php

if (!isset($model)) {
    ErrorHandler::AddError("The view requires a model!");
}

?>
<div class="container">
    <h2>
        Личный кабинет
    </h2>
    <div>
        <div class="row">

            <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-3">

                <!-- Name -->

                <div class="row">
                    <div class="col-6 text-right">
                        <b>Имя:</b>
                    </div>
                    <div class="col-6">
                        <?php echo $model['user']->Name; ?>
                    </div>
                </div>

                <!-- Email -->

                <div class="row">
                    <div class="col-6 text-right">
                        <b>Почта:</b>
                    </div>
                    <div class="col-6">
                        <?php echo $model['user']->Email; ?>
                    </div>
                </div>

            </div>

            <div class="col-lg-8 col-md-6 col-sm-12 col-12 mb-3">

                <!-- Gender -->

                <div class="row">

                    <div class="col-6 text-right">
                        <b>Пол:</b>
                    </div>
                    <div class="col-6">
                        <?php echo $model['user']->Gender == 0 ? "Мужской" : "Женский"; ?>
                    </div>
                </div>

                <!-- Year of birth -->

                <div class="row">

                    <div class="col-6 text-right">
                        <b>Год рождения:</b>
                    </div>
                    <div class="col-6">
                        <?php echo $model['user']->YearOfBirth; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <hr>

    <!--    Created polls    -->
    <div>
        <p>
            <a class="btn btn-info" data-toggle="collapse"
               href="#collapseExample" role="button"
               aria-expanded="false" aria-controls="collapseExample">
                <span class="fas fa-chevron-circle-down"></span>
                Скрыть/отобразить созданные опросы
            </a>
        </p>
        <div class="collapse" id="collapseExample">

            <?php

            if (count($model['polls']) === 0) {
                echo <<<HTML
                            <p>Вы ещё не создали ни одного опроса! <a href="/createPoll">Создать сейчас!</a></p>
                        HTML;
            } else {
                echo <<<HTML
                            <div class="row">
                        HTML;
            }

            foreach ($model['polls'] as $i => $poll) {
                echo new PollComponent($poll);
            }

            if (count($model['polls']) !== 0) {
                echo "</div>";
            }

            ?>
        </div>
    </div>

    <hr>

    <div>
        <a type="button" class="btn btn-secondary" href="main">Вернуться</a>
    </div>
</div>

<script>
    function onEnteringVariant(t) {
        if (t) {

            // Find the next element
            let parent = t.parentNode;
            let next = parent.nextElementSibling;
            if (!next) {
                return;
            }

            // Activate the next
            next.classList.remove("variant-invisible");
        }
    }

    // Hides this element and the following ones if they are empty
    function onEnteredVariant(t) {
        if (t) {
            if (t.value === "") {

                let parent = t.parentNode;
                let next = parent.nextElementSibling;
                let n = next;
                while (n) {
                    if (n.getElementsByTagName("input")[0].value !== "") {
                        return;
                    }
                    n = n.nextElementSibling;
                }

                // Hide this element
                t.classList.add("variant-invisible");

                // Hide siblings
                let toHide = next;
                while (toHide !== n) {
                    toHide.classList.add("variant-invisible");
                    toHide = n;
                }
            }
        }
    }

    document.getElementById("tQuestion").addEventListener("keydown", function (event) {
        if (event.key === 'Enter') {
            document.getElementById("tVariant0").focus();
            event.preventDefault();
            return false;
        }
    });

    function onFormEnter(event) {
        if (event.key !== 'Enter') {
            return true;
        } else if (document.activeElement.value !== "") {
            document.activeElement.focus();
            document.activeElement.parentNode
                .nextElementSibling
                .getElementsByTagName("input")[0].focus();
            return false;
        }
    }

    document.getElementById("pollCreateForm")
        .onkeydown = onFormEnter;
</script>

<div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <?php

                // Open the file with the message
                $rulesFile = $_SERVER["DOCUMENT_ROOT"] . "/texts/pollCreationRules";
                $fileContent = file_get_contents($rulesFile);

                // Define how to turn it into HTML
                $replacements = array(
                    // Headers
                    "/^#\s*(.+)$/m" => "<h2>\\1</h2>",
                    // Lists
                    "/^-.+(?!^-)/sm" => "<ul>\n\\0\n</ul>",
                    // List elements
                    "/^-\s+(.+)$/m" => "<li>\\1</li>",
                    // Italic text
                    "/\*\*(.+?)\*\*/" => "<i>\\1</i>"
                );

                // Replace each text pattern with HTML
                foreach ($replacements as $regEx => $replacement) {
                    $fileContent = preg_replace($regEx, $replacement, $fileContent);
                }
                // Print HTML
                echo $fileContent;

                ?>

                <div class="text-center">
                    <button type="button" class="btn btn-primary"
                            data-dismiss="modal">Понятно
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
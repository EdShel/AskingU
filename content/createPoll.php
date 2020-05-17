<?php

require_once "classes/ErrorHandler.php";

define("maxVariants", 8);
define("minVariants", 2);


if (isset($_POST['submit'])) {
    $question = $_POST['question'];

    if (trim($question) === "") {
        ErrorHandler::AddError("Вопрос не может быть пустым!");
    }

    $variants = array();

    // Go though all the variants
    for ($i = 0; $i < maxVariants; ++$i) {

        // Get its value
        $rawVariant = $_POST["variant{$i}"];
        $fixedVariant = preg_replace("/\s+/", " ", $rawVariant, -1);

        // If it's not an empty string
        if ($fixedVariant !== "") {
            // Add it to the array of strings
            $variants[] = $fixedVariant;
        }
    }

    if (count($variants) < minVariants) {
        ErrorHandler::AddError(
            "Для создания опроса необходимо как минимум " . minVariants . " вариантов");
    } else {

        require_once "classes/DbAccess.php";
        require_once "classes/Poll.php";
        require_once "classes/Variant.php";

        // Open db connection
        if (!isset($db)) {
            $db = new DbAccess();
        }

        $user = User::FromCookies($db);

        // Get blocking date
        $blockingTime = new DateTime('now');
        switch ($_POST["blockingTime"]) {
            case "hour":
                $blockingTime->add(new DateInterval("PT1H"));
                break;
            case "day":
                $blockingTime->add(new DateInterval("P1D"));
                break;
            case "week":
                $blockingTime->add(new DateInterval("P1W"));
                break;
            case "month":
                $blockingTime->add(new DateInterval("P1M"));
                break;
            default:
                $blockingTime = NULL;
        }

        // Create a poll
        $poll = new Poll(-1,
            $user->Id,
            $question,
            isset($_POST["isPublic"]),
            NULL,
            $blockingTime,
            0,
            isset($_POST["shuffle"]));

        $poll->ToDB($db);

        // Create variants
        for ($i = 0, $c = count($variants); $i < $c; $i++) {
            $variant = new Variant($poll->Id, $i, $variants[$i]);
            $variant->ToDB($db);
        }
    }

    // Go to the main page

    if (ErrorHandler::GetErrorsCount() === 0) {
        header("Location: main");
    } else {
        session_start();
        $_SESSION["errorMessages"] = ErrorHandler::$Errors;
        header("Location: main/error");
    }
    exit;
}

?>


<div class="container">
    <h2>
        Создание опроса
        <span class="fas fa-info-circle btn"
              data-toggle="modal"
              data-target="#rulesModal"></span>
    </h2>
    <form action="createPoll" method="post" autocomplete="off"
          id="pollCreateForm">

        <div class="row">

            <!-- Question -->

            <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-3">

                <div class="form-group">
                    <label for="tQuestion">
                        <span class="fas fa-question"></span>
                        Вопрос
                    </label>
                    <input id="tQuestion" type="text" name="question"
                           placeholder="Например, какой ваш любимый цвет?"
                           class="form-control" required>
                </div>

            </div>

            <!-- Variants -->

            <div class="col-lg-8 col-md-6 col-sm-12 col-12 mb-3">
                <div class="row">

                    <?php

                    for ($i = 0; $i < maxVariants; ++$i) {
                        $variantDOMId = "tVariant" . $i;
                        $variantOrder = $i + 1;
                        $isVisible = $i < minVariants ? "" : "variant-invisible";
                        $phpPropertyId = "variant" . $i;
                        echo <<<HTML
<div class="col-lg-6 col-md-12 col-sm-12 col-12 mb-3 
            form-group variant-creation {$isVisible}">
    <label for="{$variantDOMId}">Вариант №{$variantOrder}</label>
    <input id="{$variantDOMId}" type="text" name="{$phpPropertyId}"
           placeholder="Ответ {$variantOrder}"
           class="form-control"
           onfocus="onEnteringVariant(this)"
           onblur="onEnteredVariant(this)">
</div>

HTML;
                    }
                    ?>

                </div>
            </div>
        </div>


        <hr>

        <!--    Configurations      -->
        <div>
            <p>
                <a class="btn btn-info" data-toggle="collapse"
                   href="#collapseExample" role="button"
                   aria-expanded="false" aria-controls="collapseExample">
                    <span class="fas fa-chevron-circle-down"></span>
                    Скрыть/отобразить детальную настройку
                </a>
            </p>
            <div class="collapse" id="collapseExample">


                <!--   Is public   -->

                <div class="form-group row">
                    <div class="col-sm-4">
                        <span class="fas fa-eye"></span>
                        Виден всем
                    </div>
                    <div class="col-sm-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   id="cIsPublic" name="isPublic" checked>
                            <label class="form-check-label" for="cIsPublic">
                                Публичный опрос
                            </label>
                        </div>
                    </div>
                </div>

                <hr>

                <!--   Blocking time   -->

                <div class="form-group row">
                    <div class="col-sm-4">
                        <label for="sTimeLimit">
                            <span class="fas fa-stopwatch"></span>
                            Длительность опроса
                        </label>

                    </div>
                    <div class="col-sm-4">
                        <select id="sTimeLimit" name="blockingTime"
                                class="form-control">
                            <option value="none" selected>Без ограничений</option>
                            <option value="hour">Час</option>
                            <option value="day">День</option>
                            <option value="week">Неделя</option>
                            <option value="month">Месяц</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        *После истечения указанного периода оставить свой голос будет нельзя.
                    </div>
                </div>

                <hr>

                <!--   Do shuffle variants   -->

                <div class="form-group row">
                    <div class="col-sm-4">
                        <span class="fas fa-dice"></span>
                        Перемешивание вариантов
                    </div>
                    <div class="col-sm-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   id="cShuffle" name="shuffle">
                            <label class="form-check-label" for="cShuffle">
                                Перемешивать
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <hr>

        <div>
            <input type="submit" name="submit" value="Опубликовать" class="btn btn-primary">
            <a type="button" class="btn btn-secondary" href="main">На главную</a>
        </div>
    </form>
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
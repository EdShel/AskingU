<div class="container">
    <h2>
        Создание опроса
        <span class="fas fa-info-circle btn"
              data-toggle="modal"
              data-target="#rulesModal"
              title="Правила"></span>
        <span class="fas fa-code btn"
              data-toggle="modal"
              data-target="#xmlModal"
              title="Создать из XML"></span>
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
            <a type="button" class="btn btn-secondary" href="main">Вернуться</a>
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

<!-- Create poll via XML -->

<div class="modal fade" id="xmlModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form method="post" action="createPollXML"
                  autocomplete="off">
                <div class="modal-header">
                    <h2>Создание опроса из XML</h2>
                </div>
                <div class="modal-body">
                    <label for="tXML">Введите сюда XML код опроса:</label>
                    <textarea id="tXML" name="pollXML" class="xmlArea"><?php
                        echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<poll question="Какой ваш любимый цвет?">
    <variant>Красный</variant>
    <variant>Жёлтый</variant>
    <variant>Синий</variant>
</poll>
XML;
                        ?></textarea>
                </div>
                <div class="modal-footer">
                    <input type="submit" name="submit" value="Опубликовать" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>
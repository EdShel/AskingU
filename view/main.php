<div class="jumbotron jumbotron-fluid logo">
    <h1>AskingU</h1>
    <p>спроси любого, ответь другому</p>
</div>


<div class="container">

    <div class="row">

        <?php

        require_once "classes/MVC/Controller.php";
        require_once "classes/PollComponent.php";

        foreach (Controller::$Model as $i => $poll){
            echo new PollComponent($poll);
        }

        ?>

    </div>

</div>
<?php

require_once "Poll.php";
require_once "Variant.php";
require_once "Component.php";
require_once "VariantComponent.php";

// Type for element that displays polls on the main page
class PollComponent extends Component
{
    // Poll object to take data from
    public Poll $poll;

    // Constructor
    public function __construct(Poll $poll)
    {
        // Set data source
        $this->poll = $poll;

        // Call parent's constructor
        parent::__construct();
    }

    // Returns HTML code of the element
    public function GetHTML(): string
    {
        // Adding head of the element

        $res = <<<HTML
            <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Участников: {$this->poll->VotesCount}</h5>
                        <p class="card-text poll-question">{$this->poll->Question}</p>
                    </div>
                    <ul class="list-group list-group-flush">
            HTML;

        // Adding variants

        foreach ($this->poll->Variants as $index => $variant) {
            $variantComp = new VariantComponent($variant);

            $res .= <<<HTML
                <li class="list-group-item">
                {$variantComp->GetHTML()}
                </li>
            HTML;
        }

        // Adding footer


        $likeHTML = self::GetHeartHTML($this->poll->IsCurrentUserLiked);
        $likes = $this->poll->Likes;
        $text = $this->poll->CanVote
            ? (!$this->poll->IsCurrentUserLiked
            ? "Оценить"
            : "Убрать оценку")
        : "Оценок (войдите, чтоб оценить)";
        $likeBtn = <<<HTML
<a id="likeBtn{$this->poll->Id}" href="#" 
    class="card-link poll-bottom-icon"
    title="{$text}">{$likeHTML} $likes</a>
HTML;

        if ($this->poll->CanVote) {
            $likeBtn .= <<<HTML
<form id="likePoll{$this->poll->Id}" action="likePoll" method="post" style="display: none">
<input type="hidden" name="pollId" value="{$this->poll->Id}">
</form>
<script>
    $('#likeBtn{$this->poll->Id}').click(function(e){
        e.preventDefault();
        $('#likePoll{$this->poll->Id}').submit();
    });
</script>
HTML;

        }

        $res .= <<<HTML
                </ul>
                <div class="card-body">
                    {$likeBtn}
                    <span class="poll-bottom-icon" title="Просмотров">
                        <i class="fa fa-eye"></i>
                        <span>{$this->poll->Views}</span>
                    </span>
                    <a href="/viewPoll?id={$this->poll->Url}" class="card-link">Подробнее</a>
                </div>
            </div>
        </div>
        
        HTML;

        return $res;
    }


    // HTML code of the heart-shaped symbol of size 1em x 1em
    private static function GetHeartHTML($filled): string
    {
        $clas = $filled ? "fas" : "far";
        return <<<HTML
<i class="{$clas} fa-heart"></i>
HTML;

    }
}
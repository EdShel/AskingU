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
                        <h5 class="card-title">Опрос №{$this->poll->Id}</h5>
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

        $likeHTML = self::GetHeartHTML();
        $likes = $this->poll->Likes;

        $res .= <<<HTML
                </ul>
                <div class="card-body">
                    <a href="#" class="card-link">{$likeHTML} {$likes} Оценить</a>
                    <a href="/viewPoll?id={$this->poll->Url}" class="card-link">Подробнее</a>
HTML;

        $res .= <<<HTML
                </div>
            </div>
        </div>
        
        HTML;

        return $res;
    }


    // HTML code of the heart-shaped symbol of size 1em x 1em
    private static function GetHeartHTML(): string
    {
        return <<<HTML
<span>
    <svg class="bi bi-heart-fill" height="1em" width="1em"
        viewBox="0 0 16 16" fill="currentColor" 
        xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" 
                d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" 
                clip-rule="evenodd"/>
    </svg>
</span>
HTML;

    }
}
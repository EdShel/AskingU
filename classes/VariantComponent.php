<?php

require_once "Component.php";
require_once "User.php";

// Component of the variants in polls
class VariantComponent extends Component
{
    // How many forms has been created on the page.
    private static int $formCounter = 0;

    // Id of the element's form to send vote request
    private string $formId;

    // Data source
    public Variant $Variant;

    // Basic constructor
    public function __construct(Variant $variant)
    {
        // Get id of the form
        self::$formCounter++;
        $this->formId = "variantForm" . self::$formCounter;

        // Set data source
        $this->Variant = $variant;

        // Call parent's constructor with needed JS
        parent::__construct(self::GetJS());
    }

    // JS code required to run this element on the client page
    protected static function GetJS(): string
    {
        // Declaration of the function to submit form by its Id
        return <<<JS
            function submitForm(formId) {
                document.getElementById(formId).submit();
            }
        JS;
    }

    // Returns HTML code of the element
    public function GetHTML(): string
    {
        // Calculate percentage of votes for this variant
        $totalVotes = $this->Variant->Poll->VotesCount;
        if ($totalVotes == 0) {
            $percent = 0;
        } else {
            $percent = 100 * $this->Variant->VotesCount / $totalVotes;
        }

        // If this is the answer of the user, make it yellow
        if ($this->Variant->IsCurrentUsersVote === true) {
            $bgColor = "bg-warning";
        } else {
            $bgColor = "";
        }

        // If the user is authorised
        $userId = User::GetUserIdFromCookies();
        if ($userId != -1) {
            // Then add the handler for voting
            $clickHandler = "onclick=submitForm('$this->formId')";
            $voteable = "voteable";
        } else {
            $clickHandler = "";
            $voteable = "";
        }

        // Actually variant element
        return <<<HTML
<form action="vote" id="{$this->formId}" method="post">
    <input type="hidden" name="pollId" value="{$this->Variant->PollId}">
    <input type="hidden" name="variantId" value="{$this->Variant->Id}">
    <div class="progress variant {$voteable}" title="{$percent}%" {$clickHandler}>
            <div class="progress-bar progress-bar-striped {$bgColor}" 
                style="width:{$percent}%"
                aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
            </div>
            <p>{$this->Variant->Value}</p>
        </div>
</form>
        
HTML;
    }
}
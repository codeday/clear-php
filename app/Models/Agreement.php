<?php
namespace CodeDay\Clear\Models;

use Illuminate\Database\Eloquent;
use CodeDay\Clear\ModelContracts;
use CodeDay\Clear\Models\Batch;

class Agreement extends \Eloquent {
    use Eloquent\SoftDeletingTrait;
    protected $table = 'agreements';

    public function RenderHtmlFor(Batch\Event $event) {
        if ($this->markdown) {
            $html = \Markdown($this->markdown);
        } else {
            $html = $this->html;
        }

        return $this->renderTwigString($html, ['event' => ModelContracts\Event::Model($event, ['internal'])]);
    }

    /**
     * Helper function which renders a Twig string and associated context into HTML.
     *
     * @param           $templateString             String to render as a Twig template
     * @param array     $context                    Context to bind into the string
     * @return string                               Rendered Twig template
     */
    public static function renderTwigString($templateString, $context = [])
    {
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader,
            ['cache' => storage_path() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'twig']);
        return $twig->render($templateString, $context);
    }
}
<?php

namespace CuyZ\Valinor\Mapper\Printer;

use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Node;

use function is_scalar;

/**
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class HtmlTreePrinter implements TreePrinter
{
    // phpcs:disable
    private const CSS2 = <<<CSS
      div.tree .test-details {
        position: relative;

        padding-left: 1.5em;
        margin-left: 0;
        margin-top: 0;
        margin-bottom: 0;

        border-left: thin solid #e8e8e8;
      }
      
      div.tree .test-details.error,
      div.tree .error .test-summary:before {
        border-color: red;
      }

      div.tree .test-summary {
        list-style-type: none;
        padding-top: 0.5em;
        display: inline-block;
      }
      div.tree .test-summary:hover {
        cursor: pointer;
      }

      div.tree summary:after {
        position: absolute;
        width: 1em;
        height: 1em;
        left: -0.5em;
        top: 0.5em;
        content: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUxMiA1MTIiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTM4OS41OSAyMzkuM2gtMTE2Ljl2LTExNi45YzAtOS4yMjItNy40NzctMTYuNjk5LTE2LjY5OS0xNi42OTlzLTE2LjY5OSA3LjQ3Ny0xNi42OTkgMTYuNjk5djExNi45aC0xMTYuOWMtOS4yMjIgMC0xNi42OTkgNy40NzctMTYuNjk5IDE2LjY5OXM3LjQ3NyAxNi42OTkgMTYuNjk5IDE2LjY5OWgxMTYuOXYxMTYuOWMwIDkuMjIyIDcuNDc3IDE2LjY5OSAxNi42OTkgMTYuNjk5czE2LjY5OS03LjQ3NyAxNi42OTktMTYuNjk5di0xMTYuOWgxMTYuOWM5LjIyMiAwIDE2LjY5OS03LjQ3NyAxNi42OTktMTYuNjk5cy03LjQ3Ni0xNi42OTktMTYuNjk5LTE2LjY5OXoiLz48L3N2Zz4K');
      }

      div.tree details[open] > summary:after {
        content: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48IURPQ1RZUEUgc3ZnICBQVUJMSUMKICAgICAgICAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nCiAgICAgICAgJ2h0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCc+PHN2ZyBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAyNTEuODgyIDI1MS44ODIiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDI1MS44OCAyNTEuODgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTE5NC44MiAxMTYuOTRoLTEzNy43NmMtNC45NzEgMC05IDQuMDI5LTkgOXM0LjAyOSA5IDkgOWgxMzcuNzZjNC45NzEgMCA5LTQuMDI5IDktOXMtNC4wMjktOS05LTl6Ii8+PC9zdmc+Cg==');
      }

      div.tree .test-summary:before {
        position: absolute;
        top: 0;
        left: 0;

        width: 1em; /* width of horizontal line */
        height: 1em; /* vertical position of line */
        border-bottom: thin solid #e8e8e8;
        content: "";
      }
      

      div.tree .test-details:last-child {
        border-left: none;
      }

      div.tree .test-details:last-child .test-summary {
        /*border-left: thin solid red;*/
      }

      .test-details .test-details:last-child .test-summary:before {
        /*border-left: thin solid #e8e8e8;*/
      }

      details summary::-webkit-details-marker {
        display:none;
      }
    CSS;

    // phpcs:enable

    private string $html = <<<HTML
    <style>

    </style>
    HTML;

    public function __construct(string $title = 'Test title')
    {
        $this->html .= '<p class="tree">' . $title . '</p>';
    }

    public function print(Node $node): string
    {
        $value = $node->isValid() ? $node->value() : null;

        if (! empty($node->children())) {
            $details = 'details';
            $summary = 'summary';
        } else {
            $details = 'div';
            $summary = 'div';
        }

        $this->html .= <<<HTML
        <$details class="test-details" open>
                    <$summary class="test-summary">{$node->name()}
        HTML;

        if (is_scalar($value)) {
            $this->html .= ' : ' . $node->value();
        }

//        foreach ($node->messages() as $message) {
//            $this->html .= '<div style="color: red">' . $message . '</div>';
//        }

        $this->html .= "</$summary>";

        if (! empty($node->children())) {
            foreach ($node->children() as $subNode) {
                $this->print($subNode);
            }
        }

        $this->html .= "</$details>";

        $name = $node->name();
//        $parent = $node->parent();
//        $messages = $node->messages();

        if (is_scalar($value)) {
//            $this->html .= '<li>' . $name . ' : ' . $value . '</li>';
        } else {
//            if ($node->isRoot()) {
//                $this->html .= '&#8734;<ul class="tree">';
//            } else {
//                $this->html .= '<li>' . $name;
//            }
        }

        foreach ($node->messages() as $message) {
            $str = (string)$message;
            if ($message instanceof HasCode) {
                $str .= ' (' . $message->code() . ')';
            }
            $this->html .= '<span style="color: red;">' . $str . '</span>';
        }

//        $filter = ClassNameMessageFilter::for(Error::class);
//
//        if ($messages->has($filter)) {
//            foreach ($messages->filter($filter) as $message) {
//                $str = (string)$message;
//                if ($message instanceof HasCode) {
//                    $str .= ' (' . $message->code() . ')';
//                }
//                $this->html .= '<li style="color: red;">' . $str . '</li>';
//            }
//        }

        return $this->html();
    }

    public function html(): string
    {
        $css2 = self::CSS2;

        return <<<HTML
        <style>$css2</style>
        <div class="tree">
                    Test
                    $this->html
        </div>
        HTML;
    }
}

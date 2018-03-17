<?php

/* widgets/attendees.twig */
class __TwigTemplate_c2cee026cb06aaff003fe41837b5ca4af14ecf50233b0bec1670bbb9ba122c91 extends TwigBridge\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
    }

    // line 1
    public function getattendees($__attendees__ = null, $__hide_event__ = null, $__date__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "attendees" => $__attendees__,
            "hide_event" => $__hide_event__,
            "date" => $__date__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2
            echo "    ";
            $context["s"] = $this;
            // line 3
            echo "    <ul class=\"attendee-list\">
        ";
            // line 4
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["attendees"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["attendee"]) {
                // line 5
                echo "            ";
                echo $context["s"]->getattendee($context["attendee"], ($context["hide_event"] ?? null), ($context["date"] ?? null));
                echo "
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['attendee'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 7
            echo "    </ul>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 9
    public function getattendee($__attendee__ = null, $__hide_event__ = null, $__date__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "attendee" => $__attendee__,
            "hide_event" => $__hide_event__,
            "date" => $__date__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 10
            echo "    <li class=\"";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "type", array()), "html", null, true);
            echo "\">
        <a href=\"/event/";
            // line 11
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "batches_event_id", array()), "html", null, true);
            echo "/registrations/attendee/";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "id", array()), "html", null, true);
            echo "\">
            <img src=\"";
            // line 12
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "profile_image_safe", array()), "html", null, true);
            echo "\" alt=\"";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "name", array()), "html", null, true);
            echo "\"/>
            <span class=\"name\">
                <span class=\"first\">";
            // line 14
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "first_name", array()), "html", null, true);
            echo "</span>
                <span class=\"last\">";
            // line 15
            echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "last_name", array()), "html", null, true);
            echo "</span>
            </span>
            <span class=\"details\">
                ";
            // line 18
            if (($context["date"] ?? null)) {
                // line 19
                echo "                    <span class=\"ticket\">
                        ";
                // line 20
                if (($this->getAttribute(($context["attendee"] ?? null), "deleted_at", array()) != null)) {
                    echo "✘";
                } elseif ($this->getAttribute(($context["attendee"] ?? null), "checked_in_at", array())) {
                    echo "✓";
                }
                // line 21
                echo "                        ";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute(($context["attendee"] ?? null), "event", array()), "batch", array()), "starts_at", array()), "timestamp", array()), "n/y"), "html", null, true);
                echo "
                    </span>
                ";
            } else {
                // line 24
                echo "                    <span class=\"ticket\">
                        ";
                // line 25
                if (($this->getAttribute(($context["attendee"] ?? null), "deleted_at", array()) != null)) {
                    echo "✘";
                } elseif ($this->getAttribute(($context["attendee"] ?? null), "checked_in_at", array())) {
                    echo "✓";
                }
                // line 26
                echo "                        ";
                echo twig_escape_filter($this->env, $this->getAttribute(($context["attendee"] ?? null), "type", array()), "html", null, true);
                echo "
                    </span>
                ";
            }
            // line 29
            echo "                ";
            if ( !($context["hide_event"] ?? null)) {
                // line 30
                echo "                    <span class=\"event\">";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["attendee"] ?? null), "event", array()), "webname", array()), "html", null, true);
                echo " </span>
                ";
            }
            // line 32
            echo "            </span>
            <span class=\"devices\">
                ";
            // line 34
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(array(0 => "sms", 1 => "messenger", 2 => "app"));
            foreach ($context['_seq'] as $context["_key"] => $context["deviceType"]) {
                // line 35
                echo "                    ";
                if ($this->getAttribute(($context["attendee"] ?? null), "hasDeviceType", array(0 => $context["deviceType"]), "method")) {
                    // line 36
                    echo "                        <span class=\"device device-";
                    echo twig_escape_filter($this->env, $context["deviceType"], "html", null, true);
                    echo "\" title=\"Attendee subscribed to notifications via ";
                    echo twig_escape_filter($this->env, $context["deviceType"], "html", null, true);
                    echo "\"></span>
                    ";
                }
                // line 38
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['deviceType'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 39
            echo "            </span>
        </a>
    </li>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "widgets/attendees.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  180 => 39,  174 => 38,  166 => 36,  163 => 35,  159 => 34,  155 => 32,  149 => 30,  146 => 29,  139 => 26,  133 => 25,  130 => 24,  123 => 21,  117 => 20,  114 => 19,  112 => 18,  106 => 15,  102 => 14,  95 => 12,  89 => 11,  84 => 10,  70 => 9,  54 => 7,  45 => 5,  41 => 4,  38 => 3,  35 => 2,  21 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "widgets/attendees.twig", "");
    }
}

<?php

/* widgets/event-hud.twig */
class __TwigTemplate_0bb6c122df314b34144331fe5f84f727e30c4a3d2567b8f72bba9d7c69f7c91d extends TwigBridge\Twig\Template
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
    public function gethud($__event__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "event" => $__event__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2
            echo "    ";
            $context["status"] = "closed";
            // line 3
            echo "    ";
            if (($this->getAttribute(($context["event"] ?? null), "allow_registrations_calculated", array()) && ($this->getAttribute(($context["event"] ?? null), "remaining_registrations", array()) > 0))) {
                // line 4
                echo "        ";
                $context["status"] = "open";
                // line 5
                echo "    ";
            } elseif ($this->getAttribute(($context["event"] ?? null), "allow_registrations_calculated", array())) {
                // line 6
                echo "        ";
                $context["status"] = "sold-out";
                // line 7
                echo "    ";
            } elseif ($this->getAttribute(($context["event"] ?? null), "venue", array())) {
                // line 8
                echo "        ";
                $context["status"] = "venue";
                // line 9
                echo "    ";
            }
            // line 10
            echo "    <a href=\"/event/";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["event"] ?? null), "id", array()), "html", null, true);
            echo "\" class=\"event-hud ";
            echo twig_escape_filter($this->env, ($context["status"] ?? null), "html", null, true);
            echo "\">
        <div class=\"image\" style=\"background-image: url(/api/i/region/";
            // line 11
            echo twig_escape_filter($this->env, $this->getAttribute(($context["event"] ?? null), "region_id", array()), "html", null, true);
            echo "_512,256.jpg\"></div>
        <div class=\"data\">
            <span class=\"name\">";
            // line 13
            echo twig_escape_filter($this->env, $this->getAttribute(($context["event"] ?? null), "name", array()), "html", null, true);
            echo "</span>
            <ul class=\"stats\">
                ";
            // line 15
            if ( !$this->getAttribute(($context["event"] ?? null), "allow_registrations_calculated", array())) {
                // line 16
                echo "                    <li class=\"primary\">
                        <span class=\"number\">";
                // line 17
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["event"] ?? null), "notify", array()), "count", array()), "html", null, true);
                echo "</span>
                        <span class=\"label\">Subscribers</span>
                    </li>
                ";
            } elseif ( !$this->getAttribute($this->getAttribute($this->getAttribute(            // line 20
($context["event"] ?? null), "batch", array()), "starts_at", array()), "isFuture", array())) {
                // line 21
                echo "                    <li class=\"primary\">
                        <span class=\"number\">";
                // line 22
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["event"] ?? null), "attendees_here", array()), "count", array()), "html", null, true);
                echo "</span>
                        <span class=\"label\">Here</span>
                    </li>
                    <li>
                        <span class=\"number\">";
                // line 26
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["event"] ?? null), "registrations", array()), "count", array()), "html", null, true);
                echo "</span>
                        <span class=\"label\">Registered</span>
                    </li>
                ";
            } else {
                // line 30
                echo "                    <li class=\"primary\">
                        <span class=\"number\">";
                // line 31
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["event"] ?? null), "registrations", array()), "count", array()), "html", null, true);
                echo "</span>
                        <span class=\"label\">Registered</span>
                    </li>
                    <li>
                        <span class=\"number\">";
                // line 35
                echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute(($context["event"] ?? null), "registrations_today", array())), "html", null, true);
                echo "</span>
                        <span class=\"label\">Today</span>
                    </li>
                    <li>
                        <span class=\"number\">";
                // line 39
                echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute(($context["event"] ?? null), "registrations_this_week", array())), "html", null, true);
                echo "</span>
                        <span class=\"label\">This Week</span>
                    </li>
                ";
            }
            // line 43
            echo "            </ul>
        </div>
    </a>
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
        return "widgets/event-hud.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  128 => 43,  121 => 39,  114 => 35,  107 => 31,  104 => 30,  97 => 26,  90 => 22,  87 => 21,  85 => 20,  79 => 17,  76 => 16,  74 => 15,  69 => 13,  64 => 11,  57 => 10,  54 => 9,  51 => 8,  48 => 7,  45 => 6,  42 => 5,  39 => 4,  36 => 3,  33 => 2,  21 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "widgets/event-hud.twig", "");
    }
}

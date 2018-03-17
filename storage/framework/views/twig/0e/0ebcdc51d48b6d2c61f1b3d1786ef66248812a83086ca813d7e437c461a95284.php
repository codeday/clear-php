<?php

/* /app/resources/views/dashboard.twig */
class __TwigTemplate_6b1e98a0d9eae428b82676a92d2ed37e02176e4dffc051b71045c7093e5dcd23 extends TwigBridge\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("template.twig", "/app/resources/views/dashboard.twig", 1);
        $this->blocks = array(
            'section' => array($this, 'block_section'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "template.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 3
        $context["a"] = $this->loadTemplate("widgets/attendees.twig", "/app/resources/views/dashboard.twig", 3);
        // line 4
        $context["countdown"] = $this->loadTemplate("widgets/countdown.twig", "/app/resources/views/dashboard.twig", 4);
        // line 5
        $context["event_hud"] = $this->loadTemplate("widgets/event-hud.twig", "/app/resources/views/dashboard.twig", 5);
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_section($context, array $blocks = array())
    {
        echo "dashboard";
    }

    // line 6
    public function block_content($context, array $blocks = array())
    {
        // line 7
        echo "    ";
        echo $context["countdown"]->getfancy(($context["managed_batch"] ?? null));
        echo "
    <section class=\"deadlines\">
        <div>
            ";
        // line 10
        $context["x_0"] = $this->getAttribute($this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "addWeeks", array(0 =>  -12), "method"), "timestamp", array());
        // line 11
        echo "            ";
        $context["x_1"] = $this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "timestamp", array());
        // line 12
        echo "            ";
        $context["now"] = twig_date_format_filter($this->env, null, "U");
        // line 13
        echo "            ";
        $context["percent"] = (100 * ((($context["now"] ?? null) - ($context["x_0"] ?? null)) / (($context["x_1"] ?? null) - ($context["x_0"] ?? null))));
        // line 14
        echo "            <span class=\"now\" style=\"left: ";
        echo twig_escape_filter($this->env, max(0, min(100, ($context["percent"] ?? null))), "html", null, true);
        echo "%\"></span>
            <table><tbody><tr>
                <td>
                    <div class=\"normal\">
                        <span class=\"date\">";
        // line 18
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "addWeeks", array(0 =>  -12), "method"), "timestamp", array()), "F j"), "html", null, true);
        echo "</span>
                        <span class=\"description\">Organizing starts</span>
                    </div>
                </td>
                <td>
                    <div class=\"normal\">
                        <span class=\"date\">";
        // line 24
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "addWeeks", array(0 =>  -8), "method"), "timestamp", array()), "F j"), "html", null, true);
        echo "</span>
                        <span class=\"description\">Venues due</span>
                    </div>
                </td>
                <td>
                    <div class=\"normal\">
                        <span class=\"date\">";
        // line 30
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "addWeeks", array(0 =>  -4), "method"), "timestamp", array()), "F j"), "html", null, true);
        echo "</span>
                        <span class=\"description\">Promotion push starts</span>
                    </div>
                    <div class=\"last\">
                        <span class=\"date\">";
        // line 34
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "timestamp", array()), "F j"), "html", null, true);
        echo "</span>
                        <span class=\"description\">CodeDay</span>
                    </div>
                </td>
            </tr></tbody></table>
        </div>
    </section>
    ";
        // line 41
        if ((twig_length_filter($this->env, $this->getAttribute(($context["me"] ?? null), "current_managed_events", array())) > 0)) {
            // line 42
            echo "        <section class=\"my-events\">
            <ul>
                ";
            // line 44
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["me"] ?? null), "current_managed_events", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["event"]) {
                // line 45
                echo "                    <li>";
                echo $context["event_hud"]->gethud($context["event"]);
                echo "</li>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['event'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 47
            echo "            </ul>
        </section>
    ";
        }
        // line 50
        echo "        
    <section class=\"latest\">
        <section class=\"recent\">
            <h3>New Registrations</h3>
            ";
        // line 54
        echo $context["a"]->getattendees(twig_slice($this->env, ($context["recent_registrations"] ?? null), 0, 4));
        echo "
        </section>
        <section class=\"leaderboard\">
            <h3>Weekly Leaderboard</h3>
            <ol>
                ";
        // line 59
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_slice($this->env, ($context["leaderboard"] ?? null), 0, 10));
        foreach ($context['_seq'] as $context["_key"] => $context["event"]) {
            // line 60
            echo "                    <li>
                        <strong>";
            // line 61
            echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "name", array()), "html", null, true);
            echo ":</strong>
                        &#9652;";
            // line 62
            echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations_this_week", array())), "html", null, true);
            echo " this week
                    </li>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['event'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 65
        echo "            </ol>
        </section>
        <section class=\"news\">
            <h3>News</h3>
            ";
        // line 69
        if ($this->getAttribute(($context["managed_batch"] ?? null), "news", array())) {
            // line 70
            echo "                ";
            echo call_user_func_array($this->env->getFilter('markdown')->getCallable(), array($this->getAttribute(($context["managed_batch"] ?? null), "news", array())));
            echo "
            ";
        } else {
            // line 72
            echo "                <p>(No news.)</p>
            ";
        }
        // line 74
        echo "        </section>
    </section>

    ";
        // line 77
        if ($this->getAttribute(($context["me"] ?? null), "is_admin", array())) {
            // line 78
            echo "        <section class=\"batch-status\">
            ";
            // line 79
            $this->loadTemplate("widgets/batch-status.twig", "/app/resources/views/dashboard.twig", 79)->display($context);
            // line 80
            echo "        </section>
    ";
        }
    }

    public function getTemplateName()
    {
        return "/app/resources/views/dashboard.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  194 => 80,  192 => 79,  189 => 78,  187 => 77,  182 => 74,  178 => 72,  172 => 70,  170 => 69,  164 => 65,  155 => 62,  151 => 61,  148 => 60,  144 => 59,  136 => 54,  130 => 50,  125 => 47,  116 => 45,  112 => 44,  108 => 42,  106 => 41,  96 => 34,  89 => 30,  80 => 24,  71 => 18,  63 => 14,  60 => 13,  57 => 12,  54 => 11,  52 => 10,  45 => 7,  42 => 6,  36 => 2,  32 => 1,  30 => 5,  28 => 4,  26 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "/app/resources/views/dashboard.twig", "");
    }
}

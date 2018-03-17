<?php

/* widgets/batch-status.twig */
class __TwigTemplate_98e0935ff03149a96cf637bb6da6838cef4e66f3acfc51ad301c345533c37c41 extends TwigBridge\Twig\Template
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
        // line 1
        if ($this->getAttribute(($context["managed_batch"] ?? null), "is_loaded", array())) {
            // line 2
            echo "    ";
            $context["batches"] = ($context["loaded_batches"] ?? null);
        } else {
            // line 4
            echo "    ";
            $context["batches"] = array(0 => ($context["managed_batch"] ?? null));
        }
        // line 6
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["batches"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["batch"]) {
            // line 7
            echo "    <h2>";
            echo twig_escape_filter($this->env, $this->getAttribute($context["batch"], "name", array()), "html", null, true);
            echo "</h2>
    <table class=\"status\">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th><abbr title=\"Venue Set\">VEN</abbr></th>
                <th><abbr title=\"Registrations Open\">GO</abbr></th>
                <th><abbr title=\"Shipping Address Set\">SHA</abbr></th>
                <th><abbr title=\"Total Registrations\">Tot</abbr></th>
                <th><abbr title=\"Registrations Today\">TDy</abbr></th>
                <th><abbr title=\"Registrations This Week\">TWk</abbr></th>
                <th class=\"tickets\">Tix</th>
                <th class=\"sponsors\">Spns</th>
                <th class=\"revenue\">Rev</th>
                <th class=\"cogs\">COGS</th>
                <th class=\"net\">Net</th>
            </tr>
        </thead>
        <tbody>
            ";
            // line 27
            $context["ticket_rev"] = 0;
            // line 28
            echo "            ";
            $context["sponsor_rev"] = 0;
            // line 29
            echo "            ";
            $context["rev"] = 0;
            // line 30
            echo "            ";
            $context["costs"] = 0;
            // line 31
            echo "
            ";
            // line 32
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["batch"], "events", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["event"]) {
                // line 33
                echo "                <tr>
                    <th><a href=\"/event/";
                // line 34
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "id", array()), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "name", array()), "html", null, true);
                echo "</a></th>
                    <td><a href=\"https://s5.srnd.org/user/";
                // line 35
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "manager_username", array()), "html", null, true);
                echo "\" target=\"_blank\">";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "manager", array()), "name", array()), "html", null, true);
                echo "</a></td>
                    <td ";
                // line 36
                if ($this->getAttribute($context["event"], "venue", array())) {
                    echo "class=\"okay\"";
                }
                echo "><span><span></span></span></td>
                    <td ";
                // line 37
                if ($this->getAttribute($context["event"], "allow_registrations", array())) {
                    echo "class=\"okay\"";
                }
                echo "><span><span></span></span></td>
                    <td ";
                // line 38
                if ($this->getAttribute($context["event"], "ship_address_1", array())) {
                    echo "class=\"okay\"";
                }
                echo "><span><span></span></span></td>
                    <td>";
                // line 39
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "registrations", array()), "count", array()), "html", null, true);
                echo "</td>
                    <td>";
                // line 40
                echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations_today", array())), "html", null, true);
                echo "</td>
                    <td>";
                // line 41
                echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations_this_week", array())), "html", null, true);
                echo "</td>
                    <td class=\"tickets\">\$";
                // line 42
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["event"], "ticket_revenue", array()), 0), "html", null, true);
                echo "</td>
                    <td class=\"sponsors\">\$";
                // line 43
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["event"], "sponsor_revenue", array()), 0), "html", null, true);
                echo "</td>
                    <td class=\"revenue\">\$";
                // line 44
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["event"], "revenue", array()), 0), "html", null, true);
                echo "</td>
                    <td class=\"cogs\">\$";
                // line 45
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["event"], "costs", array()), 0), "html", null, true);
                echo "</td>
                    <td class=\"net ";
                // line 46
                if ((($this->getAttribute($context["event"], "revenue", array()) - $this->getAttribute($context["event"], "costs", array())) < 0)) {
                    echo "negative";
                }
                echo "\">
                        \$";
                // line 47
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, abs(($this->getAttribute($context["event"], "revenue", array()) - $this->getAttribute($context["event"], "costs", array()))), 0), "html", null, true);
                echo "</td>

                    ";
                // line 49
                $context["ticket_rev"] = (($context["ticket_rev"] ?? null) + $this->getAttribute($context["event"], "ticket_revenue", array()));
                // line 50
                echo "                    ";
                $context["sponsor_rev"] = (($context["sponsor_rev"] ?? null) + $this->getAttribute($context["event"], "sponsor_revenue", array()));
                // line 51
                echo "                    ";
                $context["rev"] = (($context["rev"] ?? null) + $this->getAttribute($context["event"], "revenue", array()));
                // line 52
                echo "                    ";
                $context["costs"] = (($context["costs"] ?? null) + $this->getAttribute($context["event"], "costs", array()));
                // line 53
                echo "
                </tr>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['event'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 56
            echo "                <tr>
                    <th>TOTAL</th>
                    <td colspan=\"7\">&nbsp;</td>
                    <td class=\"tickets\">\$";
            // line 59
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($context["ticket_rev"] ?? null), 0), "html", null, true);
            echo "</td>
                    <td class=\"sponsors\">\$";
            // line 60
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($context["sponsor_rev"] ?? null), 0), "html", null, true);
            echo "</td>
                    <td class=\"revenue\">\$";
            // line 61
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($context["rev"] ?? null), 0), "html", null, true);
            echo "</td>
                    <td class=\"cogs\">\$";
            // line 62
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($context["costs"] ?? null), 0), "html", null, true);
            echo "</td>
                    <td class=\"net ";
            // line 63
            if (((($context["rev"] ?? null) - ($context["costs"] ?? null)) < 0)) {
                echo "negative";
            }
            echo "\">
                        \$";
            // line 64
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, abs((($context["rev"] ?? null) - ($context["costs"] ?? null))), 0), "html", null, true);
            echo "
                    </td>
                </tr>

        </tbody>
    </table>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['batch'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "widgets/batch-status.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  193 => 64,  187 => 63,  183 => 62,  179 => 61,  175 => 60,  171 => 59,  166 => 56,  158 => 53,  155 => 52,  152 => 51,  149 => 50,  147 => 49,  142 => 47,  136 => 46,  132 => 45,  128 => 44,  124 => 43,  120 => 42,  116 => 41,  112 => 40,  108 => 39,  102 => 38,  96 => 37,  90 => 36,  84 => 35,  78 => 34,  75 => 33,  71 => 32,  68 => 31,  65 => 30,  62 => 29,  59 => 28,  57 => 27,  33 => 7,  29 => 6,  25 => 4,  21 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "widgets/batch-status.twig", "");
    }
}

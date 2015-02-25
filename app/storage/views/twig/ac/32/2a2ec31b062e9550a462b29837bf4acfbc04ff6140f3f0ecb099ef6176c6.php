<?php

/* /home/tj/Clear/app/config/../views/event/index.twig */
class __TwigTemplate_ac322a2ec31b062e9550a462b29837bf4acfbc04ff6140f3f0ecb099ef6176c6 extends TwigBridge\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        try {
            $this->parent = $this->env->loadTemplate("template.twig");
        } catch (Twig_Error_Loader $e) {
            $e->setTemplateFile($this->getTemplateName());
            $e->setTemplateLine(1);

            throw $e;
        }

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'section' => array($this, 'block_section'),
            'subnav' => array($this, 'block_subnav'),
            'content' => array($this, 'block_content'),
            'scripts' => array($this, 'block_scripts'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "template.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_title($context, array $blocks = array())
    {
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "full_name", array()), "html", null, true);
    }

    // line 3
    public function block_section($context, array $blocks = array())
    {
        echo "event";
    }

    // line 4
    public function block_subnav($context, array $blocks = array())
    {
        $this->env->loadTemplate("event/subnav.twig")->display($context);
    }

    // line 5
    public function block_content($context, array $blocks = array())
    {
        // line 6
        echo "    ";
        if ($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_registrations_calculated", array())) {
            // line 7
            echo "        <div id=\"registrations-over-time\" style=\"width:100%\"></div>
    ";
        }
        // line 9
        echo "
    <section class=\"status\">
        <section class=\"hud\">
            <ul>
                <li>
                    <span class=\"label\">Venue</span>
                    ";
        // line 15
        if ($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "venue", array())) {
            // line 16
            echo "                        <span class=\"status success\">&#10003;</span>
                    ";
        } else {
            // line 18
            echo "                        <span class=\"status failure\">&#10007;</span>
                    ";
        }
        // line 20
        echo "                </li>
                <li>
                    <span class=\"label\">Ship Info</span>
                    ";
        // line 23
        if ($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "ship_address_1", array())) {
            // line 24
            echo "                        <span class=\"status success\">&#10003;</span>
                    ";
        } else {
            // line 26
            echo "                        <span class=\"status failure\">&#10007;</span>
                    ";
        }
        // line 28
        echo "                </li>
                <li>
                    <span class=\"label\">Sponsors</span>
                    <span class=\"value\">";
        // line 31
        echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "sponsors", array())), "html", null, true);
        echo "</span>
                </li>
                <li>
                    <span class=\"label\">Attendees</span>
                    <span class=\"value\">";
        // line 35
        echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "registrations", array())), "html", null, true);
        echo "</span>
                </li>
                <li>
                    <span class=\"label\">Revenue</span>
                    <span class=\"value\">\$";
        // line 39
        echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "revenue", array()), 2), "html", null, true);
        echo "</span>
                </li>
            </ul>
        </section>
        <section class=\"toggles\">
            <form method=\"post\" action=\"/event/";
        // line 44
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/update-registration-status\">
                <div class=\"switch enableddisabled\">
                    <input type=\"checkbox\" name=\"allow_registrations\" id=\"allow_registrations\" value=\"1\"
                           ";
        // line 47
        if ($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_registrations", array())) {
            echo "checked";
        }
        // line 48
        echo "                           ";
        if ( !$this->getAttribute((isset($context["event"]) ? $context["event"] : null), "venue", array())) {
            echo "disabled";
        }
        echo " />
                    <label for=\"allow_registrations\">
                        <span class=\"inner\"></span>
                        <span class=\"switch\">&#10005;</span>
                    </label>
                    <span class=\"label
                        ";
        // line 54
        if ($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_registrations", array())) {
            echo "on";
        }
        // line 55
        echo "                        \">Event</span>
                </div>
            </form>

            <form method=\"post\" action=\"/event/";
        // line 59
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/update-waitlist-status\">
                <div class=\"switch openclosed\">
                    <input type=\"checkbox\" name=\"allow_waitlist_signups\" id=\"allow_waitlist_signups\" value=\"1\"
                           ";
        // line 62
        if (($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_waitlist_signups", array()) && $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_registrations_calculated", array()))) {
            echo "checked";
        }
        // line 63
        echo "                           ";
        if ( !$this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_registrations_calculated", array())) {
            echo "disabled";
        }
        echo " />
                    <label for=\"allow_waitlist_signups\">
                        <span class=\"inner\"></span>
                        <span class=\"switch\">&#10005;</span>
                    </label>
                    <span class=\"label
                        ";
        // line 69
        if (($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_waitlist_signups", array()) && $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "allow_registrations_calculated", array()))) {
            echo "on";
        }
        echo "\"
                        >Waitlist</span>
                </div>
            </form>
        </section>
    </section>

    <form action=\"/event/";
        // line 76
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/notes\" method=\"post\" class=\"notes\">
        <h3>Notes</h3>
        <div class=\"field\">
            <label for=\"notes\">Notes</label>
            <textarea name=\"notes\">";
        // line 80
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "notes", array()), "html", null, true);
        echo "</textarea>
            <span class=\"help\">(For your internal use.)</span>
        </div>
        <input type=\"submit\" value=\"Save\"/>
    </form>
";
    }

    // line 86
    public function block_scripts($context, array $blocks = array())
    {
        // line 87
        echo "    <script type=\"text/javascript\">
        \$('#allow_registrations, #allow_waitlist_signups').on('change', function() {
            \$(this).parents('form').submit();
        });
    </script>
    <style type=\"text/css\">
        #registrations-over-time .axis path,
        #registrations-over-time .axis line {
            fill: none;
            stroke: #000;
            shape-rendering: crispEdges;
        }

        #registrations-over-time .line {
            fill: none;
            stroke: #000;
            stroke-width: 2px;
        }

        #registrations-over-time rect {
            fill: #ccc;
        }
    </style>
    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/d3/3.4.11/d3.min.js\"></script>
    <script type=\"text/javascript\">

        var elem = \$('#registrations-over-time');

        var margin = {top: 20, right: 20, bottom: 30, left: 50},
                width = elem.width() - margin.left - margin.right,
                height = (elem.width() * 0.4) - margin.top - margin.bottom;

        var parseDate = d3.time.format(\"%d-%b-%y\").parse;

        var x = d3.time.scale()
                .range([0, width]);

        var y = d3.scale.linear()
                .range([height, 0]);

        var xAxis = d3.svg.axis()
                .scale(x)
                .orient(\"bottom\");

        var yAxis = d3.svg.axis()
                .scale(y)
                .orient(\"left\");

        var line = d3.svg.line()
                .x(function(d) { return x(d.date); })
                .y(function(d) { return y(d.registrations); });

        var svg = d3.select(\"#registrations-over-time\").append(\"svg\")
                .attr(\"width\", width + margin.left + margin.right)
                .attr(\"height\", height + margin.top + margin.bottom)
                .append(\"g\")
                .attr(\"transform\", \"translate(\" + margin.left + \",\" + margin.top + \")\");

        d3.csv(\"/event/";
        // line 145
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/chartdata.csv\", function(error, data) {
            data.forEach(function(d) {
                d.date = parseDate(d.date);
                d.delta = +d.delta;
                d.registrations = +d.registrations;
            });

            x.domain([
                d3.min(data, function(d) { return d.date; }),
                parseDate('";
        // line 154
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "starts_at", array()), "j-M-y"), "js"), "html", null, true);
        echo "')
            ]);
            y.domain([0, Math.max(";
        // line 156
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "max_registrations", array()), "js"), "html", null, true);
        echo ", d3.max(data, function(d) { return d.registrations; }))]);

            var width = x(data[1].date) - x(data[0].date);

            svg.selectAll('rect')
                    .data(data)
                    .enter()
                    .append('rect')
                    .attr('x', function(d) { return x(d.date) - width; })
                    .attr('y', function(d) { return y(d.delta); })
                    .attr(\"width\", width)
                    .attr(\"height\", function(d) { return height - y(d.delta); });

            svg.append(\"path\")
                    .datum(data)
                    .attr(\"class\", \"line\")
                    .attr(\"d\", line);


            svg.append(\"g\")
                    .attr(\"class\", \"x axis\")
                    .attr(\"transform\", \"translate(0,\" + height + \")\")
                    .call(xAxis);

            svg.append(\"g\")
                    .attr(\"class\", \"y axis\")
                    .call(yAxis)
                    .append(\"text\")
                    .attr(\"transform\", \"rotate(-90)\")
                    .attr(\"y\", 6)
                    .attr(\"dy\", \".71em\")
                    .style(\"text-anchor\", \"end\")
                    .text(\"Registrations\");
        });

    </script>
";
    }

    public function getTemplateName()
    {
        return "/home/tj/Clear/app/config/../views/event/index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  291 => 156,  286 => 154,  274 => 145,  214 => 87,  211 => 86,  201 => 80,  194 => 76,  182 => 69,  170 => 63,  166 => 62,  160 => 59,  154 => 55,  150 => 54,  138 => 48,  134 => 47,  128 => 44,  120 => 39,  113 => 35,  106 => 31,  101 => 28,  97 => 26,  93 => 24,  91 => 23,  86 => 20,  82 => 18,  78 => 16,  76 => 15,  68 => 9,  64 => 7,  61 => 6,  58 => 5,  52 => 4,  46 => 3,  40 => 2,  11 => 1,);
    }
}

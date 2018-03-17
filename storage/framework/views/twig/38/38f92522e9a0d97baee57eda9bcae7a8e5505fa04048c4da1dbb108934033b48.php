<?php

/* template.twig */
class __TwigTemplate_ed9bdebbbf0d1ac8e4202f2901db9434637aa2e4f0ac39b423ac36f47e37e058 extends TwigBridge\Twig\Template
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
        echo "<!doctype html>
<html lang=\"en\">
<head>
    <title>
        ";
        // line 5
        if (        $this->renderBlock("title", $context, $blocks)) {
            // line 6
            echo "            ";
            $this->displayBlock("title", $context, $blocks);
            echo " //
        ";
        }
        // line 8
        echo "        Clear // CodeDay
    </title>
    <link rel=\"stylesheet\" href=\"/assets/css/mobile-keyframes.css\"/>
    <link rel=\"stylesheet\" href=\"/assets/css/style.css?v13\" />
    <link rel=\"stylesheet\" href=\"/assets/css/sweet-alert.css\"/>
    <link rel=\"stylesheet\" href=\"/assets/fonts/avenir-next.css\"/>
    <link id=\"favicon\" rel=\"icon\" type=\"image/svg\" href=\"/assets/img/CodeDayHeart.svg?v3\" />
    <link rel=\"shortcut icon\" href=\"/assets/img/logo-square.png?agood\" />
    <meta name=\"viewport\" content=\"width=device-width\" />
    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />
    <meta name=\"mobile-web-app-capable\" content=\"yes\" />
    <meta name=\"slack-app-id\" content=\"A5ANMB0CF\">
    <script type=\"text/javascript\" src=\"https://maps.googleapis.com/maps/api/js?key=AIzaSyCVggXa9btjGmzqgUim-1HjmnMtbF3UNms&sensor=false&libraries=visualization,places\"></script>
    <script type=\"text/javascript\">
        (function(){
            var simpleMapStyle = [
                {
                    \"featureType\": \"landscape\",
                    \"stylers\": [
                        { \"visibility\": \"off\" }
                    ]
                },{
                    \"featureType\": \"administrative\",
                    \"stylers\": [
                        { \"visibility\": \"off\" }
                    ]
                },{
                    \"featureType\": \"administrative.country\",
                    \"elementType\": \"geometry\",
                    \"stylers\": [
                        { \"visibility\": \"on\" }
                    ]
                },{
                    \"featureType\": \"administrative.province\",
                    \"elementType\": \"geometry\",
                    \"stylers\": [
                        { \"visibility\": \"on\" }
                    ]
                },{
                    \"featureType\": \"administrative.province\",
                    \"elementType\": \"labels\",
                    \"stylers\": [
                        { \"visibility\": \"on\" }
                    ]
                },{
                    \"featureType\": \"road\",
                    \"stylers\": [
                        { \"visibility\": \"off\" }
                    ]
                },{
                    \"featureType\": \"transit\",
                    \"stylers\": [
                        { \"visibility\": \"off\" }
                    ]
                },{
                    \"featureType\": \"poi\",
                    \"stylers\": [
                        { \"visibility\": \"off\" }
                    ]
                },{
                    \"featureType\": \"landscape\",
                    \"elementType\": \"geometry\",
                    \"stylers\": [
                        { \"saturation\": -100 },
                        { \"lightness\": 100 },
                        { \"visibility\": \"on\" }
                    ]
                },{
                    \"featureType\": \"water\",
                    \"stylers\": [
                        { \"saturation\": -100 },
                        { \"lightness\": 27 }
                    ]
                }
            ];
            window.defaultMapOptions = {
                center: new google.maps.LatLng(38.216194740798436, -95.59806542968748),
                zoom: 4,
                disableDefaultUI: true,
                styles: simpleMapStyle,
                scrollwheel: false,
                draggable: false
            }
        })();
    </script>
    <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js\"></script>
    <script type=\"text/javascript\">
      !function(a,b,c,d,e,f,g){a.RaygunObject=e,a[e]=a[e]||function(){
      (a[e].o=a[e].o||[]).push(arguments)},f=b.createElement(c),g=b.getElementsByTagName(c)[0],
      f.async=1,f.src=d,g.parentNode.insertBefore(f,g)}(window,document,\"script\",\"//cdn.raygun.io/raygun4js/raygun.min.js\",\"rg4js\");
    </script>
</head>
<body class=\"";
        // line 100
        $this->displayBlock("section", $context, $blocks);
        echo "\">
    ";
        // line 101
        if (($context["status_message"] ?? null)) {
            // line 102
            echo "        <div class=\"info status\">";
            echo twig_escape_filter($this->env, ($context["status_message"] ?? null), "html", null, true);
            echo "</div>
    ";
        } elseif (        // line 103
($context["error"] ?? null)) {
            // line 104
            echo "        <div class=\"info error\">";
            echo twig_escape_filter($this->env, ($context["error"] ?? null), "html", null, true);
            echo "</div>
    ";
        } elseif (        // line 105
($context["old_batch"] ?? null)) {
            // line 106
            echo "        <div class=\"info old-batch\">Warning: you are editing an old batch. <a href=\"/batch/change\">Switch?</a></div>
    ";
        }
        // line 108
        echo "    <header>
        <section class=\"menu-handle\">
            <button class=\"menu-icon\">
                <span class=\"menu-layer\"></span>
            </button>
        </section>
        <h1";
        // line 114
        if (($context["event"] ?? null)) {
            echo " class=\"with-region\"";
        }
        echo ">
            <span class=\"event\">CodeDay</span>
            <span class=\"tool\">Clear</span>
            ";
        // line 117
        if (($context["event"] ?? null)) {
            // line 118
            echo "                <span class=\"region\">";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["event"] ?? null), "name", array()), "html", null, true);
            echo "</span>
            ";
        }
        // line 120
        echo "        </h1>
        <div id=\"updates\"></div>
        <section class=\"menu off\">
            <section class=\"batch\">
                <ul>
                    <li class=\"batch";
        // line 125
        if ((        $this->renderBlock("section", $context, $blocks) == "batch")) {
            echo " active";
        }
        echo "\"><a href=\"/batch\">";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["managed_batch"] ?? null), "name", array()), "html", null, true);
        echo "</a></li>
                </ul>
            </section>
            <nav>
                <ul>
                    <li ";
        // line 130
        if ((        $this->renderBlock("section", $context, $blocks) == "dashboard")) {
            echo "class=\"active\"";
        }
        echo "><a href=\"/\">Events</a></li>
                    ";
        // line 131
        if (($context["event"] ?? null)) {
            // line 132
            echo "                        <li class=\"event active\"><a href=\"/event/";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["event"] ?? null), "id", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["event"] ?? null), "name", array()), "html", null, true);
            echo "</a></li>
                    ";
        }
        // line 134
        echo "                </ul>
            </nav>
            <form method=\"get\" action=\"/search\" class=\"search ";
        // line 136
        if ((        $this->renderBlock("section", $context, $blocks) == "search")) {
            echo "active";
        }
        echo "\">
                <input type=\"text\" name=\"q\" value=\"";
        // line 137
        echo twig_escape_filter($this->env, ($context["search"] ?? null), "html", null, true);
        echo "\" placeholder=\"Find an Attendee\" />";
        // line 138
        echo "<a href=\"#\" class=\"new-registration-button\">+</a>
                <input type=\"submit\" value=\"Search\" />
            </form>
            <section class=\"other\">
                <ul>
                    <li><a href=\"https://organizer.training.srnd.org/\" target=\"_blank\">Organizer Guide</a></li>
                    <li ";
        // line 144
        if ((        $this->renderBlock("section", $context, $blocks) == "tools")) {
            echo "class=\"active\"";
        }
        echo "><a href=\"/tools\">Tools</a></li>
                    ";
        // line 145
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["managed_batch"] ?? null), "starts_at", array()), "addDays", array(0 =>  -30), "method"), "isPast", array()) || (        $this->renderBlock("section", $context, $blocks) == "dayof"))) {
            // line 146
            echo "                        <li ";
            if ((            $this->renderBlock("section", $context, $blocks) == "dayof")) {
                echo "class=\"active\"";
            }
            echo "><a href=\"/dayof\">Day-Of</a></li>
                    ";
        }
        // line 148
        echo "                    ";
        if ($this->getAttribute(($context["me"] ?? null), "is_admin", array())) {
            // line 149
            echo "                        <li ";
            if ((            $this->renderBlock("section", $context, $blocks) == "settings")) {
                echo "class=\"active\"";
            }
            echo "><a href=\"/settings\">Settings</a></li>
                    ";
        }
        // line 151
        echo "                    <li class=\"help\"><a href=\"/help\">Help</a></li>
                </ul>
            </section>
        </section>
    </header>

    <div class=\"wrap\">
        ";
        // line 158
        if (        $this->renderBlock("subnav", $context, $blocks)) {
            // line 159
            echo "            <section class=\"subnav off\">
                ";
            // line 160
            $this->displayBlock("subnav", $context, $blocks);
            echo "
            </section>
        ";
        }
        // line 163
        echo "        <section class=\"content ";
        if (        $this->renderBlock("subnav", $context, $blocks)) {
            echo "with-subnav";
        }
        echo "\">
            ";
        // line 164
        $this->displayBlock("content", $context, $blocks);
        echo "
        </section>
    </div>

    <footer>
        Copyright &copy; 2014-";
        // line 169
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "Y"), "html", null, true);
        echo " StudentRND. <a href=\"https://raygun.com/products/crash-reporting/\">Error and crash reporting software</a> provided by <a href=\"https://raygun.com/\">Raygun</a>.
    </footer>

    ";
        // line 172
        $this->loadTemplate("widgets/new.twig", "template.twig", 172)->display($context);
        // line 173
        echo "
    <script type=\"text/javascript\" src=\"/assets/js/sweet-alert.min.js\"></script>
    <script type=\"text/javascript\" src=\"/assets/js/app.js\"></script>
    ";
        // line 176
        $this->displayBlock("scripts", $context, $blocks);
        echo "
    ";
        // line 177
        if ((($context["status_message"] ?? null) || ($context["error"] ?? null))) {
            // line 178
            echo "        <script type=\"text/javascript\">
            \$(document).ready(function(){
                setTimeout(function(){
                    \$('body > .info').animate({'height': 'toggle', 'opacity': 'toggle'});
                }, 5000);
            });
        </script>
    ";
        }
        // line 186
        echo "    <script type=\"text/javascript\">
      rg4js('apiKey', '";
        // line 187
        echo twig_escape_filter($this->env, ($context["raygun_api_key"] ?? null), "html", null, true);
        echo "');
      rg4js('enablePulse', true);
      rg4js('enableCrashReporting', true);

      ";
        // line 191
        if (($context["me"] ?? null)) {
            // line 192
            echo "        rg4js('setUser', {
          identifier: ";
            // line 193
            echo twig_jsonencode_filter($this->getAttribute(($context["me"] ?? null), "username", array()));
            echo ",
          isAnonymous: false,
          email: ";
            // line 195
            echo twig_jsonencode_filter($this->getAttribute(($context["me"] ?? null), "email", array()));
            echo ",
          firstName: ";
            // line 196
            echo twig_jsonencode_filter($this->getAttribute(($context["me"] ?? null), "first_name", array()));
            echo ",
          fullName: ";
            // line 197
            echo twig_jsonencode_filter($this->getAttribute(($context["me"] ?? null), "name", array()));
            echo "
        });
      ";
        }
        // line 200
        echo "    </script>
    <script>
        var HW_config = {
            selector: \"#updates\",
            account:  \"xaBzA7\"
        }
    </script>
    <script async src=\"//cdn.headwayapp.co/widget.js\"></script>
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "template.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  356 => 200,  350 => 197,  346 => 196,  342 => 195,  337 => 193,  334 => 192,  332 => 191,  325 => 187,  322 => 186,  312 => 178,  310 => 177,  306 => 176,  301 => 173,  299 => 172,  293 => 169,  285 => 164,  278 => 163,  272 => 160,  269 => 159,  267 => 158,  258 => 151,  250 => 149,  247 => 148,  239 => 146,  237 => 145,  231 => 144,  223 => 138,  220 => 137,  214 => 136,  210 => 134,  202 => 132,  200 => 131,  194 => 130,  182 => 125,  175 => 120,  169 => 118,  167 => 117,  159 => 114,  151 => 108,  147 => 106,  145 => 105,  140 => 104,  138 => 103,  133 => 102,  131 => 101,  127 => 100,  33 => 8,  27 => 6,  25 => 5,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "template.twig", "");
    }
}

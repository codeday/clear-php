<?php

/* template.twig */
class __TwigTemplate_46eeea63c928344f1dc6efd4d5bc03b5ea619c7a9fd6f42e907286d995e5d741 extends TwigBridge\Twig\Template
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
        if ($this->renderBlock("title", $context, $blocks)) {
            // line 6
            echo "            ";
            $this->displayBlock("title", $context, $blocks);
            echo " //
        ";
        }
        // line 8
        echo "        Clear // CodeDay
    </title>
    <link rel=\"stylesheet\" href=\"/assets/css/style.css\" />
    <link rel=\"stylesheet\" href=\"/assets/css/sweet-alert.css\"/>
    <link id=\"favicon\" rel=\"icon\" type=\"image/png\" href=\"/assets/img/favicon.png\" />
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
</head>
<body class=\"";
        // line 88
        $this->displayBlock("section", $context, $blocks);
        echo "\">
    ";
        // line 89
        if ((isset($context["status_message"]) ? $context["status_message"] : null)) {
            // line 90
            echo "        <div class=\"info status\">";
            echo twig_escape_filter($this->env, (isset($context["status_message"]) ? $context["status_message"] : null), "html", null, true);
            echo "</div>
    ";
        } elseif (        // line 91
(isset($context["error"]) ? $context["error"] : null)) {
            // line 92
            echo "        <div class=\"info error\">";
            echo twig_escape_filter($this->env, (isset($context["error"]) ? $context["error"] : null), "html", null, true);
            echo "</div>
    ";
        } elseif (        // line 93
(isset($context["old_batch"]) ? $context["old_batch"] : null)) {
            // line 94
            echo "        <div class=\"info old-batch\">Warning: you are editing an old batch. <a href=\"/change-batch\">Switch?</a></div>
    ";
        }
        // line 96
        echo "    <header>
        <h1>
            <span class=\"event\">CodeDay</span>
            <span class=\"tool\">Close</span>
        </h1>
        <nav>
            <ul>
                <li ";
        // line 103
        if (($this->renderBlock("section", $context, $blocks) == "dashboard")) {
            echo "class=\"active\"";
        }
        echo "><a href=\"/\">Dashboard</a></li>
                <li ";
        // line 104
        if (($this->renderBlock("section", $context, $blocks) == "tools")) {
            echo "class=\"active\"";
        }
        echo "><a href=\"/tools\">Tools</a></li>
                ";
        // line 105
        if ($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "is_admin", array())) {
            // line 106
            echo "                    <li ";
            if (($this->renderBlock("section", $context, $blocks) == "settings")) {
                echo "class=\"active\"";
            }
            echo "><a href=\"/settings\">Settings</a></li>
                    <li ";
            // line 107
            if (($this->renderBlock("section", $context, $blocks) == "debug")) {
                echo "class=\"active\"";
            }
            echo "><a href=\"/debug\">Debug</a></li>
                ";
        }
        // line 109
        echo "                ";
        if ((isset($context["event"]) ? $context["event"] : null)) {
            // line 110
            echo "                    <li class=\"active\"><a href=\"/event/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "name", array()), "html", null, true);
            echo "</a></li>
                ";
        }
        // line 112
        echo "            </ul>
        </nav>
        <section class=\"user\">
            <ul>
                <li class=\"username\">";
        // line 116
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["me"]) ? $context["me"] : null), "username", array()), "html", null, true);
        echo "</li>
                <li class=\"batch\"><a href=\"/change-batch\">";
        // line 117
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "name", array()), "html", null, true);
        echo "</a></li>
                <li class=\"logout\"><a href=\"/logout\">Logout</a></li>
            </ul>
        </section>
    </header>

    <div class=\"wrap\">
        ";
        // line 124
        if ($this->renderBlock("subnav", $context, $blocks)) {
            // line 125
            echo "            <section class=\"subnav\">
                ";
            // line 126
            $this->displayBlock("subnav", $context, $blocks);
            echo "
            </section>
        ";
        }
        // line 129
        echo "        <section class=\"content ";
        if ($this->renderBlock("subnav", $context, $blocks)) {
            echo "with-subnav";
        }
        echo "\">
            ";
        // line 130
        $this->displayBlock("content", $context, $blocks);
        echo "
        </section>
    </div>

    <footer>
        Copyright &copy; ";
        // line 135
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "Y"), "html", null, true);
        echo " StudentRND.
        <a href=\"#\" class=\"toggle-sounds\">Disable sounds</a>
        <a href=\"#\" id=\"tour-start\">Start tour</a>
    </footer>
    <script type=\"text/javascript\" src=\"/assets/js/sweet-alert.min.js\"></script>
    <script type=\"text/javascript\" src=\"/assets/js/app.js\"></script>
    ";
        // line 141
        $this->displayBlock("scripts", $context, $blocks);
        echo "
    ";
        // line 142
        if (((isset($context["status_message"]) ? $context["status_message"] : null) || (isset($context["error"]) ? $context["error"] : null))) {
            // line 143
            echo "        <script type=\"text/javascript\">
            \$(document).ready(function(){
                setTimeout(function(){
                    \$('body > .info').animate({'height': 'toggle', 'opacity': 'toggle'});
                }, 5000);
            });
        </script>
    ";
        }
        // line 151
        echo "
    <link rel=\"stylesheet\" href=\"/assets/css/shepherd-theme-arrows.css\" />
    <script src=\"/assets/js/shepherd.min.js\"></script>
    ";
        // line 154
        if ($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "wasFirstLogin", array())) {
            // line 155
            echo "        <script type=\"text/javascript\">
            localStorage['current-tour-page'] = 'dashboard';
        </script>
    ";
        }
        // line 159
        echo "    <script src=\"/assets/js/tour.js\"></script>
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
        return array (  271 => 159,  265 => 155,  263 => 154,  258 => 151,  248 => 143,  246 => 142,  242 => 141,  233 => 135,  225 => 130,  218 => 129,  212 => 126,  209 => 125,  207 => 124,  197 => 117,  193 => 116,  187 => 112,  179 => 110,  176 => 109,  169 => 107,  162 => 106,  160 => 105,  154 => 104,  148 => 103,  139 => 96,  135 => 94,  133 => 93,  128 => 92,  126 => 91,  121 => 90,  119 => 89,  115 => 88,  33 => 8,  27 => 6,  25 => 5,  19 => 1,);
    }
}

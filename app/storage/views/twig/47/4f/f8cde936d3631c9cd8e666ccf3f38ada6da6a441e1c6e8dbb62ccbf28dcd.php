<?php

/* event/subnav.twig */
class __TwigTemplate_474ff8cde936d3631c9cd8e666ccf3f38ada6da6a441e1c6e8dbb62ccbf28dcd extends TwigBridge\Twig\Template
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
        echo "<h2>";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "name", array()), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "batch", array()), "name", array()), "html", null, true);
        echo "</h2>

<section class=\"general\">
    <ul>
        <li><a href=\"/event/";
        // line 5
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "\">Overview</a></li>
        <li><a href=\"/event/";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/registrations\">Registrations</a></li>
        <li><a href=\"/event/";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/subscriptions\">Subscriptions</a></li>
        <li><a href=\"/event/";
        // line 8
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/emails\">Send Email</a></li>
    </ul>
</section>

<section class=\"website\">
    <h3>Website</h3>
    <ul>
        <li><a href=\"/event/";
        // line 15
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/promotions\">Promotion Codes</a></li>
        <li><a href=\"/event/";
        // line 16
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/venue\">Venue Info</a></li>
        <li><a href=\"/event/";
        // line 17
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/sponsors\">Sponsors</a></li>
        <li><a href=\"/event/";
        // line 18
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/activities\">Schedule Activities</a></li>
    </ul>
</section>

<section class=\"settings\">
    <h3>Settings</h3>
    <ul>
        <li><a href=\"/event/";
        // line 25
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
        echo "/subusers\">Subusers</a></li>
        ";
        // line 26
        if ($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "shipment_tracking", array())) {
            // line 27
            echo "            <li><a href=\"http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=";
            echo twig_escape_filter($this->env, twig_urlencode_filter($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "shipment_tracking", array())), "html", null, true);
            echo "&loc=en_us\" target=\"_blank\">Shipping</a></li>
        ";
        } else {
            // line 29
            echo "            <li><a href=\"/event/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
            echo "/shipping\">Shipping</a></li>
        ";
        }
        // line 31
        echo "        ";
        if ( !$this->getAttribute($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "batch", array()), "preevent_email_sent_at", array())) {
            // line 32
            echo "            <li><a href=\"/event/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
            echo "/preevent\">Pre-Event Email</a></li>
        ";
        }
        // line 34
        echo "        ";
        if ($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "is_admin", array())) {
            // line 35
            echo "            <li><a href=\"/event/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
            echo "/supplies\">Supplies</a></li>
            <li><a href=\"/event/";
            // line 36
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["event"]) ? $context["event"] : null), "id", array()), "html", null, true);
            echo "/special\">Special</a></li>
        ";
        }
        // line 38
        echo "    </ul>
</section>
";
    }

    public function getTemplateName()
    {
        return "event/subnav.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  113 => 38,  108 => 36,  103 => 35,  100 => 34,  94 => 32,  91 => 31,  85 => 29,  79 => 27,  77 => 26,  73 => 25,  63 => 18,  59 => 17,  55 => 16,  51 => 15,  41 => 8,  37 => 7,  33 => 6,  29 => 5,  19 => 1,);
    }
}

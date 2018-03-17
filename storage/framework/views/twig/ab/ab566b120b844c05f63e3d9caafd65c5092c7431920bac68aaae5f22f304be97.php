<?php

/* tools/subnav.twig */
class __TwigTemplate_d286758235f467ecff10082c6fdfd5b2f49dc81982a57da936f80cf33eb75831 extends TwigBridge\Twig\Template
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
        echo "<section class=\"batch-tools\">
    <ul>
        <li><a href=\"/tools/tidbits\">Tidbits</a></li>
        <li><a href=\"/tools/banlist\">Banlist</a></li>
        ";
        // line 5
        if ($this->getAttribute(($context["me"] ?? null), "is_admin", array())) {
            // line 6
            echo "            <li><a href=\"/tools/giftcards\">Giftcards</a></li>
            <li><a href=\"/tools/query\">Query</a></li>
            <li><a href=\"/tools/preview\">Preview</a></li>
        ";
        }
        // line 10
        echo "    </ul>
</section>

<section class=\"api\">
    <h3>API</h3>
    <ul>
        <li><a href=\"/tools/applications\">Applications</a></li>
        <li><a href=\"/docs/model/Batch\">Docs</a></li>
    </ul>
</section>
";
    }

    public function getTemplateName()
    {
        return "tools/subnav.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  33 => 10,  27 => 6,  25 => 5,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "tools/subnav.twig", "");
    }
}

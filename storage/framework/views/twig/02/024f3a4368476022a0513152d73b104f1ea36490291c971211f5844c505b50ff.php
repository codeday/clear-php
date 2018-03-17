<?php

/* /app/resources/views/tools/tidbits/index.twig */
class __TwigTemplate_3ba87d609de7fc08537f02e8f8b764f46dd2cd3e3c671833909fa5b3abc1f3a6 extends TwigBridge\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("template.twig", "/app/resources/views/tools/tidbits/index.twig", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'section' => array($this, 'block_section'),
            'subnav' => array($this, 'block_subnav'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "template.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 5
        $context["regions"] = $this->loadTemplate("widgets/regions.twig", "/app/resources/views/tools/tidbits/index.twig", 5);
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_title($context, array $blocks = array())
    {
        echo "Tidbits";
    }

    // line 3
    public function block_section($context, array $blocks = array())
    {
        echo "tools";
    }

    // line 4
    public function block_subnav($context, array $blocks = array())
    {
        $this->loadTemplate("tools/subnav.twig", "/app/resources/views/tools/tidbits/index.twig", 4)->display($context);
    }

    // line 6
    public function block_content($context, array $blocks = array())
    {
        // line 7
        echo "    <header>
        <h2>Tidbits</h2>
        <p>See tidbits of information for past events.</p>
    </header>

    ";
        // line 12
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["all_regions"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["region"]) {
            // line 13
            echo "        ";
            if ((twig_length_filter($this->env, $this->getAttribute($context["region"], "events", array())) > 0)) {
                // line 14
                echo "            <a href=\"/tools/tidbits/";
                echo twig_escape_filter($this->env, $this->getAttribute($context["region"], "id", array()), "html", null, true);
                echo "\" class=\"region-link\">";
                echo $context["regions"]->getregion($context["region"]);
                echo "</a>
        ";
            }
            // line 16
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['region'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "/app/resources/views/tools/tidbits/index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  77 => 16,  69 => 14,  66 => 13,  62 => 12,  55 => 7,  52 => 6,  46 => 4,  40 => 3,  34 => 2,  30 => 1,  28 => 5,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "/app/resources/views/tools/tidbits/index.twig", "");
    }
}

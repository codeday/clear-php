<?php

/* /home/tj/Clear/app/config/../views/event/subscriptions.twig */
class __TwigTemplate_f132ba02f61c51b0a338680aad84eed7b8dbb03a426544785a0f3db338ef6d6c extends TwigBridge\Twig\Template
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
        echo "<header>
  <h2>Subscriptions</h2>
  <p>See who's subscribed to the email list.</p>
</header>

<table class=\"directory\">
  <thead>
    <tr>
      <th>ID</th>
      <th>Email</th>
    </tr>
  </thead>
  <tbody>
    ";
        // line 19
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["event"]) ? $context["event"] : null), "notify", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["sub"]) {
            // line 20
            echo "    <tr>
      <td>";
            // line 21
            echo twig_escape_filter($this->env, $this->getAttribute($context["sub"], "id", array()), "html", null, true);
            echo "</td>
      <td><a href=\"mailto:";
            // line 22
            echo twig_escape_filter($this->env, $this->getAttribute($context["sub"], "email", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["sub"], "email", array()), "html", null, true);
            echo "
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sub'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 24
        echo "  </tbody>
</table>
";
    }

    public function getTemplateName()
    {
        return "/home/tj/Clear/app/config/../views/event/subscriptions.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  96 => 24,  86 => 22,  82 => 21,  79 => 20,  75 => 19,  60 => 6,  57 => 5,  51 => 4,  45 => 3,  39 => 2,  11 => 1,);
    }
}

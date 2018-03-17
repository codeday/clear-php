<?php

/* /app/resources/views/errors/404.twig */
class __TwigTemplate_c0e59c2de7950c396588a66d41bca2ffdcfc3811ad5df99d2d09ec937fd1057c extends TwigBridge\Twig\Template
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
        echo "<!-- This file should not extend any templates or reference any external scripts/styles! -->
<html>
  <head>
    <title>404 Not Found</title>
    <style>
      body{
        animation: colorpulse 10s infinite;
        -webkit-animation: colorpulse 10s infinite;
        -moz-animation: colorpulse 10s infinite;
        height: 100%;
        overflow: hidden;
        margin: 0;
      }

      @-webkit-keyframes colorpulse{
        0%{
          background: rgb(11,192,194);
        }

        25%{
          background: rgb(248,99,243);
        }

        50%{
          background: rgb(252,88,67);
        }

        100%{
          background: rgb(11,192,194);
        }
      }

      @keyframes colorpulse{
        0%{
          background: rgb(11,192,194);
        }

        25%{
          background: rgb(248,99,243);
        }

        50%{
          background: rgb(252,88,67);
        }

        100%{
          background: rgb(11,192,194);
        }
      }

      @moz-keyframes colorpulse{
        0%{
          background: rgb(11,192,194);
        }

        25%{
          background: rgb(248,99,243);
        }

        50%{
          background: rgb(252,88,67);
        }

        100%{
          background: rgb(11,192,194);
        }
      }

      .message {
        font-family: \"Arial\";
        height: 100%;
        width: 100%;
        top: 45%;
        position: fixed;
        font-size: 2.5em;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class=\"message\">
      Looks like this page is gone. <a href=\"/\">Go back?</a>
    </div>
  </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "/app/resources/views/errors/404.twig";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "/app/resources/views/errors/404.twig", "");
    }
}

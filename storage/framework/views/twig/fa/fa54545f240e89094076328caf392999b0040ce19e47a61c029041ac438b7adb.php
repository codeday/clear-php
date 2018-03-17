<?php

/* widgets/countdown.twig */
class __TwigTemplate_d6d81e645a58773733cc5b41d8059fdf9c418383493401d6d291dcfd72a7dc6e extends TwigBridge\Twig\Template
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
    }

    // line 1
    public function getfancy($__to__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "to" => $__to__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2
            echo "    ";
            $context["s"] = $this;
            // line 3
            echo "    <section class=\"countdown\">
        <section class=\"active\">
            ";
            // line 5
            echo $context["s"]->getplain(($context["to"] ?? null));
            echo "
        </section>
        <section class=\"details\">
            <span class=\"name\">CodeDay ";
            // line 8
            echo twig_escape_filter($this->env, $this->getAttribute(($context["to"] ?? null), "name", array()), "html", null, true);
            echo "</span>
            <span class=\"date\">";
            // line 9
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute(($context["to"] ?? null), "starts_at", array()), "F j, Y"), "html", null, true);
            echo "</span>
        </section>
    </section>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 13
    public function getplain($__to__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "to" => $__to__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 14
            echo "    ";
            if ($this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "isFuture", array())) {
                // line 15
                echo "        <span class=\"weeks\" id=\"time-remaining-weeks\">";
                echo twig_escape_filter($this->env, twig_round(($this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "diffInDays", array()) / 7), 0, "floor"), "html", null, true);
                echo " weeks</span>
        <span class=\"days\" id=\"time-remaining-days\">";
                // line 16
                echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "diffInDays", array()) % 7), "html", null, true);
                echo " days</span>
        <span class=\"hours\" id=\"time-remaining-hours\">";
                // line 17
                echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "diffInHours", array()) % 24), "html", null, true);
                echo " hours</span>
        <span class=\"minutes\" id=\"time-remaining-minutes\">";
                // line 18
                echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "diffInMinutes", array()) % 60), "html", null, true);
                echo " minutes</span>
        <span class=\"seconds\" id=\"time-remaining-seconds\">";
                // line 19
                echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "diffInSeconds", array()) % 60), "html", null, true);
                echo " seconds</span>
        left.
    ";
            } elseif ($this->getAttribute($this->getAttribute(            // line 21
($context["to"] ?? null), "ends_at", array()), "isFuture", array())) {
                // line 22
                echo "        It's CodeDay!
    ";
            } else {
                // line 24
                echo "        CodeDay is over.
    ";
            }
            // line 26
            echo "    <script type=\"text/javascript\">
        (function(){
            var countdown_clock = {
                die_at: (";
            // line 29
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["to"] ?? null), "starts_at", array()), "timestamp", array()), "html", null, true);
            echo " + (60 * 60 * 12)) * 1000,
                has_started: false,
                get_remaining_seconds: function () {
                return Math.floor((this.die_at - (+new Date())) / 1000);
            },
            get_countdown_component_simple: function (div, mod) {
                return Math.floor((this.get_remaining_seconds() / div) % mod);
            },
            get_countdown: function () {
                return {
                    weeks: Math.floor((this.get_remaining_seconds() / (60 * 60 * 24 * 7))),
                    days: this.get_countdown_component_simple(60 * 60 * 24, 7),
                    hours: this.get_countdown_component_simple(60 * 60, 24),
                    minutes: this.get_countdown_component_simple(60, 60),
                    seconds: this.get_countdown_component_simple(1, 60)
                };
            },
            is_leap_year: function (year) {
                return !((year % 4) || (!(year % 100) && (year % 400)));
            },
            days_in_year: function (year) {
                return this.is_leap_year(year) ? 366 : 365;
            },
            tick: function () {
                var countdown = countdown_clock.get_countdown();

                var weeks = countdown.weeks > 0 ? countdown.weeks + ' week' + (countdown.weeks != 1 ? 's' : '') : '';
                var days = countdown.days > 0 ? countdown.days + ' day' + (countdown.days != 1 ? 's' : '') : '';
                var hours = countdown.hours > 0 ? countdown.hours + ' hour' + (countdown.hours != 1 ? 's' : '') : '';
                var minutes = countdown.minutes > 0 ? countdown.minutes + ' minute' + (countdown.minutes != 1 ? 's' : '') : '';
                var seconds = countdown.seconds ? countdown.seconds + ' second' + (countdown.seconds != 1 ? 's' : '') : '';

                document.getElementById('time-remaining-weeks').textContent = weeks + (weeks && (days || hours || minutes || seconds) ? ',' : '');
                document.getElementById('time-remaining-days').textContent = days + (days && (hours || minutes || seconds) ? ',' : '');
                document.getElementById('time-remaining-hours').textContent = hours + (hours && (minutes || seconds) ? ',' : '');
                document.getElementById('time-remaining-minutes').textContent = minutes + (minutes && (seconds) ? ',' : '');
                document.getElementById('time-remaining-seconds').textContent = seconds;
            },
            start: function () {
                if (this.has_started) return;
                this.has_started = true;

                var animator = null;
                if (typeof(window['requestAnimationFrame']) !== 'undefined') {
                    animator = window['requestAnimationFrame'];
                } else if (typeof(window['mozRequestAnimationFrame']) !== 'undefined') {
                    animator = window['mozRequestAnimationFrame'];
                } else if (typeof(window['webkitRequestAnimationFrame']) !== 'undefined') {
                    animator = window['webkitRequestAnimationFrame'];
                } else {
                    animator = function (lambda) {
                        lambda();
                    }
                }

                setInterval(function () {
                    animator(countdown_clock.tick);
                }, 1000);

                animator(function () {
                    countdown_clock.tick();
                });
            }
        };
        if (document.getElementById('time-remaining-weeks')) {
            countdown_clock.start();
        }
        })();
    </script>

";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "widgets/countdown.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  121 => 29,  116 => 26,  112 => 24,  108 => 22,  106 => 21,  101 => 19,  97 => 18,  93 => 17,  89 => 16,  84 => 15,  81 => 14,  69 => 13,  50 => 9,  46 => 8,  40 => 5,  36 => 3,  33 => 2,  21 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "widgets/countdown.twig", "");
    }
}

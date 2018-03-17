<?php

/* widgets/new.twig */
class __TwigTemplate_526bee98706c0d9bd5583b24b22d6e209b9ee36f8f07bcf49997c3ac078e6948 extends TwigBridge\Twig\Template
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
        echo "<section class=\"new-registration\">
    <form method=\"post\" action=\"/new\" class=\"info\" id=\"register-form\">
        <h2>Create a New Registration</h2>
        ";
        // line 4
        if ($this->getAttribute(($context["me"] ?? null), "is_admin", array())) {
            // line 5
            echo "            ";
            if ($this->getAttribute(($context["managed_batch"] ?? null), "is_loaded", array())) {
                // line 6
                echo "                ";
                $context["events"] = array();
                // line 7
                echo "                ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["loaded_batches"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["batch"]) {
                    // line 8
                    echo "                    ";
                    $context["events"] = twig_array_merge(($context["events"] ?? null), $this->getAttribute($context["batch"], "events", array()));
                    // line 9
                    echo "                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['batch'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 10
                echo "            ";
            } else {
                // line 11
                echo "                ";
                $context["events"] = $this->getAttribute(($context["managed_batch"] ?? null), "events", array());
                // line 12
                echo "            ";
            }
            // line 13
            echo "        ";
        } else {
            // line 14
            echo "            ";
            $context["events"] = $this->getAttribute(($context["me"] ?? null), "current_managed_events", array());
            // line 15
            echo "        ";
        }
        // line 16
        echo "        <div class=\"field\">
            <label for=\"batches_event_id\">Event</label>
            <select name=\"batches_event_id\" id=\"batches_event_id\">
                ";
        // line 19
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["events"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["_event"]) {
            // line 20
            echo "                    <option value=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["_event"], "id", array()), "html", null, true);
            echo "\" ";
            if (($this->getAttribute($context["_event"], "id", array()) == $this->getAttribute(($context["event"] ?? null), "id", array()))) {
                echo "selected";
            }
            echo ">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["_event"], "name", array()), "html", null, true);
            echo " (";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["_event"], "batch", array()), "name", array()), "html", null, true);
            echo ")</option>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['_event'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 22
        echo "            </select>
        </div>

        <div class=\"attendee\">
            <h3>Attendee Info</h3>
            <div class=\"field\">
                <label for=\"first_name\">First Name</label>
                <input type=\"text\" name=\"first_name\" id=\"first_name\" required />
            </div>

            <div class=\"field\">
                <label for=\"last_name\">Last Name</label>
                <input type=\"text\" name=\"last_name\" id=\"last_name\" required />
            </div>

            <div class=\"field\">
                <label for=\"email\">Email</label>
                <input type=\"text\" name=\"email\" id=\"email\" required />
            </div>

            <div class=\"field\">
                <label for=\"type\">Ticket Type</label>
                <select name=\"type\" id=\"type\">
                    ";
        // line 45
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(array(0 => "student", 1 => "volunteer", 2 => "mentor", 3 => "judge", 4 => "teacher", 5 => "sponsor", 6 => "press", 7 => "vip"));
        foreach ($context['_seq'] as $context["_key"] => $context["type"]) {
            // line 46
            echo "                        <option value=\"";
            echo twig_escape_filter($this->env, $context["type"], "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $context["type"], "html", null, true);
            echo "</option>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['type'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 48
        echo "                </select>
            </div>
        </div>

        <div class=\"parent\">
            <h3>Parent Info</h3>
                <div class=\"field\">
                    <label for=\"parent_name\">Name</label>
                    <input type=\"text\" name=\"parent_name\" id=\"parent_name\" />
                </div>

                <div class=\"field\">
                    <label for=\"parent_email\">Email</label>
                    <input type=\"email\" name=\"parent_email\" id=\"parent_email\" />
                </div>

                <div class=\"field\">
                    <label for=\"parent_phone\">Phone 1</label>
                    <input type=\"phone\" name=\"parent_phone\" id=\"parent_phone\" />
                </div>

                <div class=\"field\">
                    <label for=\"parent_secondary_phone\">Phone 2</label>
                    <input type=\"phone\" name=\"parent_secondary_phone\" id=\"parent_secondary_phone\" />
                </div>
        </div>
        <section class=\"payment\">
            <h3>Billing Info</h3>
            <div class=\"field\">
                <label for=\"amount\">Amount to Bill</label>
                <input type=\"number\" name=\"amount\" id=\"amount\" value=\"0\" min=\"0\" required />
            </div>
            <div class=\"field\">
                <label for=\"card\">Card Info</label>
                <div class=\"group combined\">
                    <div id=\"card-details\">
                        <input type=\"text\" id=\"card\" style=\"width:100%\" placeholder=\"4242 4242 4242 4242\" /><br />
                        <input type=\"text\" id=\"mm\" name=\"mm\" pattern=\"\\d\\d?\" placeholder=\"MM\" />
                        <input type=\"text\" id=\"yy\" name=\"yy\" pattern=\"\\d\\d(\\d\\d)?\" placeholder=\"YYYY\" /><br />
                        <a href=\"#\" id=\"card-swipe-handle\">Swipe Card</a>
                    </div>
                    <div id=\"swipe-details\" style=\"display:none\">
                        <input type=\"text\" id=\"card-swipe-area\" placeholder=\"Swipe card now...\" />
                        <a href=\"#\" id=\"card-swipe-cancel-handle\">Cancel</a>
                    </div>
                </div>
            </div>
        </section>
        <input type=\"hidden\" name=\"token\" id=\"token\" value=\"\" />
        <input type=\"submit\" value=\"Register\" />
        <a href=\"#\" class=\"cancel\">Cancel</a>
    ";
        // line 99
        echo ($context["csrf"] ?? null);
        echo "</form>
</section>

<script type=\"text/javascript\" src=\"https://js.stripe.com/v2/\"></script>
<script type=\"text/javascript\">
    // # Show/hide
    \$('.new-registration-button').on('click', function(e){
        e.preventDefault();
        \$('.new-registration').css('display', 'block');
    });
    \$('.new-registration form').on('click', function(e){
        e.stopPropagation();
    });
    \$('.new-registration, .new-registration .cancel').on('click', function(e){
        e.preventDefault();
        \$('.new-registration').css('display', 'none');
        \$('.new-registration input[type=\"text\"]').val('');
    });

    // # Parent Info Display
    \$('.new-registration form #type').on('change', function(){
        if (\$('#type').val() == 'student' || \$('#type').val() == 'volunteer') {
            \$('.parent').css('visibility', 'visible');
        } else {
            \$('.parent').css('visibility', 'hidden');
            \$('.parent input').val('');
        }
    });

    // # Payment Display
    \$(\".new-registration form #amount\").on('input', function(){
        var paymentElems = \$(\".new-registration form #card, .new-registration form #mm, .new-registration form #yy\");
        var paymentParents = paymentElems.parents('div.field');
        if (\$(this).val() > 0) {
            paymentElems.attr('required', true);
            paymentParents.css('display', 'block');
        } else {
            paymentElems.attr('required', false).val('');
            paymentParents.css('display', 'none');
        }
    });

    // # Card Swiping
    \$('.new-registration form #card-swipe-handle').on('click', function(){
        \$('.new-registration form #swipe-details').css('display','block');
        \$('.new-registration form #card-details').css('display', 'none');
        \$('.new-registration form #card-swipe-area').val('').focus();
    });

    \$('.new-registration form #card-swipe-cancel-handle').on('click', function(){
        \$('.new-registration form #swipe-details').css('display','none');
        \$('.new-registration form #card-details').css('display', 'block');
        \$('.new-registration form #card-swipe-area').val('');
    });

    var parseSwipeData = function(swipe)
    {
        var trackOneStartsAt = swipe.indexOf('%');
        if (trackOneStartsAt < 0) throw \"Unsupported reader.\";
        var trackOneEndsAt = swipe.indexOf('?');
        var trackOneContent = swipe.substr(trackOneStartsAt + 1, trackOneEndsAt - trackOneStartsAt - 1);
        var components = trackOneContent.split('^');

        var cardType = components[0].substr(0,1);
        var cardNumber = components[0].substr(1);

        var nameComponents = components[1].split('/');
        var name = nameComponents.length != 2 ? components[1] :
        {first: nameComponents[1], last: nameComponents[0]};

        var expMonth = components[2].substr(0,2);
        var expYear = components[2].substr(2,2);

        return {
            type: cardType,
            number: cardNumber,
            name: name,
            exp: {
                month: expMonth,
                year: expYear
            }
        };
    }

    \$('.new-registration form #card-swipe-area').on('keydown', function(event){
        event.stopPropagation();
        if (event.keyCode == 13) {
            event.preventDefault();
            var swiped = \$(this).val();
            var cardInfo = parseSwipeData(swiped);

            \$('.new-registration form #card').val(cardInfo.number);
            \$('.new-registration form #mm').val(cardInfo.exp.month);
            \$('.new-registration form #yy').val(cardInfo.exp.year);

            if (typeof(cardInfo.name) === 'object'
                    && \$('#first_name').val().length === 0 && \$('#last_name').val().length === 0) {
                var prettyName = function(name) {
                    return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
                };

                \$('#first_name').val(prettyName(cardInfo.name.first));
                \$('#last_name').val(prettyName(cardInfo.name.last));
            }

            \$('.new-registration form #card-swipe-cancel-handle').click();
            return false;
        }
    });

    // # Process Payments
    Stripe.setPublishableKey('";
        // line 210
        echo twig_escape_filter($this->env, ($context["stripe_pk"] ?? null), "html", null, true);
        echo "');
    \$('.new-registration #register-form').submit(function(e){
        if (\$(\".new-registration form #amount\").val() == 0) {
            return;
        }
        e.preventDefault();

        if (\$('.new-registration form #pay-button').text() == '...') {
            return;
        }

        \$('.new-registration form #pay-button').text('...');

        Stripe.card.createToken({
            number: \$('.new-registration form #card').val(),
            exp_month: \$('.new-registration form #mm').val(),
            exp_year: \$('.new-registration form #yy').val()
        },
        function(status, response) {
            if (response.error) {
                swal({
                    title: 'Error',
                    text: response.error.message,
                    type: 'error'
                });
                \$('.new-registration form #pay-button').text('Register');
            } else {
                var token = response.id;
                \$('.new-registration form #token').val(token);
                \$('.new-registration #register-form').off().submit();
            }
        });
    });
</script>

";
    }

    public function getTemplateName()
    {
        return "widgets/new.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  297 => 210,  183 => 99,  130 => 48,  119 => 46,  115 => 45,  90 => 22,  73 => 20,  69 => 19,  64 => 16,  61 => 15,  58 => 14,  55 => 13,  52 => 12,  49 => 11,  46 => 10,  40 => 9,  37 => 8,  32 => 7,  29 => 6,  26 => 5,  24 => 4,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "widgets/new.twig", "");
    }
}

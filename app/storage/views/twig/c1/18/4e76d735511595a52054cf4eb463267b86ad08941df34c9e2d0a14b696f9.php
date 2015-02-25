<?php

/* /home/tj/Clear/app/config/../views/dashboard.twig */
class __TwigTemplate_c1184e76d735511595a52054cf4eb463267b86ad08941df34c9e2d0a14b696f9 extends TwigBridge\Twig\Template
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
            'section' => array($this, 'block_section'),
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
    public function block_section($context, array $blocks = array())
    {
        echo "dashboard";
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "    <section class=\"countdown\">
        <section class=\"active\">
            ";
        // line 6
        if ($this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "isFuture", array())) {
            // line 7
            echo "                <span class=\"weeks\" id=\"time-remaining-weeks\">";
            echo twig_escape_filter($this->env, twig_round(($this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "diffInDays", array()) / 7), 0, "floor"), "html", null, true);
            echo " weeks</span>
                <span class=\"days\" id=\"time-remaining-days\">";
            // line 8
            echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "diffInDays", array()) % 7), "html", null, true);
            echo " days</span>
                <span class=\"hours\" id=\"time-remaining-hours\">";
            // line 9
            echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "diffInHours", array()) % 24), "html", null, true);
            echo " hours</span>
                <span class=\"minutes\" id=\"time-remaining-minutes\">";
            // line 10
            echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "diffInMinutes", array()) % 60), "html", null, true);
            echo " minutes</span>
                <span class=\"seconds\" id=\"time-remaining-seconds\">";
            // line 11
            echo twig_escape_filter($this->env, ($this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "diffInSeconds", array()) % 60), "html", null, true);
            echo " seconds</span>
                left.
            ";
        } elseif ($this->getAttribute($this->getAttribute(        // line 13
(isset($context["managed_batch"]) ? $context["managed_batch"] : null), "ends_at", array()), "isFuture", array())) {
            // line 14
            echo "                It's CodeDay!
            ";
        } else {
            // line 16
            echo "                CodeDay is over.
            ";
        }
        // line 18
        echo "        </section>
        <section class=\"details\">
            <span class=\"name\">CodeDay ";
        // line 20
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "name", array()), "html", null, true);
        echo "</span>
            <span class=\"date\">";
        // line 21
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "F j, Y"), "html", null, true);
        echo "</span>
        </section>
    </section>
    ";
        // line 24
        if ((twig_length_filter($this->env, $this->getAttribute((isset($context["me"]) ? $context["me"] : null), "current_managed_events", array())) > 0)) {
            // line 25
            echo "        <section class=\"my-events\">
            <ul>
                ";
            // line 27
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "current_managed_events", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["event"]) {
                // line 28
                echo "                    <li id=\"my-";
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "id", array()), "html", null, true);
                echo "\">
                        <a href=\"/event/";
                // line 29
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "id", array()), "html", null, true);
                echo "\">
                            <div class=\"
                                name
                                ";
                // line 32
                if (($this->getAttribute($context["event"], "allow_registrations_calculated", array()) && ($this->getAttribute($context["event"], "remaining_registrations", array()) > 0))) {
                    // line 33
                    echo "                                    open
                                ";
                } elseif ($this->getAttribute(                // line 34
$context["event"], "allow_registrations_calculated", array())) {
                    // line 35
                    echo "                                    sold-out
                                ";
                } elseif ($this->getAttribute(                // line 36
$context["event"], "venue", array())) {
                    // line 37
                    echo "                                    venue
                                ";
                } else {
                    // line 39
                    echo "                                    closed
                                ";
                }
                // line 41
                echo "                            \" title=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "name", array()), "html", null, true);
                echo "\">
                                ";
                // line 42
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "abbr", array()), "html", null, true);
                echo "
                            </div>
                            ";
                // line 44
                if ( !$this->getAttribute($context["event"], "allow_registrations_calculated", array())) {
                    // line 45
                    echo "                                <div class=\"notify\">
                                    <span class=\"number\">";
                    // line 46
                    echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "notify", array()), "count", array()), "html", null, true);
                    echo "</span>
                                    <span class=\"label\">Subscribers</span>
                                </div>
                            ";
                } elseif ( !$this->getAttribute($this->getAttribute(                // line 49
(isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "isFuture", array())) {
                    // line 50
                    echo "                                <div class=\"here\">
                                    <span class=\"number\">";
                    // line 51
                    echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "attendees_here", array()), "count", array()), "html", null, true);
                    echo "</span>
                                    <span class=\"label\">Here</span>
                                </div>
                            ";
                } else {
                    // line 55
                    echo "                                <div class=\"registrants\">
                                    <span class=\"number\">";
                    // line 56
                    echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "registrations", array()), "count", array()), "html", null, true);
                    echo "</span>
                                    <span class=\"label\">Registrations</span>
                                </div>
                                <div class=\"today\">
                                    <span class=\"number\">";
                    // line 60
                    echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations_today", array())), "html", null, true);
                    echo "</span>
                                    <span class=\"label\">Today</span>
                                </div>
                                <div class=\"this-week\">
                                    <span class=\"number\">";
                    // line 64
                    echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations_this_week", array())), "html", null, true);
                    echo "</span>
                                    <span class=\"label\">This Week</span>
                                </div>
                                <div class=\"percent\">
                                    <span class=\"number\">";
                    // line 68
                    echo twig_escape_filter($this->env, twig_round((($this->getAttribute($this->getAttribute($context["event"], "registrations", array()), "count", array()) / $this->getAttribute($context["event"], "registration_estimate", array())) * 100), 0), "html", null, true);
                    echo "%</span>
                                    <span class=\"label\">Of Commitment</span>
                                </div>
                                <div class=\"predicted\">
                                    <span class=\"number\">?</span>
                                    <span class=\"label\">Predicted</span>
                                </div>
                            ";
                }
                // line 76
                echo "                        </a>
                    </li>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['event'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 79
            echo "            </ul>
        </section>
    ";
        }
        // line 82
        echo "    <section class=\"other-events\">
        <section class=\"list\">
            <ul>
                ";
        // line 85
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "events", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["event"]) {
            // line 86
            echo "                    <li id=\"hud-";
            echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "id", array()), "html", null, true);
            echo "\">
                        ";
            // line 87
            if ($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "is_admin", array())) {
                // line 88
                echo "                            <a href=\"/event/";
                echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "id", array()), "html", null, true);
                echo "\">
                        ";
            }
            // line 90
            echo "                            <div class=\"
                            name
                            ";
            // line 92
            if (($this->getAttribute($context["event"], "allow_registrations_calculated", array()) && ($this->getAttribute($context["event"], "remaining_registrations", array()) > 0))) {
                // line 93
                echo "                                open
                            ";
            } elseif ($this->getAttribute(            // line 94
$context["event"], "allow_registrations_calculated", array())) {
                // line 95
                echo "                                sold-out
                            ";
            } elseif ($this->getAttribute(            // line 96
$context["event"], "venue", array())) {
                // line 97
                echo "                                venue
                            ";
            } else {
                // line 99
                echo "                                closed
                            ";
            }
            // line 101
            echo "                        \" title=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "name", array()), "html", null, true);
            echo "\">
                                ";
            // line 102
            echo twig_escape_filter($this->env, $this->getAttribute($context["event"], "abbr", array()), "html", null, true);
            echo "
                            </div>
                            ";
            // line 104
            if ( !$this->getAttribute($context["event"], "allow_registrations_calculated", array())) {
                // line 105
                echo "                                <div class=\"notify\">
                                    <span class=\"number\">";
                // line 106
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "notify", array()), "count", array()), "html", null, true);
                echo "</span>
                                    <span class=\"label\">Sub</span>
                                </div>
                            ";
            } elseif ( !$this->getAttribute($this->getAttribute(            // line 109
(isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "isFuture", array())) {
                // line 110
                echo "                                <div class=\"here\">
                                    <span class=\"number\">";
                // line 111
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "attendees_here", array()), "count", array()), "html", null, true);
                echo "</span>
                                    <span class=\"label\">Here</span>
                                </div>
                            ";
            } else {
                // line 115
                echo "                                <div class=\"registrants\">
                                    <span class=\"number\">";
                // line 116
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "registrations", array()), "count", array()), "html", null, true);
                echo "</span>
                                    <span class=\"label\">Reg</span>
                                </div>
                                <div class=\"this-week\">
                                    <span class=\"number\">";
                // line 120
                echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations_this_week", array())), "html", null, true);
                echo "</span>
                                    <span class=\"label\">Week</span>
                                </div>
                            ";
            }
            // line 124
            echo "                        ";
            if ($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "is_admin", array())) {
                // line 125
                echo "                            </a>
                        ";
            }
            // line 127
            echo "                    </li>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['event'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 129
        echo "            </ul>
        </section>
        <section class=\"map\">
            <div class=\"heatmap\"></div>
        </section>
    </section>
";
    }

    // line 136
    public function block_scripts($context, array $blocks = array())
    {
        // line 137
        echo "    <script type=\"text/javascript\">
        (function(){
            var countdown_clock = {
                die_at: (";
        // line 140
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "starts_at", array()), "timestamp", array()), "html", null, true);
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
    <script type=\"text/javascript\">
        function initialize() {
            var mapElem = \$('div.heatmap')[0];
            var map = new google.maps.Map(mapElem, window.defaultMapOptions);
            map.setZoom(3);

            var overlay = new google.maps.OverlayView();
            overlay.draw = function() {};
            overlay.setMap(map);


            var eventRegs = [
                ";
        // line 221
        $context["isFirst"] = true;
        // line 222
        echo "                ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "events", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["event"]) {
            // line 223
            echo "                ";
            if ( !(isset($context["isFirst"]) ? $context["isFirst"] : null)) {
                echo ",";
            }
            // line 224
            echo "                    {
                        coords: new google.maps.LatLng(";
            // line 225
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "coordinates", array()), "lat", array()), "html", null, true);
            echo ", ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["event"], "coordinates", array()), "lng", array()), "html", null, true);
            echo "),
                        regs: ";
            // line 226
            echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["event"], "registrations", array())), "html", null, true);
            echo "
                    }
                ";
            // line 228
            $context["isFirst"] = false;
            // line 229
            echo "                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['event'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 230
        echo "            ];

            var points = [];
            for (var i in eventRegs) {
                var e = eventRegs[i];
                for (var j = 0; j < e.regs; j++) {
                    points.push(e.coords);
                }
            }

            heatmap = new google.maps.visualization.HeatmapLayer({
                data: new google.maps.MVCArray(points)
            });
            heatmap.setMap(map);
            heatmap.set('radius', 25);

            var tzColors = {
                \"Pacific\": '#2A85F7',
                \"Mountain\": '#639A93',
                \"Central\": '#C47061',
                \"Eastern\": '#DC6EED'
            };

            var circles = {
                    ";
        // line 254
        $context["isFirst"] = true;
        // line 255
        echo "                    ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["managed_batch"]) ? $context["managed_batch"] : null), "events", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["region"]) {
            // line 256
            echo "                    ";
            if ( !(isset($context["isFirst"]) ? $context["isFirst"] : null)) {
                echo ",";
            }
            // line 257
            echo "                    \"";
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute($context["region"], "webname", array()), "js"), "html", null, true);
            echo "\": {
                        \"name\": \"";
            // line 258
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute($context["region"], "name", array()), "js"), "html", null, true);
            echo "\",
                        \"id\": \"";
            // line 259
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute($context["region"], "id", array()), "js"), "html", null, true);
            echo "\",
                        \"webname\": \"";
            // line 260
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute($context["region"], "webname", array()), "js"), "html", null, true);
            echo "\",
                        \"abbr\": \"";
            // line 261
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute($context["region"], "abbr", array()), "js"), "html", null, true);
            echo "\",
                        \"manager_username\": \"";
            // line 262
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->getAttribute($context["region"], "manager_username", array()), "js"), "html", null, true);
            echo "\",
                        \"color\": tzColors[\"";
            // line 263
            echo twig_escape_filter($this->env, $this->getAttribute($context["region"], "simple_timezone", array()), "html", null, true);
            echo "\"],
                        \"circle\": new google.maps.Circle({
                    fillColor: tzColors[\"";
            // line 265
            echo twig_escape_filter($this->env, $this->getAttribute($context["region"], "simple_timezone", array()), "html", null, true);
            echo "\"],
                    fillOpacity: 1,
                    strokeColor: '#000000',
                    strokeOpacity: 0,
                    strokeWeight: 6,
                    radius: 70000,
                    map: map,
                    center: new google.maps.LatLng(";
            // line 272
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["region"], "coordinates", array()), "lat", array()), "html", null, true);
            echo ", ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["region"], "coordinates", array()), "lng", array()), "html", null, true);
            echo ")
                })
            }
            ";
            // line 275
            $context["isFirst"] = false;
            // line 276
            echo "            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['region'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 277
        echo "        };

        var divover = document.createElement('div');
        divover.style.position = 'absolute';
        divover.style.zIndex = '5000';
        divover.style.backgroundColor = '#2A85F7';
        divover.style.color = '#ffffff';
        divover.style.padding = '2px';
        divover.style.paddingLeft = '5px';
        divover.style.fontSize = '10px';
        divover.style.display = 'none';
        mapElem.insertBefore(divover, null);

        for (var city in circles) {
            var circle = circles[city].circle;

            google.maps.event.addListener(circle, \"click\", (function(circle, city) {
                return function() {
                    ";
        // line 295
        if ($this->getAttribute((isset($context["me"]) ? $context["me"] : null), "is_admin", array())) {
            // line 296
            echo "                        window.location = '/event/'+circles[city].id;
                    ";
        }
        // line 298
        echo "                };
            })(circle, city));
            google.maps.event.addListener(circle, \"mouseover\", (function(circle, name, color) {
                return function() {
                    var point = overlay.getProjection().fromLatLngToContainerPixel(circle.getCenter());
                    divover.style.left = (point.x + 8)+\"px\";
                    divover.style.top = (point.y - 8)+\"px\";
                    divover.style.backgroundColor = color;
                    divover.style.display = \"block\";
                    divover.textContent = name;
                };
            })(circle, circles[city].name, circles[city].color));

            google.maps.event.addListener(circle, \"mouseout\", function() {
                divover.style.display = \"none\";
            });
        }

            ";
        // line 317
        echo "            var textElem = document.createElement('input');
            textElem.classList.add('region-picker');
            textElem.placeholder = 'Filter by name...';
            mapElem.parentNode.appendChild(textElem);

            textElem.addEventListener('keyup', function(){
                var currentText = textElem.value;
                for (var city in circles) {
                    var circle = circles[city].circle;
                    var name = circles[city].name.toLowerCase();
                    var webname = circles[city].webname.toLowerCase();
                    var abbr = circles[city].abbr.toLowerCase();
                    var manager_username = circles[city].manager_username.toLowerCase();

                    if (currentText.length == 0
                            || city.substr(0, currentText.length) == currentText
                            || abbr.substr(0, currentText.length) == currentText
                            || webname.substr(0, currentText.length) == currentText
                            || manager_username.substr(0, currentText.length) == currentText
                            || name.substr(0, currentText.length) == currentText) {
                        circle.setMap(map);
                        \$('#hud-'+circles[city].id).css('visibility', 'visible');
                    } else {
                        circle.setMap(null);
                        \$('#hud-'+circles[city].id).css('visibility', 'hidden');
                    }
                }
            });
        }
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    <script type=\"text/javascript\">

        (function(d){d.each([\"backgroundColor\",\"borderBottomColor\",\"borderLeftColor\",\"borderRightColor\",\"borderTopColor\",\"color\",\"outlineColor\"],function(f,e){d.fx.step[e]=function(g){if(!g.colorInit){g.start=c(g.elem,e);g.end=b(g.end);g.colorInit=true}g.elem.style[e]=\"rgb(\"+[Math.max(Math.min(parseInt((g.pos*(g.end[0]-g.start[0]))+g.start[0]),255),0),Math.max(Math.min(parseInt((g.pos*(g.end[1]-g.start[1]))+g.start[1]),255),0),Math.max(Math.min(parseInt((g.pos*(g.end[2]-g.start[2]))+g.start[2]),255),0)].join(\",\")+\")\"}});function b(f){var e;if(f&&f.constructor==Array&&f.length==3){return f}if(e=/rgb\\(\\s*([0-9]{1,3})\\s*,\\s*([0-9]{1,3})\\s*,\\s*([0-9]{1,3})\\s*\\)/.exec(f)){return[parseInt(e[1]),parseInt(e[2]),parseInt(e[3])]}if(e=/rgb\\(\\s*([0-9]+(?:\\.[0-9]+)?)\\%\\s*,\\s*([0-9]+(?:\\.[0-9]+)?)\\%\\s*,\\s*([0-9]+(?:\\.[0-9]+)?)\\%\\s*\\)/.exec(f)){return[parseFloat(e[1])*2.55,parseFloat(e[2])*2.55,parseFloat(e[3])*2.55]}if(e=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(f)){return[parseInt(e[1],16),parseInt(e[2],16),parseInt(e[3],16)]}if(e=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(f)){return[parseInt(e[1]+e[1],16),parseInt(e[2]+e[2],16),parseInt(e[3]+e[3],16)]}if(e=/rgba\\(0, 0, 0, 0\\)/.exec(f)){return a.transparent}return a[d.trim(f).toLowerCase()]}function c(g,e){var f;do{f=d.css(g,e);if(f!=\"\"&&f!=\"transparent\"||d.nodeName(g,\"body\")){break}e=\"backgroundColor\"}while(g=g.parentNode);return b(f)}var a={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0],transparent:[255,255,255]}})(jQuery);


        \$('body').ready(function(){
            var previous_events = {};

            var setEvents = function(data)
            {
                for (var id in data) {
                    if(!data.hasOwnProperty(id)) {
                        continue;
                    }
                    var event = data[id];
                    var current_data = \$('#my-'+id+', #hud-'+id);
                    var alert_val;

                    if (event.allow_registrations) {
                        alert_val = event.registrations;
                    } else {
                        alert_val = event.notify;
                    }

                    if (previous_events[id] && previous_events[id] < alert_val) {
                        playSound('registration');

                        var nameplate = current_data.find('div.name');
                        var originalColor = nameplate.css('background-color');
                        nameplate.css(\"background-color\", \"#F9FFB3\")
                                .animate({ backgroundColor: originalColor}, 1500)
                                .animate({ backgroundColor: \"#F9FFB3\"}, 500)
                                .animate({ backgroundColor: originalColor}, 1500)
                                .animate({ backgroundColor: \"#F9FFB3\"}, 500)
                                .animate({ backgroundColor: originalColor}, 1500)
                                .animate({ backgroundColor: \"#F9FFB3\"}, 500)
                                .animate({ backgroundColor: originalColor}, 1500);
                    }

                    current_data.find('.registrants .number').text(event.registrations);
                    current_data.find('.today .number').text(event.today);
                    current_data.find('.this-week .number').text(event.this_week);
                    current_data.find('.percent .number').text(event.percent+'%');
                    current_data.find('.predicted .number').text(event.predicted);
                    current_data.find('.notify .number').text(event.notify);
                    current_data.find('.here .number').text(event.here)
                    previous_events[id] = alert_val;
                }
            }

            setInterval(function(){
                var xhr = \$.ajax({
                    method: 'GET',
                    dataType: 'json',
                    url: '/updates.json',
                    success: function(data) {
                        setEvents(data);
                    }
                });
                setTimeout(function(){
                    xhr.abort();
                }, 10000);
            }, 5000);
        });
    </script>
";
    }

    public function getTemplateName()
    {
        return "/home/tj/Clear/app/config/../views/dashboard.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  607 => 317,  587 => 298,  583 => 296,  581 => 295,  561 => 277,  555 => 276,  553 => 275,  545 => 272,  535 => 265,  530 => 263,  526 => 262,  522 => 261,  518 => 260,  514 => 259,  510 => 258,  505 => 257,  500 => 256,  495 => 255,  493 => 254,  467 => 230,  461 => 229,  459 => 228,  454 => 226,  448 => 225,  445 => 224,  440 => 223,  435 => 222,  433 => 221,  349 => 140,  344 => 137,  341 => 136,  331 => 129,  324 => 127,  320 => 125,  317 => 124,  310 => 120,  303 => 116,  300 => 115,  293 => 111,  290 => 110,  288 => 109,  282 => 106,  279 => 105,  277 => 104,  272 => 102,  267 => 101,  263 => 99,  259 => 97,  257 => 96,  254 => 95,  252 => 94,  249 => 93,  247 => 92,  243 => 90,  237 => 88,  235 => 87,  230 => 86,  226 => 85,  221 => 82,  216 => 79,  208 => 76,  197 => 68,  190 => 64,  183 => 60,  176 => 56,  173 => 55,  166 => 51,  163 => 50,  161 => 49,  155 => 46,  152 => 45,  150 => 44,  145 => 42,  140 => 41,  136 => 39,  132 => 37,  130 => 36,  127 => 35,  125 => 34,  122 => 33,  120 => 32,  114 => 29,  109 => 28,  105 => 27,  101 => 25,  99 => 24,  93 => 21,  89 => 20,  85 => 18,  81 => 16,  77 => 14,  75 => 13,  70 => 11,  66 => 10,  62 => 9,  58 => 8,  53 => 7,  51 => 6,  47 => 4,  44 => 3,  38 => 2,  11 => 1,);
    }
}

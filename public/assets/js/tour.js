(function(){
    "use strict";
    var pages = {
        'dashboard': function(tour) {
            var eventsCount = $('.my-events li').length;

            tour.addStep(
                'intro',
                {
                    title: 'Welcome to Clear!',
                    text: "Clear is the tool we use to manage CodeDay events. Since this is the first time you've" +
                        " logged in, we'll take a second to get you familiar.<br /><br />If you skip this tour, you" +
                        " can restart it later by clicking \"tour\" on the footer.",
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'my-events',
                {
                    title: 'Your Event' + (eventsCount > 1 ? 's' : ''),
                    text: "Here you can see an overview of the " + (eventsCount > 1 ? 'events' : 'event') +
                            " you're involved with.<br /><br />This is live-updating; as people register you'll" +
                            " see a flash and get a 'ding', so many people leave this page open in the background.",
                    attachTo: {
                        element: $('.my-events li:first-child .name')[0],
                        on: 'right'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
              'event-predictions',
              {
                title: 'Predictions',
                text: "With Clear, you can see how many registrations we predict for your event. "+
                      "If the prediction is over your venue's limit, you'll see a \"+\" after it.",
                attachTo: {
                  element: $('.my-events li:first-child .predicted')[0],
                  on: 'left'
                },
                scrollTo: false,
                showCancelLink: true
              }
            );

            tour.addStep(
                'event-settings',
                {
                    title: 'Your Event' + (eventsCount > 1 ? 's' : ''),
                    text: "You can click on an event to modify its settings. Let's try opening an event now.",
                    attachTo: {
                        element: $('.my-events li:first-child .name')[0],
                        on: 'right'
                    },
                    scrollTo: false,
                    showCancelLink: true,
                    buttons: [
                        {
                            text: 'Open ' + $('.my-events li:first-child .name').attr('title'),
                            action: function() {
                                localStorage['current-tour-page'] = 'event';
                                $('.my-events li:first-child .name').click()
                            }
                        }
                    ]
                }
            );
        },

        'event': function(tour) {
            tour.addStep(
                'event-status',
                {
                    title: 'Overview',
                    text: "By default, you'll land in the Overview page. Here, you can see some basic details about" +
                        " the event, like the number of registrants and the event revenue.<br /><br />You can also" +
                        " toggle some major event settings.",
                    attachTo: {
                        element: $('.status')[0],
                        on: $('#registrations-over-time').length > 0 ? 'top' : 'bottom'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'event-status',
                {
                    title: 'Notes',
                    text: "If there's anything unusual about the event, you can store it in Notes. This is usually" +
                        " used to store things like requests for gluten free food.",
                    attachTo: {
                        element: $('.notes')[0],
                        on: 'top'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'event-sidebar-registrations',
                {
                    title: 'Other Pages',
                    text: "Most event tools and settings are on other pages.<br /><br />For example, in registrations" +
                        " you can refund attendees, transfer their tickets, or manually add a new attendee.",
                    attachTo: {
                        element: $('.subnav .general li:nth-child(3n - 1)')[0],
                        on: 'right'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'tools',
                {
                    title: 'Tools',
                    text: "Most of the rest of the things you should do are in Tools. Let's take a look.",
                    attachTo: {
                        element: $('header nav li a[href="/tools"]')[0],
                        on: 'bottom'
                    },
                    scrollTo: false,
                    showCancelLink: true,
                    buttons: [
                        {
                            text: 'Open Tools',
                            action: function() {
                                localStorage['current-tour-page'] = 'tools';
                                $('header nav li a[href="/tools"]')[0].click()
                            }
                        }
                    ]
                }
            );
        },

        'tools': function(tour)
        {
            tour.addStep(
                'tools-attendee-finder',
                {
                    title: 'Attendee Finder',
                    text: "There are a few useful tools here.<br /><br />In the attendee finder, you can search for" +
                        " attendees across all events. If an attendee says xhe registered but xhe's not on your list," +
                        " you can check if xhe registered for another account here.",
                    attachTo: {
                        element: $('.subnav .batch-tools li:nth-child(3n - 2)')[0],
                        on: 'right'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'tools-checkin',
                {
                    title: 'Checkin',
                    text: "When you get to the event, you can load up the Checkin tool and start checking in attendees" +
                        " at the door.",
                    attachTo: {
                        element: $('.subnav .evangelism li:nth-child(3n - 2)')[0],
                        on: 'right'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'tools-kickoff',
                {
                    title: 'Kickoff Slide Deck',
                    text: "We automatically generate an online slide deck and notes for you to use at kickoff. You can" +
                        " access them in the Kickoff section.",
                    attachTo: {
                        element: $('.subnav .evangelism li:nth-child(3n - 1)')[0],
                        on: 'right'
                    },
                    scrollTo: false,
                    showCancelLink: true
                }
            );

            tour.addStep(
                'tools-kickoff',
                {
                    text: "That's about all you need to know! Feel free to poke around, and email us if you have any" +
                        " questions.",
                    scrollTo: false,
                    showCancelLink: true,

                    buttons: [
                        {
                            text: 'End the Tour',
                            action: function() {
                                delete localStorage['current-tour-page'];
                                $('header nav li a[href="/"]')[0].click();
                            }
                        }
                    ]
                }
            );
        }
    };

    $(document).ready(function(){
        var tour = new Shepherd.Tour({
            defaults: {
                classes: 'shepherd-theme-arrows',
                scrollTo: true
            }
        });

        $('#tour-start').on('click', function(){
            localStorage['current-tour-page'] = 'dashboard';
            window.location = '/';
        });

        tour.on('cancel', function(){
            delete localStorage['current-tour-page'];
        });

        var currentPage = $('body').attr('class');
        if (currentPage && pages.hasOwnProperty(currentPage) && localStorage['current-tour-page'] === currentPage) {
            pages[currentPage](tour);
            tour.start();
        }
    });

})();

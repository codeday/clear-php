<?php
namespace CodeDay\Clear\Services;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Html2Text\Html2Text;
use Postmark\PostmarkClient;

/**
 * Helps with the sending of emails to individuals or groups.
 *
 * The Email service is an interface to our SMTP servers which offloads emails to a queue for faster processing, and
 * also supports sending in the future and sending to lists.
 *
 * @package     CodeDay\Clear\Services
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2014-2015 StudentRND
 * @license     Perl Artistic License 2.0
 */
class Email {
    /**
     * Stand-in for Laravel's broken \Mail::queue(), which doesn't support sending mail with bound models or mail
     * without templates.
     *
     * @param string        $fromName               The name of the person sending the email.
     * @param string        $fromEmail              The email from which the email will appear to be sent.
     * @param string        $toName                 The name of the person receiving the email.
     * @param string        $toEmail                The email of the person receiving the email.
     * @param string        $subject                The email subject line.
     * @param View|string   $contentText            String or View representing the text part of the email, or null if
     *                                              HTML-only.
     * @param View|string   $contentHtml            String or View representing the HTML part of the email, or null if
     *                                              text-only.
     * @param bool          $isMarketing            True if the email is a marketing-related message and should comply
     *                                              with CAN-SPAM. False if the message is transactional.
     */
    public static function SendOnQueue($fromName, $fromEmail, $toName, $toEmail, $subject,
                                        $contentText, $contentHtml = null, $isMarketing = false)
    {
        // Render views if they were passed in
        if (is_object($contentText) && get_class($contentText) === 'Illuminate\View\View') {
            $contentText = $contentText->render();
        }
        if (is_object($contentHtml) && get_class($contentHtml) === 'Illuminate\View\View') {
            $contentHtml = $contentHtml->render();
        }

        // Inline the CSS
        if (isset($contentHtml)) {
            $inliner = new CssToInlineStyles();
            $inliner->setHtml($contentHtml);
            $inliner->setUseInlineStylesBlock(true);
            $inliner->setStripOriginalStyleTags(true);
            $contentHtml = $inliner->convert();
        }

        // Update unsubscribe link (the CSS inliner will remove this if we insert it before this point)
        if (isset($contentHtml)) {
            $contentHtml = str_replace('--unsubscribe--', '<%asm_preferences_raw_url%>', $contentHtml);
        }
        if (isset($contentText)) {
            $contentText = str_replace('--unsubscribe--', '<%asm_preferences_raw_url%>', $contentText);
        }

        // Generate any missing components
        if (!isset($contentText)) {
            $contentText = Html2Text::convert($contentHtml);
        } elseif (!isset($contentHtml)) {
            $contentHtml = nl2br($contentText);
        }

        // Submit for processing
        \Queue::push(function($job) use ($fromName, $fromEmail, $toName, $toEmail, $subject,
                                         $contentText, $contentHtml, $isMarketing) {
            if ($fromEmail !== 'tickets@codeday.org') {
                $sendgrid = new \SendGrid(config('sendgrid.api_key'));
                $email = new \SendGrid\Email();
                $email
                    ->addTo($toEmail, $toName)
                    ->setFrom($fromEmail)
                    ->setFromName($fromName)
                    ->setSubject($subject)
                    ->setAsmGroupId($isMarketing ? config('sendgrid.asm.marketing') : config('sendgrid.asm.transactional'))
                    ->setText($contentText)
                    ->setHtml($contentHtml);


                $sendgrid->send($email);
            } else {
                $postmark = new PostmarkClient(config('postmark.api_key'));
                $postmark->sendEmailBatch([[
                    'To' => "$toName <$toEmail>",
                    'From' => "$fromName <$fromEmail>",
                    'TrackOpens' => false,
                    'Subject' => $subject,
                    'TextBody' => $contentText,
                    'HtmlBody' => $contentHtml
                ]]);
            }
            $job->delete();
        });
    }

    /**
     * Enqueues an email to all [...] at an event (where [...] is controlled by $listType).
     *
     * @param string                $fromName                   The name of the person sending the email
     * @param string                $fromEmail                  The email from which the email will appear to be sent
     * @param Models\Batch\Event    $event                      The event to send the email to
     * @param string                $listType                   String controlling who gets the email at the event:
     *                                                            - me
     *                                                            - attendees
     *                                                            - nonreturning-attendees
     *                                                            - notify
     *                                                            - notify-unreg
     *                                                            - parents
     *                                                            - sponsors
     *                                                            - volunteers
     *                                                            - teachers
     *                                                            - event-staff
     * @param string                $subject                    The email subject line
     * @param mixed                 $contentText                String or View representing the text part of the email,
     *                                                          or null if HTML-only.
     * @param mixed                 $contentHtml                String or View representing the HTML part of the email,
     *                                                          or null if text-only.
     * @param array                 $additionalContext          Any additional context to be bound into the Views at
     *                                                          render-time. Because Views are rendered on the queue,
     *                                                          any context is serialized and deserialized. Passing
     *                                                          models into $additionalContext instead of the Views
     *                                                          directly will be more reliable due to special
     *                                                          processing.
     * @param bool                  $isMarketing                True if the email is a marketing-related message and
     *                                                          should comply with CAN-SPAM. False if the message is
     *                                                          transactional.
     */
    public static function SendToEvent($fromName, $fromEmail, Models\Batch\Event $event, $listType, $subject,
                                       $contentText, $contentHtml = null,
                                       $additionalContext = [], $isMarketing = false)
    {
        // Send a chat post!
        try {
            $stubAttendee = [
                'name' => '[name]',
                'email' => '[email]',
                'registration' => ['first_name' => '[name]', 'last_name' => '', '[name]' => 'everyone', 'id' => '[ticket id]']
            ];

            $rSubject = Email::renderTemplateWithContext($subject, array_merge($stubAttendee, $additionalContext));
            $messageRender = Email::renderTemplateWithContext($additionalContext['content'], array_merge($stubAttendee, $additionalContext, ['subject' => $rSubject]));
            if ($listType === "attendees") {
                $postText = sprintf("@here Your event team just sent out an email! Here's the info --\n\n#### %s\n```\n%s\n```", $subject, $messageRender);
                Mattermost::Message("community", $event->webname, $postText);
                if ($event->webname !== $event->region->webname) {
                    Mattermost::Message("community", $event->region->webname, $postText);
                }
            }
        } catch (\Exception $ex) {}

        $eventId = $event->id;
        $contentText = self::serializeView($contentText);
        $contentHtml = self::serializeView($contentHtml);


        $fn = function($job) use ($listType, $fromName, $fromEmail, $eventId, $subject,
                                         $contentText, $contentHtml, $additionalContext, $isMarketing) {

            // Deserialize views
            $contentText = Email::deserializeView($contentText);
            $contentHtml = Email::deserializeView($contentHtml);

            // Refresh any models from the database because serialization doesn't always work
            $event = Models\Batch\Event::where('id', '=', $eventId)->firstOrFail();
            $additionalContext = Email::refreshContextModels($additionalContext);

            foreach (Email::getToListFromType($event, $listType) as $to) {
                try {
                    $rSubject = Email::renderTemplateWithContext($subject, array_merge((array)$to, $additionalContext));
                    Email::SendOnQueue(
                        $fromName, $fromEmail,
                        $to->name, $to->email,
                        $rSubject,
                        Email::renderTemplateWithContext($contentText, array_merge((array)$to, $additionalContext, ['subject' => $rSubject])),
                        Email::renderTemplateWithContext($contentHtml, array_merge((array)$to, $additionalContext, ['subject' => $rSubject])),
                        $isMarketing
                    );
                } catch (\Exception $ex) {}
            }

            if (isset($job)) {
                $job->delete();
            }
        };


        if ($listType === 'me') {
            $fn(null);
        } else {
            \Queue::push($fn);
        }
    }


    /**
     * Enqueues an email to all [...] at an event in a batch (where [...] is controlled by $listType).
     *
     * @param string                $fromName                   The name of the person sending the email
     * @param string                $fromEmail                  The email from which the email will appear to be sent
     * @param Models\Batch          $batch                      The batch to which the email should be sent
     * @param string                $listType                   String controlling who gets the email at the event:
     *                                                            - me
     *                                                            - attendees
     *                                                            - nonreturning-attendees
     *                                                            - notify
     *                                                            - notify-unreg
     *                                                            - parents
     *                                                            - sponsors
     *                                                            - volunteers
     *                                                            - teachers
     *                                                            - event-staff
     * @param string                $subject                    The email subject line
     * @param mixed                 $contentText                String or View representing the text part of the email,
     *                                                          or null if HTML-only.
     * @param mixed                 $contentHtml                String or View representing the HTML part of the email,
     *                                                          or null if text-only.
     * @param array                 $additionalContext          Any additional context to be bound into the Views at
     *                                                          render-time. Because Views are rendered on the queue,
     *                                                          any context is serialized and deserialized. Passing
     *                                                          models into $additionalContext instead of the Views
     *                                                          directly will be more reliable due to special
     *                                                          processing.
     * @param bool                  $isMarketing                True if the email is a marketing-related message and
     *                                                          should comply with CAN-SPAM. False if the message is
     *                                                          transactional.
     * @param bool                  $onlyWithOpenRegistrations  If true, emails will only be sent to events which have
     *                                                          registrations enabled.
     */
    public static function SendToBatch($fromName, $fromEmail, Models\Batch $batch, $listType, $subject, $contentText,
                                       $contentHtml = null, $additionalContext = [], $isMarketing = false,
                                       $onlyWithOpenRegistrations = false)
    {
        $batchId = $batch->id;
        $contentText = self::serializeView($contentText);
        $contentHtml = self::serializeView($contentHtml);

        \Queue::push(function($job) use ($fromName, $fromEmail, $batchId, $listType, $subject, $contentText,
                                         $contentHtml, $additionalContext, $isMarketing, $onlyWithOpenRegistrations) {

            // Deserialize views
            $contentText = Email::deserializeView($contentText);
            $contentHtml = Email::deserializeView($contentHtml);

            // Refresh any models from the database because serialization doesn't always work
            $batch = Models\Batch::where('id', '=', $batchId)->firstOrFail();
            $additionalContext = Email::refreshContextModels($additionalContext);

            foreach ($batch->events as $event) {
                if ($onlyWithOpenRegistrations && !$event->allow_registrations_calculated) {
                    continue;
                }

                $eventContract = ModelContracts\Event::Model($event, ['internal']);
                $additionalContextCopy = array_merge(['event' => $eventContract], $additionalContext);

                $fromNameCopy = Email::renderTwigString($fromName, $additionalContextCopy);
                $fromEmailCopy = Email::renderTwigString($fromEmail, $additionalContextCopy);

                Email::SendToEvent($fromNameCopy, $fromEmailCopy, $event, $listType, $subject, $contentText,
                    $contentHtml, $additionalContextCopy, $isMarketing);
            }

            $job->delete();
        });
    }

    /**
     * Renders a view with the given context bound in.
     *
     * @param $view         View|string         The view to render. (If a string, will be treated as a Twig template.)
     * @param $context      object[]            The context to bind to the view. An array of name-value pairs.
     *
     * @return null|string                      Null if $view is null, otherwise the rendered template as a string.
     */
    public static function renderTemplateWithContext($view, $context)
    {
        if ($view === null) {
            return null;
        } elseif (is_object($view) && get_class($view) === 'Illuminate\View\View') {
            return $view->with($context)->render();
        } else {
            return self::renderTwigString($view, $context);
        }
    }

    /**
     * Gets all available list types.
     *
     * @return Object[]                         An array containing descriptions of the list types:
     *                                            - id: the ID string to use for $listType in related messages.
     *                                            - name: the name to display to e.g. a user.
     *                                            - lambda: the function which, taking an event, returns a list of the
     *                                                people on the email list as objects with:
     *                                                  - name: the full name of the recipient.
     *                                                  - email: the email of the recipient.
     *                                                  - registration: the Model representing the registration of the
     *                                                      recipient.
     */
    public static function GetToListTypes()
    {
        $compare_registration = function($a, $b) {
            return ($a->email === $b->email) ? 0 : 1;
        };

        return [
            'me' => [
                'id' => 'me',
                'name' => 'Me - for testing',
                'lambda' => function($event) {
                    return [
                        (object)[
                            'name' => Models\User::me()->name,
                            'email' => Models\User::me()->username.'@srnd.org',
                            'registration' => ModelContracts\Registration::Model(
                                Models\Batch\Event\Registration::orderByRaw('RAND()')->first()
                            , ['internal'])
                        ]
                    ];
                }
            ],

            'attendees' => [
                'id' => 'attendees',
                'name' => 'Attendees',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return $registration->type === 'student';
                    }));
                }
            ],

            'nonreturning-attendees' => [
                'id' => 'nonreturning-attendees',
                'name' => "Attendees Of The Last Event Who Haven't Re-Registered",
                'lambda' => function($event) use ($compare_registration) {
                    if (!$event->previous_event) {
                        return [];
                    }

                    $twice_attendees = array_uintersect(iterator_to_array($event->registrations),
                        iterator_to_array($event->previous_event->registrations), $compare_registration);

                    $nonreturning_attendees = array_udiff( iterator_to_array($event->previous_event->registrations),
                        $twice_attendees, $compare_registration);

                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter($nonreturning_attendees, function($registration) {
                        return $registration->type === 'student';
                    }));
                }
            ],

            'attendees-checkedin' => [
                'id' => 'attendees-checkedin',
                'name' => 'Attendees Who Checked In',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return $registration->type === 'student' && isset($registration->checked_in_at);
                    }));
                }
            ],

            'notify' => [
                'id' => 'notify',
                'name' => 'Notification Subscribers',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->email,
                            'email' => $user->email
                        ];
                    }, iterator_to_array($event->notify));
                }
            ],

            'notify-unreg' => [
                'id' => 'notify-unreg',
                'name' => "Notification Subscribers Who Haven't Registered",
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->email,
                            'email' => $user->email
                        ];
                    }, $event->unregistered_notify);
                }
            ],

            'parents' => [
                'id' => 'parents',
                'name' => 'Parents',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->parent_name,
                            'email' => $user->parent_email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return isset($registration->parent_name) && isset($registration->parent_email);
                    }));
                }
            ],

            'sponsors' => [
                'id' => 'sponsors',
                'name' => 'Sponsors',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return $registration->type === 'sponsor';
                    }));
                }
            ],

            'teachers' => [
                'id' => 'teachers',
                'name' => 'Teachers',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return $registration->type === 'teacher';
                    }));
                }
            ],

            'mentors' => [
                'id' => 'mentors',
                'name' => 'Mentors',
                'lambda' => function($event) {
                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return $registration->type === 'mentor';
                    }));
                }
            ],

            'event-staff' => [
                'id' => 'event-staff',
                'name' => 'Event Staff',
                'lambda' => function($event) {
                    $people = array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user, ['internal'])
                        ];
                    }, array_filter(iterator_to_array($event->registrations), function($registration) {
                        return $registration->type === 'volunteer';
                    }));

                    if ($event->manager) {
                        $people[] = (object)[
                            'name' => $event->manager->name,
                            'email' => $event->manager->email
                        ];
                    }

                    if ($event->evangelist) {
                        $people[] = (object)[
                            'name' => $event->evangelist->name,
                            'email' => $event->manager->email
                        ];
                    }

                    foreach ($event->subusers as $subuser) {
                        $people[] = (object)[
                            'name' => $subuser->name,
                            'email' => $subuser->email
                        ];
                    }

                    return $people;
                }
            ],

            'rms' => [
                'id' => 'event-staff',
                'name' => 'Event Staff',
                'lambda' => function($event) {
                    if ($event->manager) {
                        return [(object)[
                            'name' => $event->manager->name,
                            'email' => $event->manager->email
                        ]];
                    } else return [];
                }
            ],

            'venues' => [
                'id' => 'venues',
                'name' => 'Venues',
                'lambda' => function($event) {
                    return [(object)[
                        'name' => $event->venue_contact_name,
                        'email' => $event->venue_contact_email
                    ]];
                }
            ],
        ];
    }

    /**
     * Gets the list of recipients given an event and list type.
     *
     * @param Models\Batch\Event    $event                  The event to send the emails to.
     * @param string                $toListType             String controlling who gets an email at an event. Valid
     *                                                      choices are any of the IDs from GetListTypes().
     *
     * @return Object[]                                     An array of objects, each in the format specified in
     *                                                      GetListTypes().
     * @throws \Exception
     */
    public static function getToListFromType($event, $toListType)
    {
        if (!array_key_exists($toListType, self::GetToListTypes())) {
            throw new \Exception("Unknown list type: ".$toListType);
        }

        $lambda = self::GetToListTypes()[$toListType]['lambda'];
        return $lambda($event);
    }

    /**
     * Refreshes model data from the database. Intended for use in serialized closures so that Laravel can act on actual
     * models, rather than serialized representations.
     *
     * @param object[]  $additionalContext      Key-value array of parameters to be passed to a template. From this
     *                                          array, any instances of \Eloquent will be refreshed.
     *
     * @return object[]                         Key-value array of parameters to be passed to a template, with all
     *                                          instances of \Eloquent refreshed.
     */
    public static function refreshContextModels($additionalContext)
    {
        $additionalContextCopy = $additionalContext; // Copy so we can change the class while iterating
        foreach ($additionalContextCopy as $k => $v) {
            if (is_subclass_of($v, '\Eloquent')) {
                $class = get_class($v);
                $additionalContext[$k] = $class::where($v->getKeyName(), '=', $v->{$v->getKeyName()})->first();
            }

            // TODO: Better support for collections
        }

        return $additionalContext;
    }

    /**
     * Converts a View into an array of data which can be serialized in a closure.
     *
     * @param object         $view      The view or primitive to serialize
     * @return string[]                 Array containing the data associated with the view
     */
    public static function serializeView($view)
    {
        if (is_a($view, '\Illuminate\View\View')) {
            return [
                'type' => 'view',
                'path' => $view->getName(),
                'data' => $view->getData()
            ];
        } else {
            return [
                'type' => 'primitive',
                'data' => $view
            ];
        }
    }

    /**
     * Converts a serialized view into a usable View.
     *
     * @param String[]      $view       The serialized view to deserialize
     * @return View                     The deserialized View
     */
    public static function deserializeView($view)
    {
        if ($view['type'] === 'primitive') {
            return $view['data'];
        } else {
            return \View::make($view['path'], $view['data']);
        }
    }

    /**
     * Checks if we are prevented from sending an email to this recipient.
     *
     * @param string    $email                  The recipient's email (the one to check against our blacklist).
     * @param bool      $isMarketing            True if the email we're sending is promotional in nature, false if it's
     *                                          transactional.
     *
     * @return bool                             True if the email cannot be sent to the recipient.
     */
    private static function isRecipientBlacklisted($email, $isMarketing)
    {
        $unsub = Models\Unsubscribe::where('email', '=', $email)->first();

        if (!$unsub) return false;
        else return $unsub->type === 'all' || $isMarketing;
    }


    /**
     * Helper function which renders a Twig string and associated context into HTML.
     *
     * @param           $templateString             String to render as a Twig template
     * @param array     $context                    Context to bind into the string
     * @return string                               Rendered Twig template
     */
    public static function renderTwigString($templateString, $context = [])
    {
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader,
            ['cache' => storage_path() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'twig']); // Needed because of
        // hhvm eval() bug.
        return $twig->render($templateString, $context);
    }
}

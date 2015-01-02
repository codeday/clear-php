<?php
namespace CodeDay\Clear\Services;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Email {
    /**
     * Stand-in for Laravel's broken \Mail::queue(), which doesn't support sending mail with bound models or mail
     * without templates.
     *
     * @param $fromName
     * @param $fromEmail
     * @param $toName
     * @param $toEmail
     * @param $subject
     * @param $contentText
     * @param null $contentHtml
     */
    public static function SendOnQueue($fromName, $fromEmail, $toName, $toEmail, $subject,
                                        $contentText, $contentHtml = null)
    {
        // Render views if they were passed in
        if (is_object($contentText) && get_class($contentText) === 'Illuminate\View\View') {
            $contentText = $contentText->render();
        }
        if (is_object($contentHtml) && get_class($contentHtml) === 'Illuminate\View\View') {
            $contentHtml = $contentHtml->render();
        }

        // If there's no HTML part, render the text part as HTML
        if ($contentHtml === null) {
            $contentHtml = nl2br($contentText);
        }

        // Enqueue the email
        \Mail::queue(['emails/blank_html', 'emails/blank_text'],
            ['content_text' => $contentText, 'content_html' => $contentHtml],
            function($envelope) use ($fromEmail, $fromName, $toEmail, $toName, $subject) {
                $envelope->from(trim($fromEmail), $fromName);
                $envelope->to(trim($toEmail), $toName);
                $envelope->subject($subject);
            }
        );
    }

    public static function SendToEvent($fromName, $fromEmail, $event, $listType, $subject,
                                       $contentText, $contentHtml = null,
                                       $additionalContext = [])
    {
        foreach (self::getToListFromType($event, $listType) as $to) {
            self::SendOnQueue(
                $fromName, $fromEmail,
                $to->name, $to->email,
                self::renderTemplateWithContext($subject, array_merge((array)$to, $additionalContext)),
                self::renderTemplateWithContext($contentText, array_merge((array)$to, $additionalContext)),
                self::renderTemplateWithContext($contentHtml, array_merge((array)$to, $additionalContext))
            );
        }
    }

    public static function PreviewToEvent($fromName, $fromEmail, $event, $listType, $subject,
                                       $contentText, $contentHtml = null,
                                       $additionalContext = [])
    {
        $list = self::getToListFromType($event, $listType);
        if (count($list) === 0) {
            return (object)[
                'count' => 0
            ];
        }

        $to = $list[0];
        $contentTextRendered = self::renderTemplateWithContext($contentText, array_merge((array)$to, $additionalContext));
        if ($contentHtml === null) {
            $contentHtmlRendered = nl2br($contentTextRendered);
        } else {
            $contentHtmlRendered = self::renderTemplateWithContext($contentHtml, array_merge((array)$to, $additionalContext));
        }

        return (object)[
            'count' => count($list),
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'to_name' => $to->name,
            'to_email' => $to->email,
            'subject' => self::renderTemplateWithContext($subject, array_merge((array)$to, $additionalContext)),
            'content_text' => $contentTextRendered,
            'content_html' => $contentHtmlRendered
        ];
    }

    private static function renderTemplateWithContext($view, $context)
    {
        if ($view === null) {
            return null;
        } elseif (is_object($view) && get_class($view) === 'Illuminate\View\View') {
            return $view->with($context)->render();
        } else {
            return self::renderTwigString($view, $context);
        }
    }

    public static function GetToListTypes()
    {
        $compare_registration = function($a, $b) {
            return $a->email === $b->email;
        };

        return [
            'me' => [
                'id' => 'me',
                'name' => 'Me - for testing',
                'lambda' => function($event) {
                    return [
                        (object)[
                            'name' => Models\User::me()->name,
                            'email' => Models\User::me()->username.'@studentrnd.org',
                            'registration' => ModelContracts\Registration::Model(
                                Models\Batch\Event\Registration::orderByRaw('RAND()')->first()
                            )
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
                            'registration' => ModelContracts\Registration::Model($user)
                        ];
                    }, iterator_to_array($event->registrations));
                }
            ],

            'nonreturning-attendees' => [
                'id' => 'nonreturning-attendees',
                'name' => "Attendees Of The Last Event Who Haven't Re-Registered",
                'lambda' => function($event) use ($compare_registration) {
                    $previous_event = Models\Batch\Event
                        ::select('batches_events.*')
                        ->join('batches', 'batches_events.batch_id', '=', 'batches.id')
                        ->where('batches.starts_at', '<', $event->batch->starts_at)
                        ->where('batches_events.region_id', '=', $event->region_id)
                        ->orderBy('batches.starts_at', 'DESC')
                        ->first();

                    if (!$previous_event) {
                        return [];
                    }

                    $twice_attendees = array_uintersect(iterator_to_array($event->registrations),
                        iterator_to_array($previous_event->registrations), $compare_registration);

                    $nonreturning_attendees = array_udiff($twice_attendees,
                        iterator_to_array($previous_event->registrations), $compare_registration);

                    return array_map(function($user){
                        return (object)[
                            'name' => $user->name,
                            'email' => $user->email,
                            'registration' => ModelContracts\Registration::Model($user)
                        ];
                    }, $nonreturning_attendees);
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
            ]
        ];
    }

    private static function getToListFromType($event, $toListType)
    {
        if (!array_key_exists($toListType, self::GetToListTypes())) {
            throw new \Exception("Unknown list type: ".$toListType);
        }

        $lambda = self::GetToListTypes()[$toListType]['lambda'];
        return $lambda($event);
    }


    /**
     * Helper function which renders a Twig string and associated context into HTML.
     *
     * @param $templateString
     * @param array $context
     * @return string
     */
    private static function renderTwigString($templateString, $context = [])
    {
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader);
        return $twig->render($templateString, $context);
    }
} 
<?php
namespace CodeDay\Clear\Services;

use \Carbon\Carbon;

/**
 * Reads metadata about this project's Git status.
 *
 * The GitRepository Service is an interface to the metadata stored about the site in Git. It allows getting information
 * such as revision, authored time, and author of the currently running commit.
 *
 * @package     CodeDay\Clear\Services
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2014-2015 StudentRND
 * @license     Perl Artistic License 2.0
 */
class GitRepository {

    public static function getAuthoredTime()
    {
        try {
            return Carbon::createFromTimestamp(static::getBlobMetadata(self::getVersion())->header['author']->time);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getAuthor()
    {
        try {
            return static::getBlobMetadata(self::getVersion())->header['author']->name;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getVersion()
    {
        try {
            return static::getGitCommit('HEAD');
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getVersionShort()
    {
        try {
            return substr(self::getVersion(), -10);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getBlobMetadata($commit)
    {
        if (\Cache::has('git-repository.'.$commit)) {
            return \Cache::get('git-repository.'.$commit);
        }

        // Get the blob data
        $commit_base = substr($commit, 0, 2);
        $commit_file = substr($commit, 2);
        $full_file = implode(DIRECTORY_SEPARATOR, [self::getGitBase(), 'objects', $commit_base, $commit_file]);
        if (!file_exists($full_file)) {
            return null;
        }
        $full_contents = file_get_contents($full_file);
        $blob = gzuncompress($full_contents);

        // Process the data
        $result = (object)[
            'header' => [],
            'body' => []
        ];
        $isHeader = true;
        foreach (explode("\n", $blob) as $line) {
            if (strlen(trim($line)) === 0) {
                $isHeader = false;
                continue;
            }
            if ($isHeader) {
                list($key, $value) = preg_split('/[ \t]+/', trim($line), 2);
                if (in_array($key, ['author', 'committer'])) {
                    $split_parts = preg_split('/[ \t]+/', trim($value));
                    $tz = $split_parts[count($split_parts) - 1] * 36;
                    $time = $split_parts[count($split_parts) - 2];
                    $name = implode(' ', array_slice($split_parts, 0, count($split_parts) - 2));
                    $timezone_name = timezone_name_from_abbr(null, $tz, true);
                    $dt = new \DateTime('@'.$time, new \DateTimeZone($timezone_name));
                    $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                    $value = (object)[
                        'name' => $name,
                        'time' => $dt->getTimestamp()
                    ];
                }
                $result->header[$key] = $value;
            } else {
                $result->body[] = $line;
            }
        }
        $result->body = implode("\n", $result->body);

        \Cache::put('git-repository.'.$commit, $result, 1440);

        return $result;
    }

    private static function getGitCommit($pointer)
    {
        $full_file = implode(DIRECTORY_SEPARATOR, [self::getGitBase(), $pointer]);
        if (!file_exists($full_file)) {
            return null;
        }
        $content = file_get_contents($full_file);
        if (substr($content, 0, 4) === 'ref:') {
            list($ref_str, $ref) = explode(':', $content);
            return self::getGitCommit(trim($ref));
        } else {
            return trim($content);
        }
    }

    private static function getGitBase()
    {
        return implode(DIRECTORY_SEPARATOR, [dirname(dirname(__DIR__)), '.git']);
    }

}
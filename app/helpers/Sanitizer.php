<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTime;

/**
 * Class Sanitizer
 * Input sanitization, XSS escaping, and display formatting helpers.
 */
class Sanitizer
{
    /**
     * Escape HTML output against XSS attacks.
     */
    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Clean and trim input array recursively.
     */
    public static function clean(mixed $data): mixed
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = self::clean($value);
            }
            return $cleaned;
        }

        if (is_string($data)) {
            return trim($data);
        }

        return $data;
    }

    /**
     * Format a date string safely.
     */
    public static function formatDate(?string $dateStr, string $format = 'M d, Y'): string
    {
        if (empty($dateStr)) {
            return 'No date';
        }

        try {
            $dt = new DateTime($dateStr);
            return $dt->format($format);
        } catch (\Throwable) {
            return (string) $dateStr;
        }
    }

    /**
     * Format a relative human-readable timestamp (e.g., "5 mins ago", "Due in 2 days", "Overdue by 3 days").
     */
    public static function relativeTime(?string $datetimeStr): string
    {
        if (empty($datetimeStr)) {
            return '';
        }

        try {
            $dt = new DateTime($datetimeStr);
            $now = new DateTime();
            $diff = $now->diff($dt);

            if ($dt > $now) {
                // Future date (e.g., due dates)
                if ($diff->days === 0) {
                    return 'Due today';
                }
                if ($diff->days === 1) {
                    return 'Due tomorrow';
                }
                return "Due in {$diff->days} days";
            }

            // Past date
            if ($diff->days === 0) {
                if ($diff->h === 0) {
                    if ($diff->i === 0) {
                        return 'Just now';
                    }
                    return "{$diff->i}m ago";
                }
                return "{$diff->h}h ago";
            }
            if ($diff->days === 1) {
                return 'Yesterday';
            }
            if ($diff->days < 30) {
                return "{$diff->days} days ago";
            }

            return $dt->format('M d, Y');
        } catch (\Throwable) {
            return (string) $datetimeStr;
        }
    }

    /**
     * Format relative overdue time.
     */
    public static function overdueText(?string $dueDateStr): string
    {
        if (empty($dueDateStr)) {
            return '';
        }

        try {
            $due = new DateTime($dueDateStr . ' 23:59:59');
            $now = new DateTime();

            if ($now > $due) {
                $diff = $now->diff($due);
                if ($diff->days === 0) {
                    return 'Due today';
                }
                return "Overdue by {$diff->days} " . ($diff->days === 1 ? 'day' : 'days');
            }

            $diff = $due->diff($now);
            if ($diff->days === 0) {
                return 'Due today';
            }
            if ($diff->days === 1) {
                return 'Due tomorrow';
            }
            return "{$diff->days} days left";
        } catch (\Throwable) {
            return '';
        }
    }
}

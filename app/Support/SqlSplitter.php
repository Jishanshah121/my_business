<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Splits a .sql file into individual executable statements.
 *
 * Quote-, backtick- and comment-aware, so a semicolon inside a string literal
 * or a `-- comment` does not truncate a statement.
 */
final class SqlSplitter
{
    /** @return list<string> */
    public static function split(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $i = 0;

        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;

        while ($i < $length) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if (!$inSingle && !$inDouble && !$inBacktick) {
                // Line comment: -- or #
                if (($char === '-' && $next === '-') || $char === '#') {
                    $newline = strpos($sql, "\n", $i);
                    $i = $newline === false ? $length : $newline + 1;
                    continue;
                }
                // Block comment: /* ... */
                if ($char === '/' && $next === '*') {
                    $end = strpos($sql, '*/', $i + 2);
                    $i = $end === false ? $length : $end + 2;
                    continue;
                }
                if ($char === ';') {
                    $trimmed = trim($buffer);
                    if ($trimmed !== '') {
                        $statements[] = $trimmed;
                    }
                    $buffer = '';
                    $i++;
                    continue;
                }
            }

            // Escaped character inside a quoted string.
            if (($inSingle || $inDouble) && $char === '\\') {
                $buffer .= $char . $next;
                $i += 2;
                continue;
            }

            if ($char === "'" && !$inDouble && !$inBacktick) {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && !$inBacktick) {
                $inDouble = !$inDouble;
            } elseif ($char === '`' && !$inSingle && !$inDouble) {
                $inBacktick = !$inBacktick;
            }

            $buffer .= $char;
            $i++;
        }

        $trimmed = trim($buffer);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }
}

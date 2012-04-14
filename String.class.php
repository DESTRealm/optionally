<?php

namespace org\destrealm\utilities\optionally;

/**
 * String manipulation library for Optionally.
 */
class String
{

    /**
     * Normalizes the string text such that unnecessary whitespace is removed.
     * This converts text passed into Optionally's describe() such that the user
     * needn't be concerned with precisely how it might be spaced as Optionally
     * will handle indentation.
     *
     * For instance, if given the string "This\n   is a\n   sentence:\n\nI am."
     * the result would be:
     *
     * This is a sentence:
     *
     * I am.
     *
     * Note: Newlines are preserved.
     * @param  string $text String to normalize.
     * @return string Normalized string.
     */
    public static function normalize ($text)
    {
        return trim(
            preg_replace(
                '#(\v)\h(\b)#',
                '\\1\\2',
                preg_replace(
                    '#\h\h+#',
                    ' ',
                    preg_replace(
                        '#([^\n])\n(\b|\h)#',
                        '\\1 \\2',
                        preg_replace(
                            '#(\w)\n(\w|\h)#',
                            "\\1 \\2",
                            self::normalizeLineEndings($text)
                        )
                    )
                )
            )
        );
    } // end normalize ()

    /**
     * Normalizes line endings from MSDOS (\r\n) and pre-OS X (\r) to Unix (\n).
     * @param  string $text Input text.
     * @return string Text containing normalized line endings.
     */
    public static function normalizeLineEndings ($text)
    {
        if (strpos($text, "\r") === false) {
            return $text;
        }

        return str_replace("\r", "\n", str_replace("\r\n", "\n", $text));
    } // end normalizeLineEndings ()

    /**
     * Wraps the text $text at column $columns.
     * @param  string  $text       Input string.
     * @param  integer $columns=80 Column at which wrapping should take place.
     * @return string Wrapped string.
     */
    public static function wrap ($text, $columns=80)
    {
        // Normalize line endings. Hi, Windows!
        $text = self::normalizeLineEndings($text);
        $pos = 0;
        $buf = '';

        while (true) {

            if ($pos + $columns < strlen($text)) {
                $chunk = substr($text, $pos, $columns);
            } else {
                $buf .= substr($text, $pos);
                break;
            }

            if (($nl = strpos($chunk, "\n")) !== false) {
                $buf .= substr($chunk, 0, $nl + 1);
                $pos += $nl + 1;
                continue;
            }

            $posSpace   = strrpos($chunk, ' ');
            $posHyphen  = strrpos($chunk, '-')+1;
            $end = max($posSpace, $posHyphen);

            $charAt = substr($chunk, $end, 1);

            $buf .= substr($chunk, 0, $end);
            $pos += $end;

            if ($charAt === ' ') {
                $pos += 1;
            }

            $buf .= "\n";
        }

        return $buf;
    } // end wrap ()
} // end String
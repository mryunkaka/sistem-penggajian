<?php

function cleanString($value)
{
    // If value is null, return empty string to avoid null errors
    if ($value === null) {
        return '';
    }

    // Convert to string if it's not already
    $value = (string)$value;

    // Fix UTF-8 encoding issues
    if (!mb_check_encoding($value, 'UTF-8')) {
        $value = mb_convert_encoding($value, 'UTF-8', 'auto');
    }

    // Additional sanitization if needed
    $value = trim($value);

    return $value;
}

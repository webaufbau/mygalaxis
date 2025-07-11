<?php
function trim_recursive($value) {
    if (is_array($value)) {
        return array_map('trim_recursive', $value);
    }
    if (is_string($value)) {
        return trim($value);
    }
    return $value; // z.B. null, int, etc. unverändert lassen
}

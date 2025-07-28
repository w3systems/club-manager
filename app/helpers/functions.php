<?php
// app/helpers/functions.php

// Note: This file contains global helper functions.
// They are loaded directly into the global namespace via Composer's 'files' autoloading.
// DO NOT define a namespace for this file.

//use DateTime;
//use DateTimeZone;

// Basic templating functions (kept here for simplicity and global accessibility for views)
$__sections = [];
$__current_section = null;

function start_section($name) {
    global $__sections, $__current_section;
    $__current_section = $name;
    ob_start();
}

function end_section() {
    global $__sections, $__current_section;
    $__sections[$__current_section] = ob_get_clean();
    $__current_section = null;
}

function yield_section($name) {
    global $__sections;
    echo $__sections[$name] ?? '';
}

/**
 * Calculates age based on date of birth.
 * @param string|null $dob Date of birth in YYYY-MM-DD format.
 * @return int|null Age in years, or null if DOB is invalid.
 */
function calculateAge($dob) {
    if (!$dob) return null;
    try {
        // Use a specific timezone for consistent calculations, e.g., 'UTC' or 'Europe/London'
        // For date of birth, UTC is often safest to avoid daylight saving shifts affecting age calculation on boundaries.
        $birthDate = new \DateTime($dob, new \DateTimeZone('UTC'));
        $today = new \DateTime('today', new \DateTimeZone('UTC'));
        $age = $birthDate->diff($today)->y;
        return $age;
    } catch (\Exception $e) {
        error_log("Error calculating age for DOB {$dob}: " . $e->getMessage());
        return null;
    }
}

/**
 * Escapes HTML entities to prevent XSS attacks.
 * @param string|null $string The string to escape.
 * @return string The escaped string.
 */
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Retrieves old input data from the session for form re-population.
 * @param string $key The key of the input field.
 * @param string $default Default value if key not found.
 * @return string The old input value, escaped.
 */
/*function old($key, $default = '') {
    return isset($_SESSION['old_input'][$key]) ? esc($_SESSION['old_input'][$key]) : esc($default);
}*/


/**
 * Get old form input from the session.
 * Handles both string and array inputs (for multi-selects/checkboxes).
 *
 * @param string $key The name of the form field.
 * @param mixed $default A default value if nothing is found.
 * @return mixed The old input value or default.
 */
function old($key, $default = '')
{
    if (isset($_SESSION['old_input'][$key])) {
        // If the old input is an array, return it as is.
        // Otherwise, escape it for security.
        return is_array($_SESSION['old_input'][$key]) 
            ? $_SESSION['old_input'][$key] 
            : esc($_SESSION['old_input'][$key]);
    }
    return $default;
}



/**
 * Displays validation errors for a specific field or all errors.
 * @param string|null $field The specific field to display errors for. If null, displays all.
 */
function displayErrors($field = null) {
    if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])) {
        $errors = $_SESSION['errors'];
        if ($field && isset($errors[$field]) && is_array($errors[$field])) {
            echo '<div class="text-red-500 text-sm mt-1">';
            foreach ($errors[$field] as $error) {
                echo '<p>' . esc($error) . '</p>';
            }
            echo '</div>';
        } elseif (!$field && !empty($errors)) {
            // Display all errors if no specific field is requested
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
            echo '<strong class="font-bold">Validation Error!</strong>';
            echo '<span class="block sm:inline ml-2">';
            foreach ($errors as $fieldErrors) {
                if (is_array($fieldErrors)) {
                    foreach ($fieldErrors as $error) {
                        echo '<p>' . esc($error) . '</p>';
                    }
                }
            }
            echo '</span>';
            echo '</div>';
        }
    }
}

/**
 * Displays flash messages (success or error).
 */
function displayFlashMessages() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">';
        echo '<strong class="font-bold">Success!</strong>';
        echo '<span class="block sm:inline ml-2">' . esc($_SESSION['success_message']) . '</span>';
        echo '<span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display=\'none\';">';
        echo '<svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.697l-2.651 2.652a1.2 1.2 0 1 1-1.697-1.697L8.303 10 5.651 7.348a1.2 1.2 0 0 1 1.697-1.697L10 8.303l2.651-2.652a1.2 1.2 0 0 1 1.697 1.697L11.697 10l2.652 2.651a1.2 1.2 0 0 1 0 1.698z"/></svg>';
        echo '</span>';
        echo '</div>';
        unset($_SESSION['success_message']);
    }

    if (isset($_SESSION['error_message'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
        echo '<strong class="font-bold">Error!</strong>';
        echo '<span class="block sm:inline ml-2">' . esc($_SESSION['error_message']) . '</span>';
        echo '<span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display=\'none\';">';
        echo '<svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.697l-2.651 2.652a1.2 1.2 0 1 1-1.697-1.697L8.303 10 5.651 7.348a1.2 1.2 0 0 1 1.697-1.697L10 8.303l2.651-2.652a1.2 1.2 0 0 1 1.697 1.697L11.697 10l2.652 2.651a1.2 1.2 0 0 1 0 1.698z"/></svg>';
        echo '</span>';
        echo '</div>';
        unset($_SESSION['error_message']);
    }
}

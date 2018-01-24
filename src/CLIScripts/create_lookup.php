<?php

/**
 * Create all files needed to build lookup table and supporting framework elements.
 *
 * Usage:
 * cd /directory/path/to/this/script/
 * php create_lookup.php slug=dataset_types
 * php create_lookup.php slug=item_position_types
 * php create_lookup.php slug=focus_types
 * php create_lookup.php slug=light_source_types
 * php create_lookup.php slug=camera_cluster_types
 * php create_lookup.php slug=background_removal_methods
 * php create_lookup.php slug=subject_types
 * php create_lookup.php slug=data_rights_restriction_types
 * php create_lookup.php slug=scale_bar_barcode_types
 * php create_lookup.php slug=target_types
 * php create_lookup.php slug=units
 * php create_lookup.php slug=calibration_object_types
 * php create_lookup.php slug=status_types
 * php create_lookup.php slug=unit_stakeholder
 */

// Set the time limit to no limit.
set_time_limit(0);

#!/usr/bin/php
ini_set('display_errors', 1);
ini_set('track_errors', 1);
ini_set('html_errors', 1);
error_reporting(E_ALL);

// Establish paths
define('CURRENT_DIRECTORY', getcwd());
define('PATH_TO_TEMPLATES', CURRENT_DIRECTORY . '/lookup_templates/');
define('TARGET_PATH', '/Users/gor/Dropbox/Sites/quotient sites/dporepo/');

// Params
parse_str(implode('&', array_slice($argv, 1)), $_GET);
$slug = isset($_GET['slug']) ? $_GET['slug'] : FALSE;

if($slug) {

    // Create the different variations of the slug.
    $slug_parts = explode('_', $slug);
    foreach ($slug_parts as $skey => $svalue) {
        $slug_parts_new[] = ucfirst($svalue);
    }

    $slug_singular = substr($slug, 0, -1);
    $slug_camel_case = implode('', $slug_parts_new);
    $slug_words_plural = implode(' ', $slug_parts_new);
    $slug_words_singular = substr($slug_words_plural, 0, -1);

    // Template files array.
    $template_files = array(
        'src/AppBundle/Controller' => array(
            'CaptureMethodsController.php',
        ),
        'app/Resources/views/resources' => array(
            'browse_capture_methods.html.twig',
            'capture_methods_form.html.twig',
        ),
    );

    // Loop throught the template files array.
    foreach ($template_files as $key => $value) {
        foreach ($value as $filename) {

            // Class name uses camelcase for the file name.
            if($filename === 'CaptureMethodsController.php') {
                $new_filename = str_replace('CaptureMethods', $slug_camel_case, $filename);
            } else {
                $new_filename = str_replace('capture_methods', $slug, $filename);
            }

            if(!is_file(TARGET_PATH . $key . '/' . $new_filename)) {

                // Get the contents of the template file.
                $dir = ($key === 'src/AppBundle/Controller') ? 'controller' : 'views';

                // echo "\n";
                // echo "Path to template";
                // echo "\n";
                // var_dump(PATH_TO_TEMPLATES . $dir . '/' . $filename);
                // echo "\n\n";
                // echo "Target path";
                // echo "\n";
                // var_dump(TARGET_PATH . $key . '/' . $new_filename);
                // echo "\n";

                // Create a new file.
                $handle = fopen(TARGET_PATH . $key . '/' . $new_filename, 'w');

                $template_file_contents = file_get_contents(PATH_TO_TEMPLATES . $dir . '/' . $filename);
                
                // Replace all variations of the slug.
                $template_file_contents = str_replace('capture_methods', $slug, $template_file_contents);
                $template_file_contents = str_replace('capture_method', $slug_singular, $template_file_contents);
                $template_file_contents = str_replace('CaptureMethods', $slug_camel_case, $template_file_contents);
                $template_file_contents = str_replace('Capture Methods', $slug_words_plural, $template_file_contents);
                $template_file_contents = str_replace('capture methods', $slug_words_plural, $template_file_contents);
                $template_file_contents = str_replace('Capture Method', $slug_words_singular, $template_file_contents);
                $template_file_contents = str_replace('capture method', $slug_words_singular, $template_file_contents);

                // Write the new contents to the target file.
                fwrite($handle, $template_file_contents);
                fclose($handle);
            }

        }
    }

}
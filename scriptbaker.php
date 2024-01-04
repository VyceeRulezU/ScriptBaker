<?php
/*
Plugin Name: Export Posts to HTML
Description: A plugin to export WordPress posts as HTML files.
Version: 1.0
Author: Victor Ironali
*/



function add_export_link() {
    add_submenu_page(
        'tools.php',
        'Export Posts to HTML',
        'Export Posts to HTML',
        'manage_options',
        'export-posts-html',
        'export_posts_html_page'
    );
}
add_action('admin_menu', 'add_export_link');


function export_posts_html_page() {
    ?>
    <div class="wrap">
        <h1>Export Posts to HTML</h1>
        
    </div>
    <?php
}


function add_export_link_to_posts($actions, $post) {
    $actions['export_html'] = '<a href="' . admin_url('admin.php?action=export_html&post_id=' . $post->ID) . '">Export as HTML</a>';
    return $actions;
}
add_filter('post_row_actions', 'add_export_link_to_posts', 10, 2);


function export_single_post_html() {
    
    if (isset($_GET['action']) && $_GET['action'] === 'export_html' && current_user_can('edit_posts')) {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

        
        $post_content = get_post_field('post_content', $post_id);
        $html_template = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>%s</title></head><body>%s</body></html>';
        $html_content = sprintf($html_template, get_the_title($post_id), $post_content);

       
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . sanitize_title_with_dashes(get_the_title($post_id)) . '.html"');
        echo $html_content;
        exit;
    }
}
add_action('admin_init', 'export_single_post_html');


function add_bulk_action_export_html($actions) {
    $actions['export_html_bulk'] = 'Export as HTML';
    return $actions;
}
add_filter('bulk_actions-edit-post', 'add_bulk_action_export_html');


function export_bulk_posts_html() {
   
    if (isset($_GET['action']) && $_GET['action'] === 'export_html_bulk' && current_user_can('edit_posts')) {
        $post_ids = isset($_GET['post']) ? $_GET['post'] : array();

        
        $zip = new ZipArchive();
        $zip_filename = wp_tempnam('bulk_export_html', '.zip');
        $zip->open($zip_filename, ZipArchive::CREATE);

        foreach ($post_ids as $post_id) {
            $post_content = get_post_field('post_content', $post_id);
            $html_template = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>%s</title></head><body>%s</body></html>';
            $html_content = sprintf($html_template, get_the_title($post_id), $post_content);

            
            $zip->addFromString(sanitize_title_with_dashes(get_the_title($post_id)) . '.html', $html_content);
        }

        $zip->close();

        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="bulk_export_html.zip"');
        readfile($zip_filename);
        unlink($zip_filename);
        exit;
    }
}
add_action('admin_init', 'export_bulk_posts_html');
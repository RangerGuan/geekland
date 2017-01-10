<?php
if (!function_exists("get_option") || !function_exists("add_filter")) {
    die();
}
if (isset($_POST["update_feed_settings"]) || isset($_POST["check_for_updates"]) || isset($_POST["delete_feeds"]) || isset($_POST["delete_posts"]) || isset($_POST["feed_ids"]) || isset($_POST["syndicate_feed"]) || isset($_POST["update_default_settings"]) || isset($_POST["alter_default_settings"])) {

    if (!isset($_POST['csyn_token']) || ($_POST['csyn_token'] != get_option('CSYN_TOKEN'))) {
        die();
    }
}
update_option('CSYN_TOKEN', rand());
?>
<style type="text/css">
    div.cybersyn-ui-tabs-panel {
        margin: 0 5px 0 0px;
        padding: .5em .9em;
        height: 11em;
        width: 675px;
        overflow: auto;
        border: 1px solid #dfdfdf;
    }

    .error a {
        color: #100;
    }
</style>

<script type="text/javascript">
    function checkAll(form) {
        for (i = 0, n = form.elements.length; i < n; i++) {
            if (form.elements[i].type == "checkbox" && !(form.elements[i].getAttribute('onclick', 2))) {
                if (form.elements[i].checked == true)
                    form.elements[i].checked = false;
                else
                    form.elements[i].checked = true;
            }
        }
    }
</script>

<div class="wrap">
    <?php
    if (isset($_POST["alter_default_settings"])) {
        echo('<h2>RSS/Atom Syndicator - Default Settings</h2>');
    } else {
        echo('<h2>RSS/Atom Syndicator</h2>');
    }
    if (isset($_GET["edit-feed-id"])) {
        $csyn_syndicator->feedPreview($csyn_syndicator->fixURL($csyn_syndicator->feeds[(int) $_GET["edit-feed-id"]]['url']), true);
        $csyn_syndicator->showSettings(true, $csyn_syndicator->feeds[(int) $_GET["edit-feed-id"]]['options']);
    } elseif (isset($_POST["update_feed_settings"])) {
        $date_min = (int) $_POST['date_min'];
        $date_max = (int) $_POST['date_max'];
        if ($date_min > $date_max) {
            $date_min = $date_max;
        }
        if (mb_strlen(trim(stripslashes(htmlspecialchars($_POST['feed_title'], ENT_NOQUOTES)))) == 0) {
            $_POST['feed_title'] = "no name";
        }
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['title'] = trim(stripslashes(htmlspecialchars($_POST['feed_title'], ENT_NOQUOTES)));

        $new_url = trim($_POST["new_feed_url"]);
        $old_url = $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['url'];
        if (stripos($new_url, 'http') === 0 && $new_url != $old_url) {
            $query = "UPDATE $wpdb->postmeta SET meta_value = '$new_url' WHERE meta_key = 'cyberseo_rss_source' AND meta_value = '$old_url'";
            $wpdb->get_results($query);
            $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['url'] = $new_url;
        }

        if ((int) $_POST['update_interval'] == 0) {
            $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['interval'] = 0;
        } else {
            $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['interval'] = abs((int) $_POST['update_interval']);
        }
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['post_status'] = $_POST['post_status'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['comment_status'] = $_POST['post_comments'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['ping_status'] = $_POST['post_pings'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['post_author'] = intval($_POST['post_author']);
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['base_date'] = $_POST['post_publish_date'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['max_items'] = abs(intval($_POST['max_items']));
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['post_category'] = @$_POST['post_category'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['duplicate_check_method'] = $_POST['duplicate_check_method'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['undefined_category'] = $_POST['undefined_category'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['date_min'] = $date_min;
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['date_max'] = $date_max;
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['insert_media_attachments'] = $_POST['insert_media_attachments'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['set_thumbnail'] = $_POST['set_thumbnail'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['convert_encoding'] = @$_POST['convert_encoding'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['store_images'] = @$_POST['store_images'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['include_post_footers'] = @$_POST['include_post_footers'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['embed_videos'] = @$_POST['embed_videos'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['translator'] = @$_POST['translator'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['yandex_translation_dir'] = @$_POST['yandex_translation_dir'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['yandex_api_key'] = @$_POST['yandex_api_key'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['google_translation_source'] = @$_POST['google_translation_source'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['google_translation_target'] = @$_POST['google_translation_target'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['google_api_key'] = @$_POST['google_api_key'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['create_tags'] = @$_POST['create_tags'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['extract_full_articles'] = @$_POST['extract_full_articles'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['post_tags'] = $_POST['post_tags'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['post_footer'] = $_POST['post_footer'];
        $csyn_syndicator->feeds[(int) $_POST["feed_id"]]['options']['shorten_excerpts'] = $_POST['shorten_excerpts'];
        csyn_set_option(CSYN_SYNDICATED_FEEDS, $csyn_syndicator->feeds, '', 'yes');
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["check_for_updates"])) {
        $csyn_syndicator->show_report = true;
        $csyn_syndicator->syndicateFeeds(@$_POST["feed_ids"], false);
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["delete_feeds"]) && isset($_POST["feed_ids"])) {
        $csyn_syndicator->deleteFeeds($_POST["feed_ids"], false, true);
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["delete_posts"]) && isset($_POST["feed_ids"])) {
        $csyn_syndicator->deleteFeeds($_POST["feed_ids"], true, false);
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["delete_feeds_and_posts"]) && isset($_POST["feed_ids"])) {
        $csyn_syndicator->deleteFeeds($_POST["feed_ids"], true, true);
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["new_feed"]) && strlen($_POST["feed_url"]) > 0) {
        if ($csyn_syndicator->feedPreview($csyn_syndicator->fixURL($_POST["feed_url"]), false)) {
            $options = $csyn_syndicator->global_options;
            $options['undefined_category'] = 'use_global';
            $csyn_syndicator->showSettings(true, $options);
        } else {
            $csyn_syndicator->showMainPage(false);
        }
    } elseif (isset($_POST["syndicate_feed"])) {
        $date_min = (int) $_POST['date_min'];
        $date_max = (int) $_POST['date_max'];
        if ($date_min > $date_max) {
            $date_min = $date_max;
        }
        if (mb_strlen(trim(stripslashes(htmlspecialchars($_POST['feed_title'], ENT_NOQUOTES)))) == 0) {
            $_POST['feed_title'] = "no name";
        }

        if ((int) $_POST['update_interval'] == 0) {
            $update_interval = 0;
        } else {
            $update_interval = abs((int) $_POST['update_interval']);
        }
        $feed = array();
        $feed['title'] = trim(stripslashes(htmlspecialchars($_POST['feed_title'], ENT_NOQUOTES)));
        $feed['url'] = $_POST['feed_url'];
        $feed['updated'] = 0;
        $feed['options']['interval'] = $update_interval;
        $feed['options']['post_category'] = @$_POST['post_category'];
        $feed['options']['post_status'] = $_POST['post_status'];
        $feed['options']['comment_status'] = $_POST['post_comments'];
        $feed['options']['ping_status'] = $_POST['post_pings'];
        $feed['options']['post_author'] = intval($_POST['post_author']);
        $feed['options']['base_date'] = $_POST['post_publish_date'];
        $feed['options']['duplicate_check_method'] = $_POST['duplicate_check_method'];
        $feed['options']['undefined_category'] = $_POST['undefined_category'];
        $feed['options']['post_tags'] = $_POST['post_tags'];
        $feed['options']['date_min'] = $date_min;
        $feed['options']['date_max'] = $date_max;
        $feed['options']['insert_media_attachments'] = $_POST['insert_media_attachments'];
        $feed['options']['set_thumbnail'] = $_POST['set_thumbnail'];
        $feed['options']['convert_encoding'] = @$_POST['convert_encoding'];
        $feed['options']['store_images'] = @$_POST['store_images'];
        $feed['options']['include_post_footers'] = @$_POST['include_post_footers'];
        $feed['options']['embed_videos'] = @$_POST['embed_videos'];
        $feed['options']['translator'] = @$_POST['translator'];
        $feed['options']['yandex_translation_dir'] = @$_POST['yandex_translation_dir'];
        $feed['options']['yandex_api_key'] = @$_POST['yandex_api_key'];
        $feed['options']['google_translation_source'] = @$_POST['google_translation_source'];
        $feed['options']['google_translation_target'] = @$_POST['google_translation_target'];
        $feed['options']['google_api_key'] = @$_POST['google_api_key'];
        $feed['options']['create_tags'] = @$_POST['create_tags'];
        $feed['options']['extract_full_articles'] = @$_POST['extract_full_articles'];
        $feed['options']['max_items'] = abs((int) $_POST['max_items']);
        $feed['options']['post_footer'] = $_POST['post_footer'];
        $feed['options']['shorten_excerpts'] = $_POST['shorten_excerpts'];
        $feed['options']['last_archive_page'] = 2;
        $id = array_push($csyn_syndicator->feeds, $feed);
        if ((intval($update_interval)) != 0) {
            $csyn_syndicator->show_report = false;
            $csyn_syndicator->syndicateFeeds(array($id), false);
        }
        sort($csyn_syndicator->feeds);
        csyn_set_option(CSYN_SYNDICATED_FEEDS, $csyn_syndicator->feeds, '', 'yes');
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["update_default_settings"])) {
        $date_min = (int) $_POST['date_min'];
        $date_max = (int) $_POST['date_max'];
        if ($date_min > $date_max) {
            $date_min = $date_max;
        }
        $csyn_syndicator->global_options['interval'] = abs((int) $_POST['update_interval']);
        $csyn_syndicator->global_options['post_status'] = $_POST['post_status'];
        $csyn_syndicator->global_options['comment_status'] = $_POST['post_comments'];
        $csyn_syndicator->global_options['ping_status'] = $_POST['post_pings'];
        $csyn_syndicator->global_options['post_author'] = intval($_POST['post_author']);
        $csyn_syndicator->global_options['base_date'] = $_POST['post_publish_date'];
        $csyn_syndicator->global_options['max_items'] = abs((int) $_POST['max_items']);
        $csyn_syndicator->global_options['post_category'] = @$_POST['post_category'];
        $csyn_syndicator->global_options['duplicate_check_method'] = $_POST['duplicate_check_method'];
        $csyn_syndicator->global_options['undefined_category'] = $_POST['undefined_category'];
        $csyn_syndicator->global_options['date_min'] = $date_min;
        $csyn_syndicator->global_options['date_max'] = $date_max;
        $csyn_syndicator->global_options['insert_media_attachments'] = $_POST['insert_media_attachments'];
        $csyn_syndicator->global_options['set_thumbnail'] = $_POST['set_thumbnail'];
        $csyn_syndicator->global_options['convert_encoding'] = @$_POST['convert_encoding'];
        $csyn_syndicator->global_options['store_images'] = @$_POST['store_images'];
        $csyn_syndicator->global_options['include_post_footers'] = @$_POST['include_post_footers'];
        $csyn_syndicator->global_options['embed_videos'] = @$_POST['embed_videos'];
        $csyn_syndicator->global_options['translator'] = @$_POST['translator'];
        $csyn_syndicator->global_options['yandex_translation_dir'] = @$_POST['yandex_translation_dir'];
        $csyn_syndicator->global_options['yandex_api_key'] = @$_POST['yandex_api_key'];
        $csyn_syndicator->global_options['google_translation_source'] = @$_POST['google_translation_source'];
        $csyn_syndicator->global_options['google_translation_target'] = @$_POST['google_translation_target'];
        $csyn_syndicator->global_options['google_api_key'] = @$_POST['google_api_key'];
        $csyn_syndicator->global_options['create_tags'] = @$_POST['create_tags'];
        $csyn_syndicator->global_options['extract_full_articles'] = @$_POST['extract_full_articles'];
        $csyn_syndicator->global_options['post_tags'] = $_POST['post_tags'];
        $csyn_syndicator->global_options['post_footer'] = $_POST['post_footer'];
        $csyn_syndicator->global_options['shorten_excerpts'] = $_POST['shorten_excerpts'];
        csyn_set_option(CSYN_FEED_OPTIONS, $csyn_syndicator->global_options, '', 'yes');
        $csyn_syndicator->showMainPage(false);
    } elseif (isset($_POST["alter_default_settings"])) {
        $csyn_syndicator->showSettings(false, $csyn_syndicator->global_options);
    } else {
        $csyn_syndicator->showMainPage(false);
    }
    ?>
</div>

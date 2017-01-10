<?php
if (!function_exists("get_option") || !function_exists("add_filter")) {
    die();
}
?>
<script type='text/javascript'>
    function changeMode() {
        var mode = document.general_settings.<?php echo CSYN_RSS_PULL_MODE; ?>.value;
        var auto = document.getElementById("auto");
        var cron = document.getElementById("cron");
        if (mode == "auto") {
            auto.style.display = 'block';
            cron.style.display = 'none';
        } else {
            auto.style.display = 'none';
            cron.style.display = 'block';
        }
    }
</script>
<?php
// if form submitted
if (isset($_POST['Submit'])) {

    $pseudo_cron_interval = intval(@$_POST[CSYN_PSEUDO_CRON_INTERVAL]);
    if ($pseudo_cron_interval < 1) {
        $pseudo_cron_interval = 1;
    }

    $update_csyn_text = array();
    $update_csyn_queries[] = update_option(CSYN_RSS_PULL_MODE, $_POST[CSYN_RSS_PULL_MODE]);
    $update_csyn_queries[] = update_option(CSYN_PSEUDO_CRON_INTERVAL, $pseudo_cron_interval);
    $update_csyn_queries[] = update_option(CSYN_DISABLE_DUPLICATION_CONTROL, @$_POST[CSYN_DISABLE_DUPLICATION_CONTROL]);
    $update_csyn_queries[] = update_option(CSYN_LINK_TO_SOURCE, @$_POST[CSYN_FULL_TEXT_EXTRACTOR]);
    $update_csyn_queries[] = update_option(CSYN_LINK_TO_SOURCE, @$_POST[CSYN_LINK_TO_SOURCE]);
    $update_csyn_text[] = __('RSS pull mode');
    $update_csyn_text[] = __('Pseudo cron interval');
    $update_csyn_text[] = __('Feed duplication control');
    $update_csyn_text[] = __('Full article extrator path');
    $update_csyn_text[] = __('Link to source');
    $i = 0;
    $text = '';
    foreach ($update_csyn_queries as $update_csyn_query) {
        if ($update_csyn_query) {
            $text .= $update_csyn_text[$i] . ' ' . __('Updated') . '<br />';
            if ($update_csyn_text[$i] == 'RSS pull mode' || $update_csyn_text[$i] == 'Pseudo cron interval') {
                wp_clear_scheduled_hook('update_by_wp_cron');
            }
        }
        $i++;
    }
    if (empty($text)) {
        $text = __('No Option Updated');
    }
}

if (!empty($text)) {
    echo '<div id="message" class="updated fade"><p>' . $text . '</p></div>';
}
?>
<div class="wrap">

    <?php
    $problems = "";
    $upload_path = wp_upload_dir();
    if (!is_writable($upload_path['path'])) {
        $problems .= "Your " . $upload_path['path'] . " folder is not writable. You must chmod it to 777 if you want to use the \"Store Images Locally\" option.\n<br />";
    }
    if (!function_exists('mb_convert_case')) {
        $problems .= "The required <a href=\"http://php.net/manual/en/book.mbstring.php\" target=\"_blank\">mbstring</a> PHP extension is not installed. You must install it in order to make CyberSyn work properly.\n<br />";
    }
    if (!function_exists('curl_init') && ini_get('safe_mode')) {
        $problems .= "PHP variable <a href=\"http://php.net/manual/en/features.safe-mode.php\" target=\"_blank\">safe_mode</a> is enabled. You must disable it in order to make CyberSyn work properly.\n<br />";
    }
    if (!function_exists('curl_init') && !ini_get('allow_url_fopen')) {
        $problems .= "PHP variable <a href=\"http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen\" target=\"_blank\">allow_url_fopen</a> is disabled. You must enable it in order to make CyberSyn work properly.\n<br />";
    }
    if ($problems != "") {
        echo "<div id=\"message\" class=\"error\"><p>$problems</p></div>\n";
    }
    ?>

    <h2>General Settings</h2>

    <form method="post" action="<?php echo csyn_REQUEST_URI(); ?>" name="general_settings">

        <table class="form-table">
            <tr valign="top">
                <th align="left">RSS pull mode</th>
                <td align="left">
                    <select style="width: 160px;" name="<?php echo CSYN_RSS_PULL_MODE; ?>" onchange="changeMode();"<?php
                    if (defined('CSYN_ENABLE_RSS_PULL_MODE') && !CSYN_ENABLE_RSS_PULL_MODE) {
                        echo "disabled";
                    }
                    ?> size="1"><?php
                                echo '<option ' . ((get_option(CSYN_RSS_PULL_MODE) == "auto") ? 'selected ' : '') . 'value="auto">auto</option>';
                                echo '<option ' . ((get_option(CSYN_RSS_PULL_MODE) == "cron") ? 'selected ' : '') . 'value="cron">by cron job or manually</option>';
                                ?></select> - set the RSS pulling mode. If you have no access to a crontab, or not sure on how to set a cron job, set the RSS Pull Mode to "auto".
                    <br />

                    <p id="auto">
                        In this mode, the CyberSyn plugin uses WordPress pseudo cron, which will be executed by the WordPress every <input type="text" name="<?php echo CSYN_PSEUDO_CRON_INTERVAL; ?>" size="1" value="<?php echo get_option(CSYN_PSEUDO_CRON_INTERVAL); ?>"> minutes.
                        <br />
                        The pseudo cron will trigger when someone visits your WordPress site, if the scheduled time has passed.
                    </p>

                    <p id="cron">
                        In this mode, you need to manually configure <strong><a href="http://en.wikipedia.org/wiki/Cron" target="_blank">cron</a></strong> at your host. For example, if you want run a cron job once a hour, just add the following line into your crontab:
                        <strong><?php echo "0 * * * * /usr/bin/curl --silent " . get_option('siteurl') . "/?pull-feeds=" . get_option(CSYN_CRON_MAGIC); ?></strong>
                    </p>

                    <?php
                    if (defined('CSYN_ENABLE_RSS_PULL_MODE') && !CSYN_ENABLE_RSS_PULL_MODE) {
                        echo "<strong>This option is blocked by Administrator.</strong>";
                    }
                    ?>
                </td>
            </tr>

            <tr valign="top">
                <th align="left">Link to source</th>
                <td align="left"><input type="checkbox" name="<?php echo CSYN_LINK_TO_SOURCE; ?>"
                    <?php
                    if (get_option(CSYN_LINK_TO_SOURCE) == "on") {
                        echo "checked";
                    }
                    ?> /> - when enabled the post titles will be linked to their source pages.</td>
            </tr>

            <tr valign="top">
                <th scope="row">Full text extractor URL</th>
                <td>
                    <input type="text" name="<?php echo CSYN_FULL_TEXT_EXTRACTOR; ?>" size="80" value="<?php echo get_option(CSYN_FULL_TEXT_EXTRACTOR); ?>">
                    <p class="description">URL of the full text extracting script.</p>
                </td>
            </tr>

            <tr valign="top">
                <th align="left">Disable feed duplication control</th>
                <td align="left"><input type="checkbox" name="<?php echo CSYN_DISABLE_DUPLICATION_CONTROL; ?>"
                    <?php
                    if (get_option(CSYN_DISABLE_DUPLICATION_CONTROL) == "on") {
                        echo "checked";
                    }
                    ?> />
                    - allows the CyberSyn plugin to syndicate a same feed more than once.
                </td>
            </tr>

        </table>

        <br />
        <div align="center">
            <input type="submit" name="Submit" class="button-primary"
                   value="Update Options" />&nbsp;&nbsp;<input type="button"
                   name="cancel" value="Cancel" class="button"
                   onclick="javascript:history.go(-1)" />
        </div>
    </form>
</div>

<script type='text/javascript'>
    changeMode();
</script>

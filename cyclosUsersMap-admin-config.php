<?php
/*
 * Copyright 2015 Cyclos (www.cyclos.org)
 *
 * This plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * Be aware the plugin is publish under GPLv2 Software, but Cyclos 4 PRO is not,
 * you need to buy an appropriate license if you want to use Cyclos 4 PRO, see:
 * www.cyclos.org.
 */

// Block people to access the script directly (against malicious attempts)
defined('ABSPATH') or die('No script kiddies please!');

/* #################### Creates admin settings page #################### */
add_action('admin_menu', 'cyclosUsersMapAdminMenu');
function cyclosUsersMapAdminMenu() {
    add_options_page('Cyclos Users Map Settings', 'Cyclos Users Map', 'manage_options', 'CyclosUsersMapSettings', 'cyclosUsersMapAdminSettingsPage');
}
function cyclosUsersMapAdminSettingsPage() {
    if (current_user_can("manage_options")) {
        $username = esc_attr(get_option('cyclosUsersMap_adminuser'));
        $accessClientToken = esc_attr(get_option('cyclosUsersMap_token'));
        $password = esc_attr(get_option('cyclosUsersMap_adminPassword'));

        if (isset($_POST['edit_cyclosUsersMap_settings'])) {
            check_admin_referer('edit_cyclosUsersMap_settings', 'edit_cyclosUsersMap_settings');
            // If the settings are saved.
            cyclosUsersMapSaveSettings();
        } elseif (empty($accessClientToken) and empty($username) and empty($password)) {
            // If no token or user is found show the configuration page.
            cyclosUsersMapConfigureSettings();
        } elseif (isset($_POST['goto_cyclosUsersMap_settings'])) {
            check_admin_referer('goto_cyclosUsersMap_settings', 'goto_cyclosUsersMap_settings');
            // Edit button is pressed show the configuration page.
            cyclosUsersMapConfigureSettings();
        } else {
            // Show normal page
            cyclosUsersMapNormalPage();
        }
    }
}

/* #################### Shows the normal admin settings page #################### */
function cyclosUsersMapNormalPage() {
?>
<div class="wrap">
    <h2>Cyclos Users Map Wordpress Plugin</h2>
    <p>
        You can show the directory map in any page, by inserting the code: <b><code>&#91;cyclos_users_map&#93;</code></b>
    </p>
    <form name="cyclos_edit" method="post" action="#">
        <?php
    wp_nonce_field('goto_cyclosUsersMap_settings', 'goto_cyclosUsersMap_settings');
?>
       <input type="submit" name="Submit" value="Edit Cyclos plugin settings" />
    </form>
</div>
<?php
}

/* #################### Shows the edit admin setting page #################### */
function cyclosUsersMapConfigureSettings() {
    $url = esc_url(get_option('cyclosUsersMap_url'));
    $adminuser = esc_attr(get_option('cyclosUsersMap_adminuser'));
    $token = esc_attr(get_option('cyclosUsersMap_token'));
    $groupId = esc_attr(get_option('cyclosUsersMap_groupId'));
    $websiteName = esc_attr(get_option('cyclosUsersMap_websiteName'));
    $descriptionName = esc_attr(get_option('cyclosUsersMap_descriptionName'));
    $mapWidth = esc_attr(get_option('cyclosUsersMap_width'));
    $mapHeight = esc_attr(get_option('cyclosUsersMap_height'));
    $mapLat = esc_attr(get_option('cyclosUsersMap_homeLat'));
    $mapLon = esc_attr(get_option('cyclosUsersMap_homeLon'));
    $mapZoom = esc_attr(get_option('cyclosUsersMap_homeZoom'));
    $cyclosUsersMap_references = esc_html(get_option('cyclosUsersMap_references'));
?>
<div class="wrap">
    <form name="cyclos_form" method="post" action="#">
        <?php
    wp_nonce_field('edit_cyclosUsersMap_settings', 'edit_cyclosUsersMap_settings');
?>
       <h2>Cyclos Users Map Plugin Settings</h2>
        <p>
            Fill this settings to configure the plugin.<br /> You can access your
            Cyclos instance by a token or with your user and password, if you
            fill both options, it is used the token.
        </p>
        <?php
    settings_errors();
?>
       <hr>
        <table>
            <tbody style="vertical-align: top">
                <tr>
                    <td>Cyclos URL:</td>
                    <td><input type="url" name="cyclosUsersMap_url" value="<?= $url ?>"
                        size="50" required></td>
                    <td><i>e.g. https://demo.cyclos.org</i></td>
                </tr>
                <tr>
                    <td>Cyclos username:</td>
                    <td><input type="text" name="cyclosUsersMap_adminuser"
                        value="<?= $adminuser ?>" size="50"></td>
                    <td><i>e.g. demo</i></td>
                </tr>
                <tr>
                    <td>Cyclos password:</td>
                    <td><input type="password" name="cyclosUsersMap_adminpwd" value=""
                        size="50"></td>
                    <td><i>e.g. 1234.</i><br /> <i>Let it empty if you don't want to
                            override the existing password.</i></td>
                </tr>
                <tr>
                    <td>Access client token:</td>
                    <td><input type="text" name="cyclosUsersMap_accessClientToken"
                        value="<?= $token ?>" size="50"></td>
                    <td><i>A token obtained by your Cyclos instance.</i></td>
                </tr>
                <tr>
                    <td>Group id:</td>
                    <td><input type="text" name="cyclosUsersMap_groupId"
                        value="<?= $groupId ?>" size="50"></td>
                    <td><i>e.g. members</i><br /> <i>(*)The group of the users you want
                            show in the map.</i></td>
                </tr>
                <tr>
                    <td>Website internal name:</td>
                    <td><input type="text" name="cyclosUsersMap_websiteName"
                        value="<?= $websiteName ?>" size="50"></td>
                    <td><i>Default: website</i><br /> <i>(*)The internal name of the website profile field
                       in your Cyclos instance.</i></td>
                </tr>
                <tr>
                    <td>Description internal name:</td>
                    <td><input type="text" name="cyclosUsersMap_descriptionName"
                        value="<?= $descriptionName ?>" size="50"></td>
                    <td><i>Default: description</i><br /> <i>(*)The internal name of the description profile field
                       in your Cyclos instance.</i></td>
                </tr>
                <tr>
                    <td>Map width:</td>
                    <td><input type="text" name="cyclosUsersMap_width"
                        value="<?= $mapWidth ?>" size="50"></td>
                    <td><i>e.g. 100%</i></td>
                </tr>
                <tr>
                    <td>Map height:</td>
                    <td><input type="text" name="cyclosUsersMap_height"
                        value="<?= $mapHeight ?>" size="50" required></td>
                    <td><i>e.g. 600px</i></td>
                </tr>
                <tr>
                    <td>Home latitude:</td>
                    <td><input type="number" step="0.000001" name="cyclosUsersMap_homeLat"
                        value="<?= $mapLat ?>" size="50" required></td>
                    <td><i>e.g. -34.905194</i></td>
                </tr>
                <tr>
                    <td>Home longitude:</td>
                    <td><input type="number" step="0.000001" name="cyclosUsersMap_homeLon"
                        value="<?= $mapLon ?>" size="50" required></td>
                    <td><i>e.g. -56.195150</i></td>
                </tr>
                <tr>
                    <td>Home zoom:</td>
                    <td><input type="number" name="cyclosUsersMap_homeZoom"
                        value="<?= $mapZoom ?>" size="50" required></td>
                    <td><i>e.g. 13</i></td>
                </tr>

                <tr>
                    <td>References(optional):</td>
                    <td><textarea style="width: 100%" name="cyclosUsersMap_references"><?= $cyclosUsersMap_references ?></textarea></td>
                    <td><i>e.g: </i><input readonly
                        style="background-color: transparent; color: #444; font-style: italic; font-size: 13px; -webkit-appearance: button-bevel;"
                        size="30" value='<a  href="http://www.cyclos.org">Cyclos</a>'></input><br />
                        <i>Define references by html to be shown on the right corner of
                            the map.</i></td>
                </tr>
            </tbody>
        </table>
        <hr />
        <div>
            <input type="submit" name="Submit" value="Save settings" />
        </div>
    </form>
    <p>
        (*) If you don't know the internal name of that fields, contact an administrator of your Cyclos instance.
    </p>
</div>
<?php
}

/* #################### Saves admin settings page #################### */
function cyclosUsersMapSaveSettings() {
    // first retreive the posted data
    $url = esc_url($_POST['cyclosUsersMap_url']);
    if (empty($url)) {
        add_settings_error('cyclosUsersMapAdminMenu', esc_attr('settings_updated'), "ERROR: Cyclos URL was not valid.");
        cyclosUsersMapConfigureSettings();
    } else {
        $adminuser = sanitize_user($_POST['cyclosUsersMap_adminuser']);
        // Don't sanitize the password, as it can contain any characters and is sent directly via WS
        $adminpwd = $_POST['cyclosUsersMap_adminpwd'];
        $token = sanitize_text_field($_POST['cyclosUsersMap_accessClientToken']);
        if (empty($token) && empty($adminuser)) {
            add_settings_error('cyclosUsersMapAdminMenu', esc_attr('settings_updated'), "ERROR: You must fill one of this two fields: Cyclos username or Access client token.");
            cyclosUsersMapConfigureSettings();
        }else{
            $groupId = sanitize_text_field($_POST['cyclosUsersMap_groupId']);
            $websiteName = sanitize_text_field($_POST['cyclosUsersMap_websiteName']);
            $descriptionName = sanitize_text_field($_POST['cyclosUsersMap_descriptionName']);
            $mapWidth = sanitize_text_field($_POST['cyclosUsersMap_width']);
            $mapHeight = sanitize_text_field($_POST['cyclosUsersMap_height']);
            $mapLat = is_numeric($_POST['cyclosUsersMap_homeLat']) ? $_POST['cyclosUsersMap_homeLat'] : 0;
            $mapLon = is_numeric($_POST['cyclosUsersMap_homeLon']) ? $_POST['cyclosUsersMap_homeLon'] : 0;
            $mapZoom = ctype_digit($_POST['cyclosUsersMap_homeZoom']) ? $_POST['cyclosUsersMap_homeZoom'] : 13;
            $references = wp_kses_post($_POST['cyclosUsersMap_references']);
            update_option('cyclosUsersMap_url', $url);
            update_option('cyclosUsersMap_adminuser', $adminuser);
            update_option('cyclosUsersMap_token', $token);
            if (!empty($adminpwd)) {
                update_option('cyclosUsersMap_adminPassword', cyclosUsersMap_encrypt_password($adminpwd));
            }
            update_option('cyclosUsersMap_groupId', $groupId);
            update_option('cyclosUsersMap_websiteName', empty($websiteName) ? "website" : $websiteName );
            update_option('cyclosUsersMap_descriptionName', empty($descriptionName) ? "description" : $descriptionName );
            update_option('cyclosUsersMap_width', $mapWidth);
            update_option('cyclosUsersMap_height', $mapHeight);
            update_option('cyclosUsersMap_homeLat', $mapLat);
            update_option('cyclosUsersMap_homeLon', $mapLon);
            update_option('cyclosUsersMap_homeZoom', ($mapZoom));
            update_option('cyclosUsersMap_references', $references);

            cyclosUsersMapNormalPage();
      }
    }
}
function cyclosUsersMap_encrypt_password($password) {
    $key = "/r`v&cZkW6LEozST'%p8rr>nAb}n.p";
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $cipherpassword_raw = openssl_encrypt($password, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $cipherpassword_raw, $key, $as_binary = true);
    return base64_encode($iv . $hmac . $cipherpassword_raw);
}

?>

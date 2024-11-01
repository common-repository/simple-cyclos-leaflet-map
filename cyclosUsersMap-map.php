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
function cyclosUsersMap_init() {
    wp_enqueue_script('jquery');

    wp_register_script('leaflet-script', plugin_dir_url(__FILE__) . 'js/leaflet.js');
    wp_enqueue_script('leaflet-script');

    wp_register_style('leaflet-style', plugin_dir_url(__FILE__) . 'js/leaflet.css');
    wp_enqueue_style('leaflet-style');

    wp_register_style('leaflet-easybutton-style', plugin_dir_url(__FILE__) . 'js/easy-button.css');
    wp_enqueue_style('leaflet-easybutton-style');

    wp_register_style('leaflet-marker-cluster', plugin_dir_url(__FILE__) . 'js/MarkerCluster.css');
    wp_enqueue_style('leaflet-marker-cluster');

    wp_register_style('leaflet-marker-cluster-default', plugin_dir_url(__FILE__) . 'js/MarkerCluster.Default.css');
    wp_enqueue_style('leaflet-marker-cluster-default');

    wp_register_script('leaflet-easybutton-script', plugin_dir_url(__FILE__) . 'js/easy-button.js');
    wp_enqueue_script('leaflet-easybutton-script');

    wp_register_script('leaflet-marketcluster-script', plugin_dir_url(__FILE__) . 'js/leaflet.markercluster-src.js');
    wp_enqueue_script('leaflet-marketcluster-script');

    wp_register_script('mapbox-fullscreen-script', plugin_dir_url(__FILE__) . 'js/Leaflet.fullscreen.min.js');
    wp_enqueue_script('mapbox-fullscreen-script');

    wp_register_style('mapbox-fullscreen-style', plugin_dir_url(__FILE__) . 'js/leaflet.fullscreen.css');
    wp_enqueue_style('mapbox-fullscreen-style');

    wp_register_style('leaflet-search-style', plugin_dir_url(__FILE__) . 'js/leaflet-search.min.css');
    wp_enqueue_style('leaflet-search-style');

    wp_register_script('leaflet-search-script', plugin_dir_url(__FILE__) . 'js/leaflet-search.min.js');
    wp_enqueue_script('leaflet-search-script');

    wp_register_style('cyclos-map-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_style('cyclos-map-style');
}

add_action('init', 'cyclosUsersMap_init'); // Register stylesheets and javascript
add_shortcode('cyclos_users_map', 'cyclosUsersMap_shortcode'); // Create shortcode
function cyclosUsersMap_shortcode() {
    $username = esc_attr(get_option('cyclosUsersMap_adminuser'));
    $accessClientToken = esc_attr(get_option('cyclosUsersMap_token'));
    $password = esc_attr(get_option('cyclosUsersMap_adminPassword'));
    $websiteName = esc_attr(get_option('cyclosUsersMap_websiteName'));
    $descriptionName = esc_attr(get_option('cyclosUsersMap_descriptionName'));
    $mapWidth = esc_attr(get_option('cyclosUsersMap_width'));
    $mapHeight = esc_attr(get_option('cyclosUsersMap_height'));
    $home_lat = esc_attr(get_option('cyclosUsersMap_homeLat'));
    $home_lon = esc_attr(get_option('cyclosUsersMap_homeLon'));
    $home_zoom = esc_attr(get_option('cyclosUsersMap_homeZoom'));
    $cyclosUsersMap_references = get_option('cyclosUsersMap_references');
    $references = (str_replace("\"", "'", wp_kses_post($cyclosUsersMap_references)));

    if (empty($accessClientToken) and empty($username) and empty($password)) {
        return "Cyclos map warning: user not found, please contact the administrator.";
    } else if (empty($websiteName) or empty($descriptionName) or empty($mapHeight) or empty($home_lat) or empty($home_lon) or empty($home_zoom)) {
        return "Cyclos map warning: some needed options are empty, please contact the administrator.";
    } else {
        $dataFromCyclos = run_cyclosUsersMap_api();
        if (isset($dataFromCyclos)) {
            $content = '
    <div class="custom-popup" id="leaflet_cyclosUsersMap" style="height:' . $mapHeight . ';width:' . $mapWidth . ';border: 1px solid #AAAAAA;"></div>
        <script>
            function truncate(str, maxLength, maxLines) {
                var lines = str.split("\n").splice(0, maxLines);
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i];
                    if (line.length > maxLength) {
                        line = line.substr(0, maxLength);
                        var c = line.length - 1;
                        while (c > 0) {
                            if (line.charAt(c).match(/^[\s\.\,\-\:\[\]\(\)\{\}]$/)) {
                                break;
                            }
                            c--;
                        }
                        line = line.substr(0, c) + "…";
                        lines[i] = line;
                    }
                }
                return lines.join("<br>");
            }

            function createRow(data) {
                if (data) {
                    var title = "";
                    var imageContent = "";
                    var nameContent = "";
                    var displayContent = "";
                    var addressContent = "";
                    var phoneContent = "";
                    var websiteContent = "";
                    var descriptionContent = "";
                    var addressLineContent = "";
                    var zipContent = "";
                    var cityContent = "";

                    if (data.image) {
                        imageContent = "<img class=\"cyclosMapImage\" src=" + data.image.url + "\\/>";
                    }

                    if (data.display) {
                        displayContent = "<div class=\"cyclosMapName\">" + data.display + "<\\/div>";

                    }

                    if (data.name) {
                        nameContent = "<div class=\"cyclosMapName\">" + data.name + "<\\/div>";
                    }

                    title = data.display ? data.display : data.name ? data.name : "";

                    if (data.address) {
                        if (data.address.addressLine1) {
                            addressLineContent = data.address.addressLine1;
                            title += " - " + addressLineContent;
                        }
                        if (data.address.zip) {
                            zipContent = data.address.zip;
                        }
                        if (data.address.city) {
                            cityContent = data.address.city;
                            title += " - " + cityContent;
                        }
                        addressContent = "<div class=\"cyclosMapAddress\">" + addressLineContent + "<br>" +
                            zipContent + " " + cityContent + "<\\/div>";
                    }

                    if (data.phone) {
                        phoneContent = "<a href=\"tel:" + data.phone + "\" class=\"cyclosMapPhone\">" + data.phone +
                            "<\\/a>";
                    }

                    if (data.customValues && data.customValues.' . $websiteName . ') {
                        websiteContent = "<a href=\"//" + data.customValues.' . $websiteName . '.replace("https://", "").replace("http://", "") +
                            "\" class=\"cyclosMapWebsite\" target=\"_blank\">" + data.customValues.' . $websiteName . ' + "<\\/a>";
                    }

                    if (data.customValues && data.customValues.' . $descriptionName . ') {
                        var mapDescription = truncate(data.customValues.' . $descriptionName . ', 150, 2);
                        descriptionContent = "<div class=\"cyclosMapDescription\">" + mapDescription + "<\\/div>";
                    }

                    var content = "<div class=\"cyclosMapTop\">" + imageContent + displayContent + nameContent +
                        addressContent + "<\\/div>";
                    if (phoneContent != "" || websiteContent != "") {
                        content += "<div class=\"cyclosMapMiddle\">" + phoneContent + websiteContent + "<\\/div>";
                    }
                    if (descriptionContent != "") {
                        content += "<div class=\"cyclosMapBottom\">" + descriptionContent + "<\\/div>";
                    }
                    return [title, content];
                } else {
                    return ["", "<div>ERROR<\\/div>"];
                }
            }

            cyclosData = ' . $dataFromCyclos . ';
            var tiles = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "' . $references . '",
                maxZoom: 18
            });

            var map = L.map("leaflet_cyclosUsersMap", {
                layers: [tiles]
            });

            map.attributionControl.setPrefix(false);
            var markers = L.markerClusterGroup({
                spiderfyOnMaxZoom: true
            });

            jQuery.each(cyclosData, function(i, result) {
                var content = createRow(result);
                var marker = L.marker(new L.LatLng(result.address.location.latitude, result.address.location.longitude), {
                    title: content[0],
                    icon: L.icon({
                        iconUrl: "' . plugin_dir_url(__FILE__) . 'icon.png",
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                });

                marker.bindPopup("<div>" + content[1] + "<\\/div>");
                markers.addLayer(marker);
            });

            map.addLayer(markers);
            map.setView([' . $home_lat . ', ' . $home_lon . '], ' . $home_zoom . ');

            L.easyButton("homeButton", function(btn, map) {
                map.setView([' . $home_lat . ', ' . $home_lon . '], ' . $home_zoom . ');
            }).addTo(map);

            map.addControl(new L.Control.Fullscreen());

            var searchControl = new L.Control.Search({
                layer: markers,
                initial: false,
                position: "topleft",
                marker: false,
                zoom: 18
            });

            searchControl.on("search:locationfound", function(e) {
                    searchControl.collapse();
                markers.zoomToShowLayer(e.layer, function() {
                    e.layer.openPopup();
                });
            });

            map.addControl(searchControl);
        </script>';
            return $content;
        } else {
            return "An error ocurred accessing Cyclos, please contact the administrator.";
        }
    }
}
function run_cyclosUsersMap_api() {
    $username = esc_attr(get_option('cyclosUsersMap_adminuser'));
    $accessClientToken = esc_attr(get_option('cyclosUsersMap_token'));
    $password = esc_attr(cyclosUsersMap_decrypt_password(get_option('cyclosUsersMap_adminPassword')));
    $cyclos_url = esc_url(get_option('cyclosUsersMap_url'));
    $group_id = esc_attr(get_option('cyclosUsersMap_groupId'));
    $api_url = $cyclos_url . "/api/users/map?groups=" . $group_id;

    if (!empty($accessClientToken)) {
        $header = array(
            'Access-Client-Token' => "$accessClientToken"
        );
    } elseif (!empty($username) and !empty($password)) {
        $authstring = base64_encode($username . ":" . $password);
        $header = array(
            'authorization' => "Basic $authstring"
        );
    } else {
        print_r("Please make sure to enter an access client token or username and password");
    }

    $response = wp_remote_get($api_url, array(
        'timeout' => '30',
        'redirection' => '10',
        'httpversion' => '1.1',
        'headers' => $header
    ));
    if (wp_remote_retrieve_response_code($response) == 200) {
        return wp_remote_retrieve_body($response);
    }
}

?>

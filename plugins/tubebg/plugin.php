<?php
/**
 * Plugin Name: tubebg.com source
 * Plugin URI: http://phpvibe.com/
 * Description: Adds Tubebg embed source to PHPVibe
 * Version: 1.0
 * Author: PHPVibe Crew
 * Author URI: http://www.phpvibe.com
 * License: GPL
 */

function _Tubebg($hosts = array())
{
    $hosts[] = 'tubebg.com';
    return $hosts;
}

function EmbedTubebg($txt = '')
{
    global $vid;
    if (isset($vid)) {
        if ($vid->VideoProvider($vid->theLink()) == 'tubebg.com') {
            $link = $vid->theLink();
            if (!empty($link)) { // Използване на !empty вместо nullval
                preg_match("/tubebg\.com\/(\d+)/", $link, $matches);
                $id = $matches[1];
                if (!empty($id)) { // Използване на !empty вместо nullval
                    $tembed = '<iframe width="' . get_option('video-width') . '" height="' . get_option('video-height') . '" src="https://www.tubebg.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
                    $txt .= $tembed;
                }
            }
        }
    }
    return $txt;
}

function TubebggetDataFromUrl($url)
{
    $ch = curl_init();
    $timeout = 15;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // add this one, it seems to spawn redirect 301 header
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'); // spoof
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function DetailsTubebg($zvideo = '')
{
    global $video, $vid, $websLink, $VibeProvider, $VibeLink;


    $xvideo = array();
    $xvideo['description'] = '';
    $xvideo['title'] = '';
    $xvideo['thumbnail'] = '';
    $xvideo['duration'] = '';
    $xvideo['tags'] = '';

    if (_contains(strtolower($VibeProvider), 'tubebg')) {

        $html = TubebggetDataFromUrl($VibeLink);

        if (not_empty($html)) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(1);
            $dom->loadHTML($html);
            $xpath = new DOMXpath($dom);
            $jsonScripts = $xpath->query('//script[@type="application/ld+json"]');

            if ($jsonScripts->length < 1) {
                return $xvideo;
            } else {
                foreach ($jsonScripts as $node) {
                    //echo '<pre>';
                    $json = json_decode($node->nodeValue, true);
                    //$xvideo = $json;
                    if (isset($json['name']) && not_empty($json['name'])) {
                        $xvideo['title'] = $json['name'];
                    }
                    if (isset($json['description']) && not_empty($json['description'])) {
                        $xvideo['description'] = $json['description'];
                    }
                    if (isset($json['thumbnailUrl']) && not_empty($json['thumbnailUrl'])) {
                        $xvideo['thumbnail'] = $json['thumbnailUrl'];
                    }
                    if (isset($json['duration']) && not_empty($json['duration'])) {
                        $xvideo['duration'] = toSeconds(str_replace(array('PT', 'M', 'S'), array('', ':', ''), $json['duration']));
                    }

                    //print_r( $xvideo);

                    //echo '</pre>';

                    // your stuff with JSON ...
                }
            }
        }

    }
    /* End function */
    $thevideo = array_merge($zvideo, $xvideo);
    return $thevideo;
}

add_filter('EmbedDetails', 'DetailsTubebg');
add_filter('EmbedModify', 'EmbedTubebg');
add_filter('vibe-video-sources', '_Tubebg');
?>

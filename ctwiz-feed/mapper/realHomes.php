<?php

/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 07/04/15
 * Time: 23:14
 */
class ctwizMapperRealHomes
{
    static function build($post)
    {
        $property_location = get_post_meta($post->ID, 'REAL_HOMES_property_location', true);
        $property_address = get_post_meta($post->ID, 'REAL_HOMES_property_address', true);
        $post_meta_data = get_post_custom($post->ID);
        list($lat, $lon) = explode(",", $property_location);
        $description = str_replace(']]>', ']]&gt;', apply_filters('the_content', get_the_content()));

        $property = new ctwizPropertyModel(array(
            "id" => (string)$post->ID,
            "url" => esc_url(apply_filters('the_permalink', get_permalink())),
            'description' => $description,
            'publishDate' => get_the_modified_time('U'),
            'requested' => doubleval(get_post_meta($post->ID, 'REAL_HOMES_property_price', true)),
            'currency' => get_theme_currency(),
            'latitude' => doubleval($lat),
            'longitude' => doubleval($lon),
            'address' => $property_address,
        ));

        $embed_code = get_post_meta($post->ID, 'REAL_HOMES_embed_code', true);
        $video = false;
        if (!empty($embed_code)) {
            $video = stripslashes(htmlspecialchars_decode($embed_code));
        }

        if (!$video) {
            $video = get_post_meta($post->ID, 'REAL_HOMES_slideshow_url', true);
        }

        if (!$video) {
            $video = get_post_meta($post->ID, 'REAL_HOMES_tour_video_url', true);
        }

        if (!$video) {
            if (preg_match_all('/<div class=\"video\-wrapper\">(.*?)<\/div>/si', $description, $matches)) {
                $video = $matches[1][0];
            }
        }
        if ($video) {
            $property->video = $video;
        }


        $agent_display_option = get_post_meta($post->ID, 'REAL_HOMES_agent_display_option', true);

        if ($agent_display_option == "my_profile_info") {
            $property->agentMobilePhone = get_the_author_meta('mobile_number');
            $property->agentPhone = get_the_author_meta('office_number');
            $property->agentFax = get_the_author_meta('fax_number');
            $property->agentEmail = get_the_author_meta('user_email');

            $property->agentName = get_the_author_meta('display_name');

        } else {
            $property_agent = get_post_meta($post->ID, 'REAL_HOMES_agents', true);
            if ((!empty($property_agent)) && (intval($property_agent) > 0)) {
                $agent_id = intval($property_agent);
                $property->agentMobilePhone = get_post_meta($agent_id, 'REAL_HOMES_mobile_number', true);
                $property->agentPhone = get_post_meta($agent_id, 'REAL_HOMES_office_number', true);
                $property->agentFax = get_post_meta($agent_id, 'REAL_HOMES_fax_number', true);
                $property->agentEmail = get_post_meta($agent_id, 'REAL_HOMES_agent_email', true);
                $property->agentName = get_the_title($agent_id);
            }
        }


        $detail_titles = array_map('strtolower', get_post_meta($post->ID, 'REAL_HOMES_detail_titles', true));
        if (!empty($detail_titles)) {
            $detail_values = get_post_meta($post->ID, 'REAL_HOMES_detail_values', true);
            if (!empty($detail_values)) {
                $details = array_combine($detail_titles, $detail_values);
                foreach ($details as $key => $val) {
                    $prop = ctwizKeywordModel::_($key);
                    if ($prop) {
                        $property->$prop = $val;
                    }
                }
            }
        }

        $features_terms = get_the_terms($post->ID, "property-feature");
        if (!empty($features_terms)) {
            foreach ($features_terms as $fet_trms) {
                $prop = ctwizKeywordModel::_($fet_trms->name);
                if ($prop) {
                    $property->$prop = true;
                }
            }
        }
//        if ($details){
//            if ($features_terms){
//                $features_terms = array_merge($details,$features_terms);
//            }
//            else {
//                $features_terms = $details;
//            }
//        }
//        if ($features_terms && is_array($features_terms)){
//            $property->extra = $features_terms;
//        }
        $status_terms = get_the_terms($post->ID, "property-status");


        if (isset($status_terms) && is_array($status_terms)) {
            foreach ($status_terms as $term) {

                $prope = ctwizKeywordModel::_($term->name);
                if ($prope == "forRent") {
                    $property->forRent = true;
                }
                if ($prope == "forSale") {
                    $property->forSale = true;
                }
            }
        }

        $type_terms = get_the_terms($post->ID, "property-type");
        foreach ($type_terms as $term) {
            $prope = ctwizKeywordModel::_($term->name, "types");
            if ($prope) {
                $property->houseType = $prope;
                break;
            }

        }

        if (!empty($post_meta_data['REAL_HOMES_property_size'][0])) {
            $prop_size = $post_meta_data['REAL_HOMES_property_size'][0];
            $property->sqrm = doubleval($prop_size);
            if (!empty($post_meta_data['REAL_HOMES_property_size_postfix'][0])) {
                $property->sqrmUnit = $post_meta_data['REAL_HOMES_property_size_postfix'][0];
            }
        }

        if (!empty($post_meta_data['REAL_HOMES_property_bedrooms'][0])) {
            $prop_bedrooms = floatval($post_meta_data['REAL_HOMES_property_bedrooms'][0]);
            $property->bedrooms = intval($prop_bedrooms);
        }

        if (!empty($post_meta_data['REAL_HOMES_property_bathrooms'][0])) {
            $prop_bathrooms = floatval($post_meta_data['REAL_HOMES_property_bathrooms'][0]);
            $property->bathrooms = intval($prop_bathrooms);
        }

        if (!empty($post_meta_data['REAL_HOMES_property_garage'][0])) {
            $prop_garage = intval($post_meta_data['REAL_HOMES_property_garage'][0]);
            $property->parkingspace = $prop_garage;
        }

        $properties_images = rwmb_meta('REAL_HOMES_property_images', 'type=plupload_image&size=property-detail-slider-image', $post->ID);
        $photos = array();
        foreach ($properties_images as $image_id) {
            $photos[] = [
                'url' => $image_id['full_url'],
                'caption' => $image_id['title']
            ];
        }
        if (has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_url = wp_get_attachment_url($image_id);
            $photos[] = [
                'url' => $image_url
            ];
        }

        $property->photos = $photos;
        return $property;
    }
}
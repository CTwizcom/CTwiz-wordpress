<?php

/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 07/04/15
 * Time: 23:14
 */
class ctwizMapperWpCasa
{
    static function build($post)
    {

        $description = str_replace(']]>', ']]&gt;', apply_filters('the_content', get_the_content()));
        $standard_details = wpcasa_standard_details();
        $custom = get_post_custom($post->ID);

        $price = $custom['_price'][0];
        $address = $custom['_map_address'][0];
        $property = new ctwizPropertyModel(array(
            "id" => (string)$post->ID,
            "url" => esc_url(apply_filters('the_permalink', get_permalink())),
            'description' => $description,
            'publishDate' => get_the_modified_time('U'),
            'requested' => $price,
            'currency' => wpcasa_get_currency(),
//            'latitude' => doubleval($lat),
//            'longitude' => doubleval($lon),
            'address' => $address,
        ));

        $type_terms = get_the_terms($post->ID, "property-type");
        if ($type_terms) {
            foreach ($type_terms as $term) {
                $prope = ctwizKeywordModel::_($term->name, "types");
                if ($prope) {
                    $property->houseType = $prope;
                    break;
                }
            }
        } else {
            return false;
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


        if (!empty($standard_details)) {
            foreach ($standard_details as $feature => $value) {
                $property_details_value = get_post_meta(get_the_ID(), '_' . $feature, true);
                $prop = ctwizKeywordModel::_($value['label']);
                if ($prop) {
//                    echo $property_details_value."\n";
                    $property->$prop = $property_details_value;
                }
            }
        }

        $property_features_terms = get_the_terms(get_the_ID(), 'feature');

        if ($property_features_terms) {
            foreach ($property_features_terms as $term) {
                $prop = ctwizKeywordModel::_($term->name);
                if ($prop) {
                    $property->$prop = true;
                }
            }
        }

        $status = $custom['_price_status'][0];
        if (!empty($status)) {
            $prope = ctwizKeywordModel::_($status);
            if ($prope == "forRent") {
                $property->forRent = true;
            }
            if ($prope == "forSale") {
                $property->forSale = true;
            }
        }

        $properties_images = get_posts(array(
            "post_parent" => intval($post->ID),
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'order' => 'ASC',
            'orderby' => 'menu_order ID'
        ));
        $photos = array();
        foreach ($properties_images as $image_id) {
            $photos[] = array(
                'url' => wp_get_attachment_url($image_id->ID),
                'caption' => $image_id->post_name
            );
        }
        // get rev slider
        if (isset($custom['_space'][0])) {
            list($a, $b) = explode(" ", str_replace(array("[", "]"), array("", ""), $custom['_space'][0]));
            try {
                $slider = new RevSlider();
                $slider->initByMixed($b);
                $slides = $slider->getSlides(true);
                foreach ($slides as $slide) {
                    $photos[] = array(
                        'url' => $slide->getImageUrl()
                    );
                }
            } catch (Exception $e) {
                //don't care
            }
        }
        if (has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_url = wp_get_attachment_url($image_id);
            $photos[] = array(
                'url' => $image_url
            );
        }

        $property->photos = $photos;

        return $property;
    }
}
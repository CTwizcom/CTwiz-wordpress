<?php
/**
 * JSON Feed Template for displaying JSON Posts feed.
 *
 */

//global $gallery_image_size;

//$size = 'property-detail-slider-image';
//$callback = trim(esc_html(get_query_var('callback')));
//$charset = get_option('charset');

require_once dirname(__FILE__) . '/../model/property.php';
require_once dirname(__FILE__) . '/../model/keyword.php';


$filePath= dirname(__FILE__).'/../config.ini';
$config = parse_ini_file($filePath);


$data = array();
$properties = new WP_Query(array(
    'post_type' => 'property',
    'nopaging' => true
));


require_once dirname(__FILE__) . '/../mapper/'.$config['mapper'].'.php';
$mapperClass = 'ctwizMapper'.$config['mapper'];

//print_r($properties);exit;
while ($properties->have_posts()) {
    $properties->the_post();
    $home = $mapperClass::build($post,$keywords);
    if ($home) {
        $data[] = $home->to_json();
    }
}

//print_r($data);exit;
$json = json_encode($data);

nocache_headers();
//if (!empty($callback)) {
//    header("Content-Type: application/x-javascript; charset={$charset}");
//    echo "{$callback}({$json});";
//} else {
header("Content-Type: application/json; charset={$charset}");
echo $json;
//}


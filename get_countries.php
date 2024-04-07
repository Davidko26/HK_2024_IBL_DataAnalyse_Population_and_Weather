<?php
set_time_limit(2000);
ini_set('memory_limit', '-1');

$connect = mysqli_connect("localhost", "root", "", "hackathon"); 
mysqli_set_charset($connect,"utf8");


$file = fopen("API_SP.POP.TOTL_DS2_en_csv_v2_84031.csv","r");
$array_population = [];
$country = "";
$x = 0;
$array_json = [];
$array_years = [];

while(! feof($file)) {
    $arrays = fgetcsv($file);

    $array_json[$x]['state'] = $arrays[0];
    $array_json[$x]['state-code'] = $arrays[1];
    if (gettype($arrays) != "array") break;
    if ($x == 4) {
        for ($i = 4; $i < count($arrays); $i++) $array_years[$i] = $arrays[$i];
        $x++;
        continue;
    }
    for ($i = 4; $i < count($arrays); $i++) {
        $array_json[$x]['data'][$i]['year'] =  $array_years[$i];
        $array_json[$x]['data'][$i]['population'] =  $arrays[$i];
        $array_json[$x]['data'][$i]['temperature'] =  $arrays[$i];
        $query = 'INSERT INTO population (`country_code`, `name`, `year`, `population`) VALUES ("'. $arrays[1] .'", "'. $arrays[0] .'", "'. $array_years[$i] .'", "'. $arrays[$i] .'")';
        mysqli_query($connect, $query);
    }
    if (gettype($array_json[$x]['data']) == "array") $array_json[$x]['data'] = array_values($array_json[$x]['data']);
    $x++;
}


echo "<pre>";
print_r($array_json);
echo "</pre>";

fclose($file);

?>
<?php
set_time_limit(2000);
ini_set('memory_limit', '-1');

$connect = mysqli_connect("localhost", "root", "", "hackathon"); 
mysqli_set_charset($connect,"utf8");


$file = fopen("API_SP.POP.TOTL_DS2_en_csv_v2_84031.csv","r");
$country = "";
$x = 0;

$URL = "https://climathon.iblsoft.com/data/era5-land-monthly-means/edr/collections/height-above-ground/position?coords=POINT(". $lat ."%20". $long .")&parameter-name=temperature_stat:avg&datetime=1960-02,%201961-01,%201962-01,%201963-01,%201964-01,%201965-01,%201966-01,%201967-01,%201968-01,%201969-01,%201970-01,%201971-01,%201972-01,%201973-01,%201974-01,%201975-01,%201976-01,%201977-01,%201978-01,%201979-01,%201980-01,%201981-01,%201982-01,%201983-01,%201984-01,%201985-01,%201986-01,%201987-01,%201988-01,%201989-01,%201990-01,%201991-01,%201992-01,%201993-01,%201994-01,%201995-01,%201996-01,%201997-01,%201998-01,%201999-01,%202000-01,%202001-01,%202002-01,%202003-01,%202004-01,%202005-01,%202006-01,%202007-01,%202008-01,%202009-01,%202010-01,%202011-01,%202012-01,%202013-01,%202014-01,%202015-01,%202016-01,%202017-01,%202018-01,%202019-01,%202020-01,%202021-01,%202022-01&f=CoverageJSON";
file_put_contents("data.json", fopen("$URL", 'r'));


$data_json2 = json_decode(file_get_contents("JSON-krajiny.json"), true);

foreach ($data_json2 as $key => $krajina) {
    print_r($krajina);
    echo "<br>$key<br>";
    $sum_avg_temp = 0;
    $count_avg = 0;
    $array_country = [];
    for ($i = 0; $i < count($krajina); $i++) {
        $lat = $krajina[$i][1];
        $long = $krajina[$i][0];
         
        $URL = "https://climathon.iblsoft.com/data/era5-land-monthly-means/edr/collections/height-above-ground/position?coords=POINT(". $lat ."%20". $long .")&parameter-name=temperature_stat:avg&datetime=1960-02,%201961-01,%201962-01,%201963-01,%201964-01,%201965-01,%201966-01,%201967-01,%201968-01,%201969-01,%201970-01,%201971-01,%201972-01,%201973-01,%201974-01,%201975-01,%201976-01,%201977-01,%201978-01,%201979-01,%201980-01,%201981-01,%201982-01,%201983-01,%201984-01,%201985-01,%201986-01,%201987-01,%201988-01,%201989-01,%201990-01,%201991-01,%201992-01,%201993-01,%201994-01,%201995-01,%201996-01,%201997-01,%201998-01,%201999-01,%202000-01,%202001-01,%202002-01,%202003-01,%202004-01,%202005-01,%202006-01,%202007-01,%202008-01,%202009-01,%202010-01,%202011-01,%202012-01,%202013-01,%202014-01,%202015-01,%202016-01,%202017-01,%202018-01,%202019-01,%202020-01,%202021-01,%202022-01&f=CoverageJSON";
        file_put_contents("data.json", fopen("$URL", 'r'));

        $data_json = json_decode(json_encode(file_get_contents("data.json"), true));

        if (json_decode($data_json)->coverages != null) {
            for ($j = 0; $j < count((json_decode($data_json)->coverages)); $j++) {
                $temperature = ((json_decode($data_json)->coverages)[$j]->ranges->{'temperature_stat:avg'}->values[0]);
                $year = (json_decode($data_json)->coverages)[$j]->domain->axes->t->values[0];
                echo "$key + $i + $temperature + $year<br><br>";
                $array_country[explode('-', $year)[0]][$i] = $temperature;
            }
        }
    }   
    for ($i = 1960; $i < count($array_country)+1960; $i++) {
        $avg_temp = array_sum($array_country[$i])/count($array_country[$i]);
        $query = "INSERT INTO countries (country, country_code, year, temperature) VALUES ('x', '$key', ". $i .", '".$avg_temp."')";
        mysqli_query($connect, $query);
    }    
}
?>
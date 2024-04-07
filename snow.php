<?php
set_time_limit(20000);
ini_set('memory_limit', '-1');

$connect = mysqli_connect("localhost", "root", "", "hackathon"); 
mysqli_set_charset($connect,"utf8");


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
         
        $URL = "https://climathon.iblsoft.com/data/era5-land-monthly-means/edr/collections/single-layer/position?coords=POINT(". $lat ."%20". $long .")&parameter-name=snow-depth_gnd-surf_stat:avg&datetime=1960-01, 1961-01, 1962-01, 1963-01, 1964-01, 1965-01, 1966-01, 1967-01, 1968-01, 1969-01, 1970-01, 1971-01, 1972-01, 1973-01, 1974-01, 1975-01, 1976-01, 1977-01, 1978-01, 1979-01, 1980-01, 1981-01, 1982-01, 1983-01, 1984-01, 1985-01, 1986-01, 1987-01, 1988-01, 1989-01, 1990-01, 1991-01, 1992-01, 1993-01, 1994-01, 1995-01, 1996-01, 1997-01, 1998-01, 1999-01, 2000-01, 2001-01, 2002-01, 2003-01, 2004-01, 2005-01, 2006-01, 2007-01, 2008-01, 2009-01, 2010-01, 2011-01, 2012-01, 2013-01, 2014-01, 2015-01, 2016-01, 2017-01, 2018-01, 2019-01, 2020-01, 2021-01, 2022-01&f=CoverageJSON";
        $URL = preg_replace("/ /", "%20", $URL);
        file_put_contents("data.json", fopen("$URL", 'r'));

        $data_json = json_decode(json_encode(file_get_contents("data.json"), true));

        if (json_decode($data_json)->coverages != null) {
            for ($j = 0; $j < count((json_decode($data_json)->coverages)); $j++) {
                $temperature = ((json_decode($data_json)->coverages)[$j]->ranges->{'snow-depth_gnd-surf_stat:avg'}->values[0]);
                $year = (json_decode($data_json)->coverages)[$j]->domain->axes->t->values[0];
                echo "$key + $i + $temperature + $year<br><br>";
                $array_country[explode('-', $year)[0]][$i] = $temperature;
            }
        }
    }
    for ($i = 1960; $i < count($array_country)+1960; $i++) {
        $avg_temp = array_sum($array_country[$i])/count($array_country[$i]);
        $query = "UPDATE countries SET snow = '". $avg_temp ."' WHERE country_code = '$key' AND year = '$i'";
        mysqli_query($connect, $query);
    }
   
}
fclose($file);

?>
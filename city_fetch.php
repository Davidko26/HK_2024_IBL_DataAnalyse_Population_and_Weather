<?php                
    $array_csv = [];
    $i = 0;
    $temperatures = [];
    $countries = ["AUT","BIH", "BGR", "CZE", "DEU", "HRV", "HUN", "ITA", "POL", "ROU", "SRB", "SVN", "SVK", "UKR"];
    $file = fopen("worldcities-STRED.csv", "r");
    while(!feof($file)) {
        //$array_csv[$i] = fgetcsv($file);
        if ($i > 0) {
            $array_csv[$i] = fgetcsv($file);
        }
        else fgetcsv($file);
        $i++;
    }
    $array_csv = array_values($array_csv);

    $j = 0;
    foreach($array_csv as $city_array) {
        foreach($countries as $country) {
            //echo $city_array[5] . " " . $country . "<br>";
            if(is_array($city_array) && ($city_array[6] == $country)) {
                $temperatures[$country][$j] = [$city_array[2], $city_array[3]];
                $temperatures[$country] = array_values($temperatures[$country]);
            }
        }
        $j++;
    }


    //echo "<pre>";
    $temperatures_json = json_encode($temperatures);
    echo $temperatures_json;
    //echo "</pre>";
?>
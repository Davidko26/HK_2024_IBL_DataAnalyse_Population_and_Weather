<?php 
    $connect = mysqli_connect("localhost", "root", "", "hackathon"); 
    mysqli_set_charset($connect,"utf8");

    $sql = "SELECT country FROM countries GROUP BY country";
    $result = mysqli_query($connect, $sql);
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $countries[$i] = $row["country"];
        $i++;
    }

    function print_values($connect, $state, $type) {
        $x = 0;
        $sql = "SELECT population, temperature, snow FROM countries WHERE country='$state'";
        $result = mysqli_query($connect, $sql);
        // $pole = [];
        // $pole_temp = [];
        // $pole_avg = [];
        while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $pole[$x] = $row['population'];
            $pole_temp[$x] = $row['temperature'] - 273.15;
            $pole_tmp_avg[$x] = array_sum(array_slice($pole_temp, 0, $x+1)) / ($x+1);
            $pole_snow[$x] = $row["snow"];
            $pole_snow_avg[$x] = array_sum(array_slice($pole_snow, 0, $x+1)) / ($x+1);
            $x++;
        }

        //$pole_avg2 = (array_sum(array_slice($pole_temp, 0, 5)) / 5 + array_sum(array_slice($pole_temp, -5)) / 5) / 2;
        //$a = array_sum(array_slice($pole_temp, 0, 5)) / 5;
        //$b = array_sum(array_slice($pole_temp, -5)) / 5;
        //$pole_20_years = array_sum(array_slice($pole_20_years, -20)) / 20;

        //echo $a . " " . $b;

        if ($type == "fut_temp") {
            $total_change = 0;
            for ($i = 2; $i <= 62; $i++) {
                $change = $pole_temp[$i] - $pole_temp[$i - 1];
                $total_change += $change;
            }

            $avg_change = $total_change / 62;

            for ($i = 0; $i <= 27; $i++) {
                $prediction_temp[$i] = $pole_temp[62] + ($i + 1) * $avg_change;
            }
        } elseif ($type == "fut_snow") {
            $total_change = 0;
            for ($i = 2; $i <= 62; $i++) {
                $change = $pole_snow[$i - 1] - $pole_snow[$i];
                $total_change += $change;
            }

            $avg_change = $total_change / 62;

            for ($i = 0; $i <= 27; $i++) {
                $prediction_snow[$i] = $pole_snow[62] - ($i - 62) * $avg_change;
            }
        }

        $pole_str = implode(",", $pole);
        $pole_temp_str = implode(",", $pole_temp);
        $pole_avg_str = implode(",", $pole_tmp_avg);
        if ($type == "fut_temp") $pole_predic_temp_str = implode(",", $prediction_temp);
        if ($type == "fut_snow") $pole_predic_snow_str = implode(",", $prediction_snow);
        $pole_snow_str = implode(",", $pole_snow);
        $pole_snow_avg_str = implode(",", $pole_snow_avg);
        //$exponent = log($b / $a) / 52;
        
        if ($type == 'population') return $pole_str;
        elseif ($type == 'temperature') return $pole_temp_str;
        elseif ($type == 'snow') return $pole_snow_str;
        elseif ($type == "averageTmp") return $pole_avg_str;
        elseif ($type == "averageSnow") return $pole_snow_avg_str;
       // elseif ($type == 'exponent') return abs($exponent);
        elseif ($type == "fut_temp") return $pole_predic_temp_str;
        elseif ($type == "fut_snow") return $pole_predic_snow_str;
    }

    function print_future_values($connect, $state, $type) {
        $exponent = 1 + round(print_values($connect, $state, "exponent"), 4);
        for($i = 2023, $j = 0; $i <= 2050; $i++, $j++) {
            $pole_years[$j] = $i;
            // if ($j == 0) {
            //     if (print_values($connect, $state, "last_temp") < 0)$pole_to_avg[$j] = pow(abs(print_values($connect, $state, "last_temp")), $exponent) * -1;
            //     else $pole_to_avg[$j] = pow(print_values($connect, $state, "last_temp"), $exponent);
            // } 
            // else {
            //     if($pole_to_avg[$j-1] < 0) $pole_to_avg[$j] = pow(abs($pole_to_avg[$j-1]), $exponent) * -1;
            //     else $pole_to_avg[$j] = pow($pole_to_avg[$j-1], $exponent);
            // } 
        }

        $pole_str = implode(",", $pole_years);
        //$pole_temp_str = implode(",", $pole_temp);

        if ($type == 'population') return $pole_str;
        //elseif ($type == 'temperature') return $pole_temp_str;
    }
    //$selected = $_POST['selected'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
</head>
<body>
    <header>Effect of population on weather in the countries of Central Europe</header>
    <div>
        <form method="POST">
            <label for="state">Enter a state</label>
            <select name="state" id="state">
                <?php foreach($countries as $country): ?>
                    <option name='<?= $country ?>'<?php if (isset($_POST["state"]) && $_POST["state"] == $country) echo " selected"?>><?= $country ?></option>
                <?php endforeach ?>
            </select>
            <input type="submit">
        </form>
    </div>
    <div class="container">
        <?php
            if($_POST) {
                $state = $_POST["state"];

                echo "<div class='group'>
                <h2>Temperature</h2>
                <h3>History</h3>  
                <canvas id='historyChartTemperature' style='width:100%;max-width:600px'></canvas>
                <h3>Prediction</h3>  
                <canvas id='futureChartTemperature' style='width:100%;max-width:600px'></canvas></div>


                <script>
                    const xValues = [" . print_values($connect, $state, "population") . "];
                    const yValues = [" . print_values($connect, $state, "temperature") . "];
                    const averageValues = [" . print_values($connect, $state, "averageTmp") . "];
                    new Chart('historyChartTemperature', {
                        type: 'line',
                        data: {
                            labels: xValues,
                            datasets: [{
                                fill: false,
                                backgroundColor:'rgba(0,0,255,1.0)',
                                borderColor: 'rgba(0,0,255,0.1)',
                                data: yValues
                            },{
                                fill: false,
                                backgroundColor:'rgba(255,0,0,1.0)',
                                borderColor: 'rgba(255,0,0,0.1)',
                                data: averageValues
                            }]
                        },
                        options:{
                            legend: {display: false}
                        }
                    });
                </script>

                <script>
                    const xValues2 = [" . print_future_values($connect, $state, "population") . "];
                    const yValues2 = [" . print_values($connect, $state, "fut_temp") . "];
                    new Chart('futureChartTemperature', {
                        type: 'line',
                        data: {
                            labels: xValues2,
                            datasets: [{
                                fill: false,
                                backgroundColor:'rgba(255,0,0,1.0)',
                                borderColor: 'rgba(255,0,0,0.1)',
                                data: yValues2
                            }]
                        },
                        options:{
                            legend: {display: false}
                        }
                    });
                </script>";

                echo "<div class='group'>
                <h2>Temperature</h2>
                <h3>History</h3>  
                <canvas id='historyChartSnow' style='width:100%;max-width:600px'></canvas>
                <h3>Prediction</h3>  
                <canvas id='futureChartSnow' style='width:100%;max-width:600px'></canvas></div>


                <script>
                    const xValues3 = [" . print_values($connect, $state, "population") . "];
                    const yValues3 = [" . print_values($connect, $state, "snow") . "];
                    const averageValues3 = [" . print_values($connect, $state, "averageSnow") . "];
                    new Chart('historyChartSnow', {
                        type: 'line',
                        data: {
                            labels: xValues3,
                            datasets: [{
                                fill: false,
                                backgroundColor:'rgba(0,0,255,1.0)',
                                borderColor: 'rgba(0,0,255,0.1)',
                                data: yValues3
                            },{
                                fill: false,
                                backgroundColor:'rgba(255,0,0,1.0)',
                                borderColor: 'rgba(255,0,0,0.1)',
                                data: averageValues3
                            }]
                        },
                        options:{
                            legend: {display: false}
                        }
                    });
                </script>
                
                <script>
                const xValues4 = [" . print_future_values($connect, $state, "population") . "];
                const yValues4 = [" . print_values($connect, $state, "fut_snow") . "];
                new Chart('futureChartSnow', {
                    type: 'line',
                    data: {
                        labels: xValues4,
                        datasets: [{
                            fill: false,
                            backgroundColor:'rgba(255,0,0,1.0)',
                            borderColor: 'rgba(255,0,0,0.1)',
                            data: yValues4
                        }]
                    },
                    options:{
                        legend: {display: false}
                    }
                });
                </script>";

                
            }
        ?>
    </div>
    

    

    
</body>

<?php
    
?>
</html>
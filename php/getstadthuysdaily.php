<?php
require_once "db_connect.php";

session_start();

$cars = array (
    array(26,33,23,0),
    array(25,38,26,11),
    array(24,37,27,12),
    array(25,36,28,11),
    array(25,36,28,11),
    array(22,35,28,12)
);

if(isset($_POST['startDate'], $_POST['endDate'])){
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("SELECT * FROM Melaka_traffic WHERE Date>=? AND Date<=? ORDER BY Date")) {
        $select_stmt->bind_param('ss', $startDate, $endDate);
        
        // Execute the prepared query.
        if (! $select_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                )); 
        }
        else{
            $result = $select_stmt->get_result();
            $message = array();
            $dateBar = array();
            $ent1Count = 0;
            $ent2Count = 0;
            $ent3Count = 0;
            $ent4Count = 0;
            
            while ($row = $result->fetch_assoc()) {
                if(!in_array(substr($row['Date'], 10, 3), $dateBar)){
                    $message[] = array( 
                        'Date' => substr($row['Date'], 10, 3).":00",
                        'Date2' => substr($row['Date'], 0, 10),
                        'ent1Count' => 0,
                        'ent2Count' => 0,
                        'ent3Count' => 0,
                        'ent4Count' => 0,
                        'veh1Count' => 0,
                        'total' => 0
                    );

                    array_push($dateBar, substr($row['Date'], 10, 3));
                }

                $key = array_search(substr($row['Date'], 10, 3), $dateBar);

                if($row['Place'] == 'redhouse'){
                    if($row['Condition'] == 'PPL-in'){
                        $count = $row['Count'];

                        if(substr($row['Date'], 0, 10) == '2023-09-02'){
                            $count = (float)$row['Count'] * 1.5;
                        }
                        else if(substr($row['Date'], 0, 10) == '2023-08-26'){
                            $count = (float)$row['Count'] * 1.5;
                        }

                        if($row['Device'] == 'e1'){
                            $message[$key]['ent1Count'] += round((int)$count * 1.2);
                            $ent1Count += round((int)$count * 1.2);
                        }
                        else if($row['Device'] == 'e2'){
                            $message[$key]['ent2Count'] += round((int)$count * 1.2);
                            $ent2Count += round((int)$count * 1.2);
                        }
                        else if($row['Device'] == 'e3'){
                            $message[$key]['ent3Count'] += round((int)$count * 1.4);
                            $ent3Count += round((int)$count * 1.4);
                        }
                        else if($row['Device'] == 'e4'){
                            $message[$key]['ent4Count'] += round((int)$count * 1.2);
                            $ent4Count += round((int)$count * 1.2);
                        }
                    }
                    else if($row['Condition'] == 'VCL-in'){
                        if($row['Device'] == 'e1'){
                            $message[$key]['veh1Count'] += (int)$row['Count'];
                        }
                    }
                }
            }

            for($i=0; $i<count($message); $i++){
                if($message[$i]['Date2'] == '2023-09-02'){
                    $message[$i]['total'] = (int)$message[$i]['total'] + 4;
                    $message[$i]['ent1Count'] = (int)$message[$i]['ent1Count'] + 4;
                    $ent1Count = $ent1Count + 4;
                    break;
                }
                else if($message[$i]['Date2'] == '2023-08-26'){
                    $message[$i]['total'] = (int)$message[$i]['total'] + 2;
                    $message[$i]['ent1Count'] = (int)$message[$i]['ent1Count'] + 2;
                    $ent1Count = $ent1Count + 2;
                    break;
                }
                else if($message[$i]['Date2'] >= "2023-08-02"){
                    $month = 0;

                    // Find month
                    if(substr($message[$i]['Date2'], 5, 2) == '08'){
                        $month = 0;
                    }
                    else if(substr($message[$i]['Date2'], 5, 2) == '09'){
                        $month = 1;
                    }
                    else if(substr($message[$i]['Date2'], 5, 2) == '10'){
                        $month = 2;
                    }
                    else if(substr($message[$i]['Date2'], 5, 2) == '11'){
                        $month = 3;
                    }
                    else if(substr($message[$i]['Date2'], 5, 2) == '12'){
                        $month = 4;
                    }
                    else if(substr($message[$i]['Date2'], 5, 2) == '01'){
                        $month = 5;
                    }

                    //Find Total
                    if($message[$i]['ent1Count'] != 0){
                        $message[$i]['total'] = floatval($message[$i]['ent1Count']/$cars[$month][0] * 100);
                    }
                    else if($message[$i]['ent2Count'] != 0){
                        $message[$i]['total'] = floatval($message[$i]['ent2Count']/$cars[$month][1] * 100);
                    }
                    else if($message[$i]['ent3Count'] != 0){
                        $message[$i]['total'] = floatval($message[$i]['ent3Count']/$cars[$month][2] * 100);
                    }
                    else if($message[$i]['ent4Count'] != 0){
                        $message[$i]['total'] = floatval($message[$i]['ent4Count']/$cars[$month][3] * 100);
                    }

                    // Assign Value
                    if($message[$i]['ent1Count'] == 0){
                        $message[$i]['ent1Count'] = round(floatval($message[$i]['total'] * ($cars[$month][0]/100)) * 1.2);
                        $ent1Count += round((int)$message[$i]['ent1Count'] * 1.2);
                    }
                    
                    if($message[$i]['ent2Count'] == 0){
                        $message[$i]['ent2Count'] = round(floatval($message[$i]['total'] * ($cars[$month][1]/100)) * 1.2);
                        $ent2Count += round((int)$message[$i]['ent2Count'] * 1.2);
                    }
                    
                    if($message[$i]['ent3Count'] == 0){
                        $message[$i]['ent3Count'] = round(floatval($message[$i]['total'] * ($cars[$month][2]/100)) * 1.4);
                        $ent3Count += round((int)$message[$i]['ent3Count'] * 1.4);
                    }

                    if($message[$i]['ent4Count'] == 0 && $message[$i]['Date2'] >= '2023-09-10'){
                        $message[$i]['ent4Count'] = round(floatval($message[$i]['total'] * ($cars[$month][3]/100)) * 1.2);
                        $ent4Count += round((int)$message[$i]['ent4Count'] * 1.2);
                    }

                    //$message[$i]['ent1Count'] = $message[$i]['ent1Count'] + $message[$i]['ent4Count'];
                }
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message,
                    "ent1Count" => $ent1Count,
                    "ent2Count" => $ent2Count,
                    "ent3Count" => $ent3Count,
                    "ent4Count" => $ent4Count
                )
            );  
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Parameter"
        )
    ); 
}
<?php
require_once "db_connect.php";

session_start();

$cars = array (
    array(26,33,23,0),
    array(25,38,26,11)
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
                if(!in_array(substr($row['Date'], 0, 10), $dateBar)){
                    $message[] = array( 
                        'Date' => substr($row['Date'], 0, 10),
                        'ent1Count' => 0,
                        'ent2Count' => 0,
                        'ent3Count' => 0,
                        'ent4Count' => 0,
                        'veh1Count' => 0,
                        'total' => 0
                    );

                    array_push($dateBar, substr($row['Date'], 0, 10));
                }

                $key = array_search(substr($row['Date'], 0, 10), $dateBar);

                if($row['Place'] == 'redhouse'){
                    if($row['Condition'] == 'PPL-in'){
                        $count = $row['Count'];

                        if(substr($row['Date'], 0, 10) == '2023-09-02' && trim(substr($row['Date'], 10, 3)) >= '18:00'){
                            $count = round((float)$row['Count'] * 1.5);
                        }
                        else if(substr($row['Date'], 0, 10) == '2023-08-26' && trim(substr($row['Date'], 10, 3)) >= '18:00'){
                            $count = (float)$row['Count'] * 1.5;
                        }

                        if($row['Device'] == 'e1'){
                            $message[$key]['ent1Count'] += (int)$count;
                            $ent1Count += (int)$count;
                        }
                        else if($row['Device'] == 'e2'){
                            $message[$key]['ent2Count'] += (int)$count;
                            $ent2Count += (int)$count;
                        }
                        else if($row['Device'] == 'e3'){
                            $message[$key]['ent3Count'] += (int)$count;
                            $ent3Count += (int)$count;
                        }
                        else if($row['Device'] == 'e4'){
                            $message[$key]['ent4Count'] += (int)$count;
                            $ent4Count += (int)$count;
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
                if($message[$i]['Date'] >= "2023-08-02"){
                    $month = 0;

                    // Find month
                    if(substr($message[$i]['Date'], 5, 2) == '08'){
                        $month = 0;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '09'){
                        $month = 1;
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
                        $message[$i]['ent1Count'] = round(floatval($message[$i]['total'] * ($cars[$month][0]/100)));
                        $ent1Count += (int)$message[$i]['ent1Count'];
                    }
                    
                    if($message[$i]['ent2Count'] == 0){
                        $message[$i]['ent2Count'] = round(floatval($message[$i]['total'] * ($cars[$month][1]/100)));
                        $ent2Count += (int)$message[$i]['ent2Count'];
                    }
                    
                    if($message[$i]['ent3Count'] == 0){
                        $message[$i]['ent3Count'] = round(floatval($message[$i]['total'] * ($cars[$month][2]/100)));
                        $ent3Count += (int)$message[$i]['ent3Count'];
                    }

                    if($message[$i]['ent4Count'] == 0 && $message[$i]['Date'] >= '2023-09-10'){
                        $message[$i]['ent4Count'] = round(floatval($message[$i]['total'] * ($cars[$month][3]/100)));
                        $ent4Count += (int)$message[$i]['ent4Count'];
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
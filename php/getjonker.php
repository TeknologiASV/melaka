<?php
require_once "db_connect.php";

session_start();

$cars = array (
    array(26,7,24,9,12,22),
    array(22,8,25,10,18,17),
    array(20,9,21,11,17,22),
    array(19,7,25,13,17,19),
    array(17,8,26,13,16,20),
    array(21,8,22,10,17,22),
    array(21,8,22,10,17,22),
    array(21,10,22,8,17,22),
    array(21,10,22,0,17,22),
    array(21,10,22,0,17,22),
    array(21,10,22,0,17,22),
    array(21,10,22,0,17,22)
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
            $ent5Count = 0;
            $ent6Count = 0;
            
            while ($row = $result->fetch_assoc()) {
                if(!in_array(substr($row['Date'], 0, 10), $dateBar)){
                    $message[] = array( 
                        'Date' => substr($row['Date'], 0, 10),
                        'ent1Count' => 0,
                        'ent2Count' => 0,
                        'ent3Count' => 0,
                        'ent4Count' => 0,
                        'ent5Count' => 0,
                        'ent6Count' => 0,
                        'veh2Count' => 0,
                        'veh7Count' => 0,
                        'total' => 0
                    );

                    array_push($dateBar, substr($row['Date'], 0, 10));
                }

                $key = array_search(substr($row['Date'], 0, 10), $dateBar);

                if($row['Place'] == 'Jonker'){
                    if($row['Condition'] == 'PPL-in'){
                        if($row['Device'] == 'jp1'){
                            $message[$key]['ent1Count'] += (int)$row['Count'];
                            $ent1Count += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'jp3'){
                            $message[$key]['ent2Count'] += (int)$row['Count'];
                            $ent2Count += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'jp4'){
                            $message[$key]['ent3Count'] += (int)$row['Count'];
                            $ent3Count += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'jp5'){
                            $message[$key]['ent4Count'] += (int)$row['Count'];
                            $ent4Count += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'jp6'){
                            $message[$key]['ent5Count'] += (int)$row['Count'];
                            $ent5Count += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'jp7' || $row['Device'] == 'jp8'){
                            $message[$key]['ent6Count'] += (int)$row['Count'];
                            $ent6Count += (int)$row['Count'];
                        }
                    }
                    else if($row['Condition'] == 'VCL-in'){
                        if($row['Device'] == 'jp7'){
                            $message[$key]['veh7Count'] += (int)$row['Count'];
                        }
                    }
                }
            }

            for($i=0; $i<count($message); $i++){
                if($message[$i]['Date'] >= "2023-05-28"){
                    $month = 0;

                    // Find month
                    if(substr($message[$i]['Date'], 5, 2) == '12'){
                        $month = 0;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '01'){
                        $month = 1;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '02'){
                        $month = 2;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '03'){
                        $month = 3;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '04'){
                        $month = 4;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '05'){
                        $month = 5;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '06'){
                        $month = 6;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '07'){
                        $month = 7;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '08'){
                        $month = 8;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '09'){
                        $month = 9;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '10'){
                        $month = 10;
                    }
                    else if(substr($message[$i]['Date'], 5, 2) == '11'){
                        $month = 11;
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
                    else if($message[$i]['ent5Count'] != 0){
                        $message[$i]['total'] = floatval($message[$i]['ent5Count']/$cars[$month][4] * 100);
                    }
                    else if($message[$i]['ent6Count'] != 0){
                        $message[$i]['total'] = floatval($message[$i]['ent6Count']/$cars[$month][5] * 100);
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
                    
                    if($message[$i]['ent4Count'] == 0){
                        $message[$i]['ent4Count'] = round(floatval($message[$i]['total'] * ($cars[$month][3]/100)));
                        $ent4Count += (int)$message[$i]['ent4Count'];
                    }
                    
                    if($message[$i]['ent5Count'] == 0){
                        $message[$i]['ent5Count'] = round(floatval($message[$i]['total'] * ($cars[$month][4]/100)));
                        $ent5Count += (int)$message[$i]['ent5Count'];
                    }
                    
                    if($message[$i]['ent6Count'] == 0){
                        $message[$i]['ent6Count'] = round(floatval($message[$i]['total'] * ($cars[$month][5]/100)));
                        $ent6Count += (int)$message[$i]['ent6Count'];
                    }
                }
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message,
                    "ent1Count" => $ent1Count,
                    "ent2Count" => $ent2Count,
                    "ent3Count" => $ent3Count,
                    "ent4Count" => $ent4Count,
                    "ent5Count" => $ent5Count,
                    "ent6Count" => $ent6Count
                ));   
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
<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['startDate'], $_POST['endDate'])){
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("SELECT * FROM uniqlo_1u WHERE Date>=? AND Date<=? ORDER BY Date")) {
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
                        'veh7Count' => 0
                    );

                    array_push($dateBar, substr($row['Date'], 0, 10));
                }

                $key = array_search(substr($row['Date'], 0, 10), $dateBar);

                if($row['Mode'] == 'Jonker'){
                    if($row['Door'] == 'PPL-in'){
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
                    else if($row['Door'] == 'VCL-in'){
                        if($row['Device'] == 'jp1'){
                            $message[$key]['veh2Count'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'jp7'){
                            $message[$key]['veh7Count'] += (int)$row['Count'];
                        }
                    }
                }
            }

            if($ent3Count <= 0){
                $ent3Count = round($ent2Count * 0.75);
            }

            if($ent5Count <= 0){
                $ent5Count = round($ent4Count * 0.85);
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
                    "ent6Count" => $ent6Count,
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
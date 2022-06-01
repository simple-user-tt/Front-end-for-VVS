<?php session_start() ?>

<!DOCTYPE html><!--- HTML STARTS --->

<html lang="ru">

<head><!--- HEAD STARTS --->
    
    <meta name = "author" content = "Alex" />
    <meta name = "keywords" content = "����������, �����, ��, �������������, ��, ������������ ��������, IT, HTML, PC, PHP, programs, programming, recognition, vehicles, Windows" />
    <meta name = "description" content = "Vehicle Verification System" />
    <meta http-equiv = "refresh" content = "300; url = monitor.php" />
    <meta charset = "UTF-8" />
    <link rel = "stylesheet" href = "style.css" />
    <title>Vehicle Verification System - �������</title>

<script>
function help(){

         var helpWindow = window.open("help.php", "help", "height = 800, width = 600");
         helpWindow.focus();

} // end help
</script>

</head><!--- HEAD ENDS ---->

<body><!--- BODY STARTS --->

<!------ UNORDERED LISTS ------>

<ul>
   <li id = "monitor-his" title = "����������">
       <a href = "monitor.php">����������</a>
   </li>
</ul>
<ul>
   <li id = "history-his" title = "�������">
       <a href = "history.php">�������</a>
   </li>
</ul>
<ul>
<ul>
   <li id = "help-button" title = "�������">
       <input type = "button" onClick = "help()" value = "?" />
   </li>
</ul>

<!---- END OF UNORDERED LISTS ---->


<!---- DATA INPUT FIELDS ---->

<form method = "post">
<div id = "plate-date">
    ���.����� <span id = "plate"><input type = "text" id = "" name = "plate" maxlength = "40" size = "20" title = "���.�����" value = "" /></span>
    <span id = "from">� <input type = "text" id = "" name = "from" maxlength = "40" size = "20" title = "� ����� ���� � ������� �������������� �����. ������: YYYY-MM-DD HH-MM-SS" 
          value = "" /></span>
    <span id = "to">�� <input type = "text" id = "" name = "to" maxlength = "40" size = "20" title = "�� ����� ���� � ����� �������������� �����. ������: YYYY-MM-DD HH-MM-SS" 
          value = "" /></span>
    <span id = "search"><input type = "submit" id = "" name = "" title = "�������, ����� ������ ����� �� ��������� ������" value = "�����" /></span>
</div>
</form>

<!---- DATA INPUT FIELDS END ---->

<!--- DATA OUTPUT FIELD AND EXTRA FIELD --->

<?php

$iFlag = 0;

$from = $_REQUEST["from"];
$path = $_REQUEST["path"];
$plate = $_REQUEST["plate"];
$to = $_REQUEST["to"];
$type = $_REQUEST["type"];

check_input();
session_set();

if (  (  !(empty($plate))  || (   !(empty($from))  &&  !(empty($to))  )   )||  (  !(empty($plate)) && !(empty($from)) && !(empty($to))  )     ){   

    db_connect();
    
    if (  !(empty($plate)) && (  empty($from) ||  empty($to)  )  ){
       
        $sql1 = "SELECT * FROM entry_monitoring WHERE Government_number = \"$plate\" ORDER BY Time_Of_Entry ASC";      
        $result1 = mysqli_query($link, $sql1);

        $sql2 = "SELECT * FROM check_out_monitoring WHERE Government_number = \"$plate\" ORDER BY Time_Check_Out ASC";      
        $result2 = mysqli_query($link, $sql2);
      
        check_query($result1, $result2);

        table_header();

        $outputCSV = "";
        $outputXML = "";
    
        $outputXML .= "<?xml version = \"1.0\">\n";

        while ( $row1 = mysqli_fetch_assoc($result1) ) {

                $outputXML .= "<vehicle>\n";

                $row2 = mysqli_fetch_assoc($result2);
                
                $iFlag = 1;
                $output1 .= "<tr>\n";
                $output1 .= "<td>{$row1["Time_Of_Entry"]}</td>";
                $outputCSV .= "({$row1["Time_Of_Entry"]},";
                $outputXML .= "<time_of_entry>{$row1["Time_Of_Entry"]}</time_of_entry>\n";

                $timeOfEntry = $row1["Time_Of_Entry"];
                $timeCheckOut = $row2["Time_Check_Out"];
                $checkOut = strtotime($timeCheckOut);
                $entry = strtotime($timeOfEntry);
                $now = strtotime("now");

                if ($checkOut == 0) {
                    $output1 .= "<td style = \" color:red \">�� �� ����������</td>";   
                    $outputCSV .= "�� �� ����������,";
                    $outputXML .= "<time_check_out>�� �� ����������</time_check_out>\n";
                    $timeResult = $now - $entry; 
                    time_result($timeResult, 0);
                    time_result($timeResult, 1);
                    time_result($timeResult, 2);
                } // end if
                else {
                    $output1 .= "<td>{$row2["Time_Check_Out"]}</td>";
                    $outputCSV .= "{$row2["Time_Check_Out"]},";
                    $outputXML .= "<time_check_out>{$row2["Time_Check_Out"]}</time_check_out>\n";
                    $timeResult = $checkOut - $entry; 
                    time_result($timeResult, 0);
                    time_result($timeResult, 1);
                    time_result($timeResult, 2);
                } // end else

                $output1 .= "<td>{$row1["Brand"]}</td><td>{$row1["Model"]}</td><td>{$row1["Release_Year"]}</td><td>{$row1["Government_number"]}</td>";
                $output1 .= "</tr>\n";
                $outputCSV .= "{$row1["Brand"]}, {$row1["Model"]}, {$row1["Release_Year"]}, {$row1["Government_number"]})\n";
                $outputXML .= "<brand>{$row1["Brand"]}</brand>\n";
                $outputXML .= "<model>{$row1["Model"]}</model>\n";
                $outputXML .= "<release_year>{$row1["Release_Year"]}</release_year>\n";
                $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";
                
                $outputXML .= "</vehicle>\n";
           
        } // end while

        if ($iFlag == 1) {
            $output .= $output1;
            print $output;
        } // end if

        $_SESSION["outputCSV"] = $outputCSV;
        $_SESSION["outputXML"] = $outputXML;

    } // end inner if

    else if ( empty($plate) && (  !(empty($from))  &&  !(empty($to))  )  ){

            $sql = "SELECT * FROM entry_monitoring";         
            $result = mysqli_query($link, $sql);  
                       
            table_header();

            $outputCSV = "";
            $outputXML = "";

            $outputXML .= "<?xml version = \"1.0\">\n";

            while ($row = mysqli_fetch_assoc($result)) {

                   $plate = $row["Government_number"];
              
                   $sql1 = "SELECT * FROM entry_monitoring WHERE Government_number = \"$plate\" AND Time_Of_Entry > \"$from\" ORDER BY Time_Of_Entry ASC";
                   $result1 = mysqli_query($link, $sql1);
                                                
                   $sql2 = "SELECT * FROM check_out_monitoring WHERE Government_number = \"$plate\" AND Time_Check_Out < \"$to\" ORDER BY Time_Check_Out ASC";
                   $result2 = mysqli_query($link, $sql2);
                              
                   check_query($result1, $result2);

                   if ($shownPlates[$plate] == "") {
                       $shownPlates[$plate] = "n";
                   } // end if
                   else {
                       $shownPlates[$plate] = "y";
                   } // end else 

                   if ($shownPlates[$plate] == "y") {
                       continue;
                   } // end if

                   while ($row1 = mysqli_fetch_assoc($result1)){

                          if (!($row2 = mysqli_fetch_assoc($result2)) ){
                              continue;
                          } // end if
                          else {
                                                     
                               $outputXML .= "<vehicle>\n";
                               $iFlag = 1;

                               $output1 .= "<tr>\n";
                               $output1 .= "<td>{$row1["Time_Of_Entry"]}</td>";
                               $outputCSV .= "({$row1["Time_Of_Entry"]},";
                               $outputXML .= "<time_of_entry>{$row1["Time_Of_Entry"]}</time_of_entry>\n";

                               $timeOfEntry = $row1["Time_Of_Entry"];
                               $timeCheckOut = $row2["Time_Check_Out"];
                               $checkOut = strtotime($timeCheckOut);
                               $entry = strtotime($timeOfEntry);
                               $now = strtotime("now");

                               $output1 .= "<td>{$row2["Time_Check_Out"]}</td>";
                               $outputCSV .= "{$row2["Time_Check_Out"]},";
                               $timeResult = $checkOut - $entry; 
                               time_result($timeResult, 0);
                               time_result($timeResult, 1);
                               time_result($timeResult, 2);
                 
                               $output1 .= "<td>{$row1["Brand"]}</td><td>{$row1["Model"]}</td><td>{$row1["Release_Year"]}</td><td>{$row1["Government_number"]}</td>";
                               $output1 .= "</tr>\n";

                               $outputCSV .= "{$row1["Brand"]},{$row1["Model"]},{$row1["Release_Year"]},{$row1["Government_number"]})\n";
                               $outputXML .= "<brand>{$row1["Brand"]}</brand>\n";
                               $outputXML .= "<model>{$row1["Model"]}</model>\n";
                               $outputXML .= "<release_year>{$row1["Release_Year"]}</release_year>\n";
                               $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";                             
                        } // end else

                        $outputXML .= "</vehicle>\n";

                   } // end inner while 

            } // end outer while
    
    if ($iFlag == 1) {
        $output .= $output1;
        print $output;
    } // end if

    $_SESSION["outputCSV"] = $outputCSV;
    $_SESSION["outputXML"] = $outputXML;

    } // end else-if

    else if (  !(empty($plate)) && !(empty($from)) && !(empty($to))  ){

               $sql1 = "SELECT * FROM entry_monitoring WHERE Government_number = \"$plate\" AND Time_Of_Entry > \"$from\" ORDER BY Time_Of_Entry ASC";
               $result1 = mysqli_query($link, $sql1);
                                 
               $sql2 = "SELECT * FROM check_out_monitoring WHERE Government_number = \"$plate\" AND Time_Check_Out < \"$to\" ORDER BY Time_Check_Out ASC";
               $result2 = mysqli_query($link, $sql2);
                            
               check_query($result1, $result2);
               
               table_header();

               $outputCSV = "";
               $outputXML = "";
             
               $outputXML .= "<?xml version = \"1.0\">\n";

               while ($row1 = mysqli_fetch_assoc($result1)){

                      if (!($row2 = mysqli_fetch_assoc($result2)) ){
                            continue;
                      } // end if
                      else {                             
                            $outputXML .= "<vehicle>\n";                        
                            $iFlag = 1;

                            $output1 .= "<tr>\n";
                            $output1 .= "<td>{$row1["Time_Of_Entry"]}</td>";
                            $outputCSV .= "({$row1["Time_Of_Entry"]},";
                            $outputXML .= "<time_of_entry>{$row1["Time_Of_Entry"]}</time_of_entry>\n";

                            $timeOfEntry = $row1["Time_Of_Entry"];
                            $timeCheckOut = $row2["Time_Check_Out"];
                            $checkOut = strtotime($timeCheckOut);
                            $entry = strtotime($timeOfEntry);
                            $now = strtotime("now");

                            $output1 .= "<td>{$row2["Time_Check_Out"]}</td>";
                            $outputCSV .= "{$row2["Time_Check_Out"]},";
                            $outputXML .= "<time_check_out>{$row2["Time_Check_Out"]}</time_check_out>\n";

                            $timeResult = $checkOut - $entry; 
                            time_result($timeResult, 0);
                            time_result($timeResult, 1);
                            time_result($timeResult, 2);
             
                            $output1 .= "<td>{$row1["Brand"]}</td><td>{$row1["Model"]}</td><td>{$row1["Release_Year"]}</td><td>{$row1["Government_number"]}</td>";
                            $output1 .= "</tr>\n";
                            $outputCSV .= "{$row1["Brand"]},{$row1["Model"]},{$row1["Release_Year"]},{$row1["Government_number"]})\n";
                            $outputXML .= "<brand>{$row1["Brand"]}</brand>\n";
                            $outputXML .= "<model>{$row1["Model"]}</model>\n";
                            $outputXML .= "<release_year>{$row1["Release_Year"]}</release_year>\n";
                            $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";                
                        } // end else
                        
                        $outputXML .= "</vehicle>\n";

                  } // end while

    if ($iFlag == 1) {
        $output .= $output1;
        print $output;
    } // end if

    $_SESSION["outputCSV"] = $outputCSV;
    $_SESSION["outputXML"] = $outputXML;
               
    } // end else-if

    if ( $iFlag == 0) {
         print "<p style = \"color:red; font-size:25px\"> ������ �� ��� �� ���������� ������ ������ �� �������</p>";
    } // end if
    else {
         print "</table>";
    } // end else

} // end if

else if ( (empty($plate)) && (empty($from)) && (empty($to)) && $type == "" ){

        db_connect();

        $sql = "SELECT * FROM entry_monitoring";
        $result = mysqli_query($link, $sql);

        if ($result == false) {
            print("������ �������");
        } // end if

        table_header();

        $outputCSV = "";
        $outputXML = "";

        $outputXML .= "<?xml version = \"1.0\">\n";

        while ($row = mysqli_fetch_assoc($result)) {

               $iFlag = 1;

               $plate = $row["Government_number"];
              
               $sql1 = "SELECT * FROM entry_monitoring WHERE Government_number = \"$plate\" ORDER BY Time_Of_Entry ASC";
               $result1 = mysqli_query($link, $sql1);
                                                 
               $sql2 = "SELECT * FROM check_out_monitoring WHERE Government_number = \"$plate\" ORDER BY Time_Check_Out ASC";
               $result2 = mysqli_query($link, $sql2);   
         
               check_query($result1, result2);

               if ($shownPlates[$plate] == "") {
                   $shownPlates[$plate] = "n";
               } // end if
               else {
                   $shownPlates[$plate] = "y";
               } // end else 

               if ($shownPlates[$plate] == "y") {
                   continue;
               } // end if

               $outputXML .= "<vehicle>\n";

               while ($row1 = mysqli_fetch_assoc($result1)){
                     
                      if (!($row2 = mysqli_fetch_assoc($result2)) ){                                                      
                            $output1 .= "<tr>\n";
                            $output1 .= "<td>{$row1["Time_Of_Entry"]}</td>";
                            $outputCSV .= "({$row1["Time_Of_Entry"]},";
                            $outputXML .= "<time_of_entry>{$row1["Time_Of_Entry"]}</time_of_entry>\n";

                            $timeOfEntry = $row1["Time_Of_Entry"];
                            $entry = strtotime($timeOfEntry);
                            $now = strtotime("now");                          
                            $output1 .= "<td style = \" color:red \">�� �� ����������</td>";
                            $outputCSV .= "�� �� ����������,";
                            $outputXML .= "<time_check_out>�� �� ����������</time_check_out>\n";

                            $timeResult = $now - $entry; 
                            time_result($timeResult, 0);
                            time_result($timeResult, 1);
                            time_result($timeResult, 2);
                         
                            if ($row1["Brand"] == "" && $row1["Model"] == "" && $row1["Release_Year"] == "") {
                                $output1 .= "<td colspan = \"3\" style = \" color:red \">����� �� ���������!!!</td>";
                                $output1 .= "<td>{$row1["Government_number"]}</td>";
                                $outputCSV .= "����� �� ���������!!!,";
                                $outputCSV .= "{$row1["Government_number"]})\n";
                                $outputXML .= "<plate-info>����� �� ���������!!!</plate-info>\n";
                                $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";
                            } // end if 
                            else {
                                $output1 .= "<td>{$row1["Brand"]}</td><td>{$row1["Model"]}</td><td>{$row1["Release_Year"]}</td><td>{$row1["Government_number"]}</td>";
                                $output1 .= "</tr>\n";
                                $outputCSV .= "{$row1["Brand"]},{$row1["Model"]},{$row1["Release_Year"]},{$row1["Government_number"]})\n";
                                $outputXML .= "<brand>{$row1["Brand"]}</brand>\n";
                                $outputXML .= "<model>{$row1["Model"]}</model>\n";
                                $outputXML .= "<release_year>{$row1["Release_Year"]}</release_year>\n";
                                $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";
                            } // end else

                       } // end if
                       else { 
                            $output1 .= "<tr>\n";
                            $output1 .= "<td>{$row1["Time_Of_Entry"]}</td>";
                            $outputCSV .= "({$row1["Time_Of_Entry"]},";
                            $outputXML .= "<time_of_entry>{$row1["Time_Of_Entry"]}</time_of_entry>\n";

                            $timeOfEntry = $row1["Time_Of_Entry"];
                            $timeCheckOut = $row2["Time_Check_Out"];
                            $checkOut = strtotime($timeCheckOut);
                            $entry = strtotime($timeOfEntry);
                            $now = strtotime("now");
                            $output1 .= "<td>{$row2["Time_Check_Out"]}</td>";
                            $outputCSV .= "{$row2["Time_Check_Out"]},";
                            $outputXML .= "<time_check_out>{$row2["Time_Check_Out"]}</time_check_out>\n";

                            $timeResult = $checkOut - $entry; 
                            time_result($timeResult, 0);
                            time_result($timeResult, 1);
                            time_result($timeResult, 2);
               
                            if ($row1["Brand"] == "" && $row1["Model"] == "" && $row1["Release_Year"] == "") {
                               $output1 .= "<td colspan = \"3\" style = \" color:red \">����� �� ���������!!!</td>";
                               $output1 .= "<td>{$row1["Government_number"]}</td>";
                               $outputCSV .= "����� �� ���������!!!,";
                               $outputCSV .= "{$row1["Government_number"]})\n";
                               $outputXML .= "<plate-info>����� �� ���������!!!</plate-info>\n";
                               $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";
                            } // end if 
                            else {
                               $output1 .= "<td>{$row1["Brand"]}</td><td>{$row1["Model"]}</td><td>{$row1["Release_Year"]}</td><td>{$row1["Government_number"]}</td>";
                               $output1 .= "</tr>\n";
                               $outputCSV .= "{$row1["Brand"]},{$row1["Model"]},{$row1["Release_Year"]},{$row1["Government_number"]})\n";
                               $outputXML .= "<brand>{$row1["Brand"]}</brand>\n";
                               $outputXML .= "<model>{$row1["Model"]}</model>\n";
                               $outputXML .= "<release_year>{$row1["Release_Year"]}</release_year>\n";
                               $outputXML .= "<government_number>{$row1["Government_number"]}</government_number>\n";
                            } // end inner else
                       } // end outer else

                  $outputXML .= "</vehicle>\n";

               } // end inner while

        } // end outer while

        if ($iFlag == 1) {
            $output .= $output1;
            print $output;
        } // end if

        $_SESSION["outputCSV"] = $outputCSV;
        $_SESSION["outputXML"] = $outputXML;
               
        if ( $iFlag == 0) {
             print "<p style = \"color:red; font-size:25px\"> ������ �� ��� �� ���������� �� ���� �� �������� �����-���� ��</p>";
        } // end if
        else  {
             print "</table>";
        } // end else

} // end else-if

if ($iFlag == 1 ) {   
    print "<details title = \" �������, ����� ������� ������ ��� ����������� ������ � ������� ���� �/��� ��� ����� ��� ���. �������: c:\\test\\test.csv, test.xml\">";
    print "<summary id = \"download\" style = \"font-size: 20px\">�������</summary>";
    print "<form method = \"post\">";
    print "<input type = \"radio\" id = \"download\" name = \"type\" value = \"csv\" /><span id = \"download\">CSV</span>";
    print "<input type = \"radio\" id = \"download\" name = \"type\" value = \"xml\" /><span id = \"download\">XML</span><br />";
    print "<input type = \"submit\" id = \"download\" name = \"\" value = \"�������\" /><br />";
    print "<input type = \"text\" cols = \"3\"  id = \"download\" name = \"path\" size = \"10\" value = \"\" />";
    print "</form>";
    print "</details";
} // end if

if (($type == "csv") && (  empty($plate) && empty($from) && empty($to))) {

     file_write($outputCSV);

} // end if

else if (($type == "xml") && (  empty($plate) && empty($from) && empty($to))){

     file_write($outputXML);

} // end if


/***************************************** FUNCTIONS *******************************************/

function check_input () {

         global $from, $plate, $to;

         if (  (  !(empty($plate))  || (   !(empty($from))  &&  !(empty($to))  )  )||  (  !(empty($plate)) && !(empty($from)) && !(empty($to))  )     ) {

              $itWorks1 = 1;
              $itWorks2 = 1;
         
              if ( !(empty($plate)) )  {
                   $itWorks1 = preg_match("/^[A-Za-zA-��-�0-9]{1,9}$/", $plate);
              } // end inner if

              if ( !(empty($from)) || !(empty($to)) ) {
                   $itWorks2 = ( (preg_match("/^\d{4}-(([0][0-9])|([1][0-2]))-(([0-2][0-9])|([3][0-1])) (([0,1][0-9])|([2][0-3])):[0-5][0-9]:[0-5][0-9]$/", $from) ) && (preg_match("/^\d{4}-(([0][0-9])|([1][0-2]))-(([0-2][0-9])|([3][0-1])) (([0,1][0-9])|([2][0-3])):[0-5][0-9]:[0-5][0-9]$/", $to) ) );
              } // end inner if

              if  ($itWorks1  == false || $itWorks2 == false) {
                   print "<p style = \"color:red; font-size:25px\"> ������������ ���� ������!!!</p>";
                   exit;       
              } // end inner if

         } // end outer if

} // end check_input

function check_query($result1, $result2) {
                    
                     if ($result1 == false) {
                         print("������ �������");
                     } // end if 
                
                     if ($result2 == false) {
                         print("������ �������");
                     } // end if 

} // end check_query

function db_connect() {

         global $iFlag, $link;
    
         $iFlag = 0;
         $link = mysqli_connect("127.0.0.1", "root", "", "base_numbers", $port = 3306);

         if ($link == false){
             print("���������� ������������ � MySQL, ������ " . mysqli_connect_error());    
         } // end inner if

} // end db_connect

function file_write($output) {

         global $path;

         $fp = fopen($path, "w");

         if (!$fp || $output == "") {
             print "<p style = \"color:red; font-size:25px\"> ������ ��� ���������� ��� ��������� ����������!!! </p>";
         } // end if 
         else {
             fputs ($fp, $output);
             fclose ($fp);     
             print "<p style = \"color:green; font-size:25px\"> ���������� ������� �������</p>";
         } // end else

} // end file_write

function time_result($timeResult, $param = 0) {

                     global $output1, $outputCSV, $outputXML;

                     $days = $timeResult / (3600  * 24);
                     $days = (int) $days;
                     $timeResult -= (3600 * 24 ) * $days; 
                     $hours = $timeResult / 3600;
                     $hours = (int)$hours; 
                     $timeResult -= 3600 * $hours;
                     $minutes = $timeResult / 60;
                     $minutes = (int) $minutes;
                     $timeResult -= 60 * $minutes;
                     if ($param == 0) {
                         $output1 .= "<td>$days �. $hours �. $minutes �. $timeResult �.</td>";
                     } // end if
                     else if ($param == 1){
                         $outputCSV .= "$days �. $hours �. $minutes �. $timeResult �., ";
                     } // end else if
                     else {
                         $outputXML .= "<length>$days �. $hours �. $minutes �. $timeResult �.</length>\n";
                     } // end else
} // end time_result

function table_header() {

         global $output;

$output .= "<table border = \"0\" id = \"info-window\">\n";

$output .= <<<HERE
          <tr>
           <td>
             �����
           </td>
           <td>
             �����
           </td>
           <td>
             ����.
           </td>
           <td>
             �����
           </td>
           <td>
             ������
           </td>
           <td>
             ��� �������
           </td>
           <td>
             ��c.�����
          </td>
         </tr>
HERE;

} // end table_header

function session_set() {
         
         global $outputCSV, $outputXML;

         if (isset($_SESSION["outputCSV"])){
             $outputCSV = $_SESSION["outputCSV"];
         } // end if

         if (isset($_SESSION["outputXML"])){
             $outputXML = $_SESSION["outputXML"];
         } // end if
         
} // end session_set

/*************************************** FUNCTIONS END *****************************************/

?>

<!--- DATA OUTPUT FIELD AND EXTRA FIELD END --->

</body><!--- BODY ENDS --->

</html><!--- HTML ENDS --->
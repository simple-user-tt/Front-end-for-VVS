<!DOCTYPE html><!--- HTML STARTS --->

<html lang="ru">

<head><!--- HEAD STARTS --->
    
    <meta name = "author" content = "Alex" />
    <meta name = "keywords" content = "Автомобили, видео, ИТ, распознавание, ТС, транспортные средства, IT, HTML, PC, PHP, programs, programming, recognition, vehicles, Windows" />
    <meta name = "description" content = "Vehicle Verification System" />
    <meta charset = "UTF-8" />
    <link rel = "stylesheet" href = "style.css" />
    <title>Vehicle Verification System - Мониторинг</title>

<script>
function help(){

         var helpWindow = window.open("help.php", "help", "height = 800, width = 600");
         helpWindow.focus();

} // end help
</script>

</head><!--- HEAD ENDS --->

<body><!--- BODY STARTS --->

<!------ UNORDERED LISTS ------>

  <ul>
      <li id = "monitor-mon" title = "Мониторинг">
        <a href = "monitor.php">МОНИТОРИНГ</a>
      </li>
  </ul>
  <ul>
      <li id = "history-mon" title = "История">
          <a href = "history.php">ИСТОРИЯ</a>
      </li>
  </ul>
  <ul>
      <li id = "help-button" title = "Справка">
         <input type = "button" onClick = "help()" value = "?" />
      </li>
  </ul>

<!---- END OF UNORDERED LISTS ---->

<!---- HEADER AND PARAGRAPH ---->

<p>
  <div id ="now">
     Сейчас на территории
  </div>
</p>

<!--- HEADER AND PARAGRAPH END --->

<!--- DATA OUTPUT FIELD --->

<?php

db_connect();

$sql = "SELECT * FROM entry_monitoring";
$result = mysqli_query($link, $sql);

if ($result == false) {
    print("Ошибка запроса");
} // end if 

table_header();

       $iFlag = 0;

       while ($row = mysqli_fetch_assoc($result)){

              $plate = $row["Government_number"];

              $sql = "SELECT * FROM entry_monitoring WHERE Government_number = \"$plate\" ";
              $result1 = mysqli_query($link, $sql);
               
              $sql = "SELECT * FROM check_out_monitoring WHERE Government_number = \"$plate\" ";
              $result2 = mysqli_query($link, $sql);

              check_query($result1, $result2); 

              $iCounter1 = 0;
              $iCounter2 = 0;

              while ($row1 = mysqli_fetch_assoc($result1)){ 
                    
                     $iCounter1++;

                     $brand = $row1["Brand"];
                     $plate = $row1["Government_number"];
                     $model = $row1["Model"];
                     $releaseYear = $row1["Release_Year"];
                     $timeOfEntry = $row1["Time_Of_Entry"];                                    
                         
              } // end inner while

              while ($row2 = mysqli_fetch_assoc($result2)){ 
                     $iCounter2++;
              } // end inner while

              if ($shownPlates[$plate] == "") {
                  $shownPlates[$plate] = "n";
              } // end if
              else {
                  $shownPlates[$plate] = "y";
              } // end else 

              if ( $iCounter1 == $iCounter2 || $shownPlates[$plate] == "y" ) {
                  continue;
              } // end if

              $output1 .= "<tr>\n";

              if ($brand == "" && $model == "" && $releaseYear == "") {

                  $iFlag = 1;
                  $output1 .= "<td colspan = \"3\" style = \" color:red \">НОМЕР НЕ РАСПОЗНАН!!!</td>";
                  $output1 .= "<td>$plate</td>\n";
                  $output1 .= "<td>$timeOfEntry</td>\n";
                  
                  $entry = $timeOfEntry;             
                  $now = strtotime("now");
                  $entryDT = strtotime($entry);
                  $timeResult = $now - $entryDT;                   
                  time_result($timeResult);
              } // end if
     
              else {

                  $iFlag = 1;
                  
                  $output1 .= "<td>$brand</td>\n";                                
                  $output1 .= "<td>$model</td>\n";
                  $output1 .= "<td>$releaseYear</td>\n";
                  $output1 .= "<td>$plate</td>\n";
                  $output1 .= "<td>$timeOfEntry</td>\n";
                   
                  $entry = $timeOfEntry;
                  $now = strtotime("now");
                  $entryDT = strtotime($entry);
                  $timeResult = $now - $entryDT; 
                  time_result($timeResult);
                 
               } // end else 
               
               $output1 .= "</tr>\n";
         
        } // end outer while 

$output1 .= "</table>";

if ($iFlag == 1) {
    $output .= $output1;
    print $output;
} // end if

if ( $iFlag == 0 )  {
       print "<p style = \"color:red; font-size:25px; margin-left: 18%\"> В данный момент на территории нет автотранспорта</p>";
} // end if


/***************************************** FUNCTIONS *******************************************/


function check_query($result1, $result2) {
                    
                     if ($result1 == false) {
                         print("Ошибка запроса");
                     } // end if 
                
                     if ($result2 == false) {
                         print("Ошибка запроса");
                     } // end if 

} // end check_query

function db_connect() {

         global $iFlag, $link;
    
         $iFlag = 0;
         $link = mysqli_connect("127.0.0.1", "root", "", "base_numbers", $port = 3306);

         if ($link == false){
             print("Невозможно подключиться к MySQL, ошибка " . mysqli_connect_error());    
         } // end inner if

} // end db_connect

function table_header() {

         global $output;

$output .= "<table border = \"0\" id = \"info-window\">\n";

$output .= <<<HERE
          <tr>
           <td>
             Марка
           </td>
           <td>
             Модель
           </td>
           <td>
             Год вып.
           </td>
           <td>
             Гос. номер
           </td>
           <td>
             Въезд
           </td>
           <td>
             Длит.
           </td>         
          </tr>
HERE;

} // end table_header

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
                         $output1 .= "<td>$days д. $hours ч. $minutes м. $timeResult с.</td>";
                     } // end if
                     else if ($param == 1){
                         $outputCSV .= "$days д. $hours ч. $minutes м. $timeResult с., ";
                     } // end else if
                     else {
                         $outputXML .= "<length>$days д. $hours ч. $minutes м. $timeResult с.</length>\n";
                     } // end else

} // end time_result


/*************************************** FUNCTIONS END *****************************************/

?>


<!--- DATA OUTPUT FIELD ENDS --->


</body><!--- BODY ENDS --->
</html><!--- HTML ENDS --->
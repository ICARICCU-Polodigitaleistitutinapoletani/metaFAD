<?php
$filename="sheet.xls";
header("Content-Type: application/octet-stream");
header ("Content-Disposition: inline; filename=$filename");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang=it><head>
    <title>Titolo</title></head>
<body>
<table border="1">
    <?
    for ($i=1;$i < 11; $i++)
    {
        echo "<tr>";
        for ($j=1; $j<11;$j++)
        {
            $a = $i * $j;
            echo "<td>$a</td>";
        }
        echo "</tr>";
    }
    ?>
</table>
</body></html>
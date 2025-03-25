<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .mauxanh{
            color:blue;
        }
        .maudo{
            color:red;     
        }
    </style>
</head>
<body>
    <form action = "<?php echo $_SEVER['PHP_SELF']?>" method = "post">
        <input type ="number" name ="a" id="a">
        <input type ="number" name ="b" id="b">
        <input type ="submit" name ="hienthi" value="Show kết quả">
</form>
         <h2> Mảng vừa nhập :<span class ="mauxanh">:[1,2,3,4]</span></h2>
         <h2> Tổng mảng :<span class ="maudo">:1+2+3=6</span></h2>
</body>
</html>
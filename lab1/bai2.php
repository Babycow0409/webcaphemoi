<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Reset CSS */
/* Reset CSS */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(to right, #4A90E2, #9013FE);
    padding: 20px;
}

/* Form container */
form {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    width: 350px;
    text-align: center;
}

/* Heading */
.heading {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

/* Form group */
.flex {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

/* Labels */
label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #555;
    text-align: left;
}

/* Inputs */
input[type="text"] {
    padding: 10px;
    border: 2px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    transition: border 0.3s ease-in-out;
}

input[type="text"]:focus {
    border-color: #4A90E2;
    outline: none;
}

/* Submit Button */
input[type="submit"] {
    width: 100%;
    background: #4A90E2;
    color: white;
    border: none;
    padding: 12px;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease-in-out;
}

input[type="submit"]:hover {
    background: #357ABD;
}

/* Kết quả */
.form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    width: 350px;
    margin-top: 20px;
    text-align: center;
}

.form h2 {
    color: #333;
    margin-bottom: 15px;
}

.form span {
    font-size: 16px;
    font-weight: bold;
    color: #4A90E2;
}

    </style>
    <script>
      
    </script>
</head>
<body>
    <form class = "form flex" action = "<?php echo $_SERVER['PHP_SELF']?>"method ="post">
        <div class ="heading">Giải phương trình bậc nhất </div>
        <div class = "flex">
            <label for="color">Nhập a:</label>
            <input name ="a" type="text">
        </div>
        <div class ="flex">
            <label for="font">Nhập b:</label>
            <input name ="b" type ="text">
        </div>
            <input name ="giaipt" type="submit"  value="Giải phương trình" >
    </form>
    <?php
    if((isset($_POST['giaipt']))&&($_POST['giaipt'])){

        // lấy dư liệu từ
        $a=$_POST['a'];
        $b=$_POST['b'];
        if ($a == 0) {
            if ($b == 0) {
                $nghiem = "Phương trình có vô số nghiệm.";
            } else {
                $nghiem = "Phương trình vô nghiệm.";
            }
        } else {
            $nghiem = "PT có một nghiệm x = " . (-$b / $a);
        }
        
        // Tạo phương trình dạng ax + b = 0
        $pt = $a . "x + " . $b . " = 0";
       
        $kq ='<div class ="form">
           <h2>Kết quả Giải phương trình</h2>
        <div>
           <span>PT đã nhập:'.$pt.'</span>
        </div>
        <div>
           <span>  '.$nghiem.' </span>
        </div>
        </div>';
        echo $kq;
    }
   ?>
 
</body>
</html>
<?php
    session_start();
    

    if(!isset($_SESSION["questionindex"])){
        die("error");
    }
    // for DB Connect
    include_once("sql_lib.php");
    include("/var/www/captcha_key.php");
    
    require_once("recaptchalib.php");
    use Phelium\Component\reCAPTCHA;

    $reCAPTCHA = new reCAPTCHA($captcha_public, $captcha_private);

    $reCAPTCHA->setTheme("light");
    $reCAPTCHA->setLanguage("ko");

    $ques_index = $_SESSION["questionindex"];
    
    $input_answer = $_POST["question"];
    $body = trim($_POST["body"]);
    $agree = $_POST["agree"];

    if($agree !== "on"){
        echo "<script>alert('약관에 동의해주세요!'); history.back();</script>";
    }else{
        if(mb_strlen($body, "UTF-8") < 10){
            echo "<script>alert('최소 10자 이상 등록해주세요..'); history.back();</script>";
        }else{
            if (!$reCAPTCHA->isValid($_POST['g-recaptcha-response'])) {
                echo "<script>alert('Recaptcha 가 올바르지 않습니다!\\n".$resp->error."'); history.back();</script>";
            } else {
                $ques_index = mysqli_real_escape_string($conn, stripslashes($ques_index));
                $res = mysqli_query($conn, "SELECT answer FROM BAMBOO_QUESTIONS WHERE UID=".$ques_index);
                
                $answer = (string)mysqli_fetch_array($res)["answer"];
        
                if($input_answer !== $answer){
                    echo "<script>alert('질문에 올바른 대답을 해주세요!'); history.back();</script>";
                }else{
                    $body = mysqli_real_escape_string($conn, stripslashes(trim($body)));
                    $ip = '';
                    if (isset($_SERVER['HTTP_CLIENT_IP']))
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    else if(isset($_SERVER['HTTP_X_FORWARDED']))
                        $ip = $_SERVER['HTTP_X_FORWARDED'];
                    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
                        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
                    else if(isset($_SERVER['HTTP_FORWARDED']))
                        $ip = $_SERVER['HTTP_FORWARDED'];
                    else if(isset($_SERVER['REMOTE_ADDR']))
                        $ip = $_SERVER['REMOTE_ADDR'];
                    else
                        $ip = 'UNKNOWN';
                    $time = date("Y-m-d H:i:s");
        
                    if(mysqli_query($conn, "INSERT INTO BAMBOO_POSTS (IP, TIME, BODY) VALUES ('$ip', '$time', '$body')")){
                        echo "<script>alert('정상적으로 게시되었습니다.\\n관리자가 수락한 이후 게시됩니다!'); location.href='./';</script>";
                    }else{
                        echo "<script>alert('오류가 발생하였습니다.".mysqli_error($conn)."'); location.href='./';</script>";
                    }
                }
            }
        }
    }
    mysqli_close();
?>
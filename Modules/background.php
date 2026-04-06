<?php
    $imagePrefix = '../';
    if (strpos($_SERVER['REQUEST_URI'], '/HR/Employee_manage/') !== false) {
        $imagePrefix = '../../';
    }
    
    $bg_source = "";
    if(!isset($_SESSION['bg_source'])){
        $bg_source = $imagePrefix . "Images/bgimg.jpg";
    } else {
        switch($_SESSION['bg_source']){
            case "":
                $bg_source = $imagePrefix . "Images/bgimg.jpg";
                break;
            case "HR":
                $bg_source = $imagePrefix . "Images/bgimg.jpg";
                break;
            case "Emp":
                $bg_source = $imagePrefix . "Images/bgimg.jpg";
                break;
            default:
                $bg_source = $imagePrefix . "Images/bgimg.jpg";
        };
    };
?>

<!-- Background -->
<div class="bg-container">
    <img src="<?php echo $bg_source; ?>" class="bg-image">
    <div class="overlay"></div>
</div>
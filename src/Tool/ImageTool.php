<?php

namespace Api\Tool;

class ImageTool
{
    private $path=''; 
    /** 
    * 构造方法 
    * @param unknown $path 
    */
    function __construct($path=''){ 
        if (!empty($path)) { 
          $this->path=$path; 
        } 
    } 
    /** 
    +----------------------------------------------- 
    * 等比缩放函数 
    +----------------------------------------------- 
    * @param unknown $name 需处理图片的名称 
    * @param unknown $width 缩放后的宽度 
    * @param unknown $height 缩放后的高度 
    * @param string $thumb_prixs 缩放后的前缀名 
    * @return mixed $newname 返回的缩放后的文件名 
    */
    function thumb($name,$width,$height,$thumb_prixs='th_'){ 
        //获取图片信息 
        $Info=$this->ImageInfo($name); //图片的宽度，高度，类型 
        //获取图片资源，各种类型的图片都可以创建资源，jpg,gif,png 
        $imagres=$this->Img_resouce($name, $Info); 
        //获取计算图片等比例之后的大小， 
        $size=$this->getNewSize($name,$width,$height,$Info); 
        //获取新的图片资源，处理透明背景 
        $newimg=$this->getImage($imagres,$size,$Info); 
        //另存为一个新的图片，返回新的缩放后的图片名称 
        $newname=$this->SaveNewImage($newimg,$thumb_prixs.$name,$Info); 
        return $newname;  
    } 

    /** 
    +----------------------------------------------------------------------- 
    * 水印标记函数 
    +----------------------------------------------------------------------- 
    * @param unknown $backname  背景文件名 
    * @param unknown $watername 水印文件名 
    * @param number $waterpos  水印位置 
    * @param string $wa_prixs  水印前缀名 
    * @return boolean 
    */
    function waterMark($backname,$watername,$waterpos=0,$wa_prixs='wa_') { 
      
        if (file_exists($this->path.$backname) && file_exists($this->path.$watername)) { 
            
          $backinfo=$this->ImageInfo($backname); 
            
          $waterinfo=$this->ImageInfo($watername); 
            
          if(!$pos=$this->getPos($backinfo, $waterinfo, $waterpos)){ 
            echo "水印图片不应该比背景图片小"; 
            return false; 
          } 
           $backimg=$this->Img_resouce($backname, $backinfo); 
             
           $waterimg=$this->Img_resouce($watername, $waterinfo); 
           var_dump($backimg);exit(); 
           $backimg=$this->CopyImage($backimg, $waterimg, $pos, $waterinfo); 
             
           $this->SaveNewImage($backimg, $wa_prixs.$backname, $backinfo); 
        }else{ 
            
          echo "图片或水印不存在"; 
          return false; 
        } 
    } 
    /** 
    +----------------------------------------------------------------------- 
    * 获取图片信息函数 
    +----------------------------------------------------------------------- 
    * @param unknown $name 
    * @return unknown 
    */

    private function ImageInfo($img) { 
        $imageInfo=getimagesize($img); 
        if ($imageInfo!==false) { 
          $imageType=strtolower(substr(image_type_to_extension($imageInfo[2]),1)); 
          $imageSize=filesize($img); 
          $Info=array( 
            "width" => $imageInfo[0], 
            "height" => $imageInfo[1], 
            "type" => $imageType, 
            "size" => $imageSize, 
            "mime" => $imageInfo['mime'] 
          ); 
          return $Info; 
        }else{ 
          return false; 
        }   
    } 


    /** 
    +-------------------------------------------------------- 
    * 创建原图像格式函数 
    +-------------------------------------------------------- 
    * @param unknown $name 
    * @param unknown $imaginfo 
    * @return boolean|resource 
    */
    private function Img_resouce($name,$imageinfo){ 
        $iamgeres=$this->path.$name; 
        //var_dump($iamgeres);exit(); 
         switch ($imageinfo['type']) { 
          case 'gif': 
           $img=imagecreatefromgif($name); 
             break; 
          case 'jpg': 
           $img=imagecreatefromjpeg($name); 
             break; 
          case 'png': 
           $img=imagecreatefrompng($name); 
             break;             
         } 
         return $img; 
    } 
    /** 
    +-------------------------------------------------- 
    * 获取等比缩放尺寸函数 
    +-------------------------------------------------- 
    * @param unknown $name 
    * @param unknown $width 
    * @param unknown $height 
    * @param unknown $imaginfo 
    * @return Ambigous <unknown, number> 
    */
    private function getNewSize($name,$width,$height,$imaginfo){ 
       $size['width']=$imaginfo['width']; 
       $size['height']=$imaginfo['height']; 
        if ($width<$imaginfo['width']) { 
          $size['width']=$width; 
        } 
        if ($height<$imaginfo['height']) { 
          $size['height']=$height; 
        } 
        //图像等比例缩放算法 
        if ($imaginfo['width']*$size['width']>$imaginfo['height']*$size['height']) { 
          $size['height']=round($imaginfo['height']*$size['width']/$imaginfo['width']); 
        }else{ 
          $size['width']=round($imaginfo['width']*$size['height']/$imaginfo['height']); 
        } 
        return $size;  
        } 
        private function getImage($imageres,$size,$imageinfo){ 
        //新建一个真彩色图像 
        $newimg=imagecreatetruecolor($size['width'], $size['height']); 
        //将某个颜色定义为透明色 
        $otsc=imagecolortransparent($imageres); 
        //获取图像的调色板的颜色数目 
        if ($otsc>=0&&$otsc<=imagecolorstotal($imageres)) { 
          //取得某索引的颜色 
          $stran=imagecolorsforindex($imageres, $otsc); 
          //为图像分配颜色 
          $newt=imagecolorallocate($imageres, $stran['red'], $stran['green'], $stran['blue']); 
          //区域填充函数 
          imagefill($newimg, 0, 0, $newt); 
          //为图像定义透明色 
          imagecolortransparent($newimg,$newt);     
        } 
        imagecopyresized($newimg, $imageres, 0, 0, 0, 0, $size['width'], $size['height'], $imageinfo['width'], $imageinfo['height']);   
          
        imagedestroy($imageres); 
          
        return $newimg;  
    } 
    /** 
    +---------------------------------------------- 
    *保存图像函数 
    +---------------------------------------------- 
    * @param unknown $newimg 
    * @param unknown $newname 
    * @param unknown $imageinfo 
    * @return unknown  
    */
    private function SaveNewImage($newimg,$newname,$imageinfo){ 
        switch ($imageinfo['type']){ 
          case 1://gif 
            $result=imagegif($newimg,$this->path.$newname); 
            break; 
          case 2://jpg 
            $result=imagejpeg($newimg,$this->path.$newname); 
            break; 
          case 3://png 
            $result=imagepng($newimg,$this->path.$newname); 
            break; 
        } 
        imagedestroy($newimg); 
        return $newname; 
    } 
     
    /** 
    +----------------------------------------------------------------- 
    * 获取水印位置函数 
    +----------------------------------------------------------------- 
    * @param unknown $backinfo  背景信息 
    * @param unknown $waterinfo  水印信息 
    * @param unknown $waterpos  水印位置 
    * @return boolean|multitype:number 返回坐标数组 
    */
    private function getPos($backinfo,$waterinfo,$waterpos) { 
      
        if ($backinfo['width']<$waterinfo['width']||$backinfo['height']<$waterinfo['height']) { 
          return false; 
        } 
        switch ($waterpos) { 
            
          case 1://左上角 
            $posX=0; 
            $posY=0; 
             break; 
          case 2://中上方 
            $posX=$backinfo['width']-$waterinfo['width']/2; 
            $posY=0; 
             break; 
          case 3://右上角 
            $posX=$backinfo['width']-$waterinfo['width']; 
            $posY=0; 
             break; 
          case 4://左中方 
            $posX=0; 
            $posY=$backinfo['height']-$waterinfo['height']/2; 
             break; 
          case 5://正中间 
            $posX=$backinfo['width']-$waterinfo['width']/2; 
            $posY=$backinfo['height']-$waterinfo['height']/2; 
             break; 
          case 6://右中方 
            $posX=$backinfo['width']-$waterinfo['width']; 
            $posY=$backinfo['height']-$waterinfo['height']/2; 
             break; 
          case 7://底部靠左 
            $posX=0; 
            $posY=$backinfo['height']-$waterinfo['height']; 
             break; 
          case 8://底部居中 
            $posX=$backinfo['width']-$waterinfo['width']/2; 
            $posY=$backinfo['height']-$waterinfo['height']; 
             break; 
          case 9://底部靠右 
            $posX=$backinfo['width']-$waterinfo['width']; 
            $posY=$backinfo['height']-$waterinfo['height']; 
             break; 
          case 0: 
          default : 
            $posX=rand(0,$backinfo['width']-$waterinfo['width']); 
            $posY=rand(0,$backinfo['height']-$waterinfo['height']); 
             break; 
        } 
        return array('posX'=>$posX,'posY'=>$posY); 
    } 
    /** 
    +------------------------------------------------------------------- 
    * 拷贝图像 
    +------------------------------------------------------------------- 
    * @param unknown $backimg   背景资源 
    * @param unknown $waterimg  水印资源 
    * @param unknown $pos     水印位置 
    * @param unknown $waterinfo  水印信息 
    * @return unknown 
    */
    private function CopyImage($backimg,$waterimg,$pos,$waterinfo) { 
        imagecopy($backimg, $waterimg, $pos['posX'], $pos['posY'], 0, 0, $waterinfo['width'], $waterinfo['height']); 
        imagedestroy($waterimg);     
         return $backimg; 
    } 
}


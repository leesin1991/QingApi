<?php

namespace Api\Controller\V1\Upload;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Index extends Controller
{

    public function imgUpload(Request $request, Response $response) 
    {
        $post = $this->getOauthRequest();
        if ($post) {
            if ($_FILES['image']) {
                $user_id = $this->getTokenUserId($post['access_token'], $post['client_id']);
                if ($user_id <= 0) {
                     return $this->jsonError($response,41001,'前先登录！');
                }
                $size = getimagesize($_FILES["image"]['tmp_name']);
                $imgTypeArr = [1 => 'GIF', 2 => 'JPG', 3 => 'PNG'];
                if ($size[2] != 1 && $size[2] != 2 && $size[2] != 3) {
                    return $this->jsonError($response,42007,'图片格式错误');
                }
                if ($_FILES["image"]['size'] > 2097152) {
                    return $this->jsonError($response,42008,'图片大小不能超过2M');
                }
                $rootDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/web/upload/";
                $savePath = date('Y') . '/' . date('md') . '/' . date('H');
                $saveDir = $rootDir . $savePath;
                $uploadName = $_FILES["image"]['name'];
                $uploadType = strstr($uploadName, '.');
                $saveName = uniqid() . $uploadType;
//                if(!is_dir($saveDir)){
//                    mkdir($saveDir,0777, true); 
//                    chmod($saveDir, 0777);
//                }
                mkpath($saveDir);
                $moveRes = move_uploaded_file($_FILES["image"]['tmp_name'], $saveDir . '/' . $saveName);
                if (!$moveRes) {
                    return $this->jsonError($response,42009,'上传失败');
                }
                $extension = strtolower($imgTypeArr[$size[2]]);
                $from = getHttpUserAgent();
                $attachData = [
                    'attach_id' => 0,
                    'uid' => $user_id,
                    'name' => $this->t($_FILES['image']['name']),
                    'type' => $_FILES['image']['type'],
                    'size' => $_FILES['image']['size'],
                    'extension' => $extension,
                    'width' => $size[0],
                    'height' => $size[1],
                    'save_path' => $savePath . '/',
                    'save_name' => $saveName,
                    'source' => $from,
                    'ctime' => time(),
                    'hash' => md5(uniqid())
                ];
                $saveRes = $this->db->lq_image()->insert($attachData);
                if ($saveRes) {
                    $insert_id = $this->db->el_image()->insert_id();
                    $data = ['img_id' => $insert_id];
                    return $this->jsonSuccess($response, $data);
                } else {
                    return $this->jsonError($response,20001,'保存失败');
                }
            } else {
                return $this->jsonError($response,42010,'请上传图片');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
    }



}

<?php

namespace Api\Controller\V1\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;

class Index extends Controller
{

    public function register(Request $request, Response $response)
    {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['username'] && $post['password'] && $post['repassword']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
                return $this->jsonError($response,40012,'手机格式错误');
            }
            $mobileCheck = $this->checkMobileIsExists($post['mobile']);
            if (!$mobileCheck) {
                return $this->jsonError($response,40011,'手机号码已经注册，请直接登陆');
            }
            $usernameValidate = validateUsername($post['username']);
            if (!$usernameValidate) {
                return $this->jsonError($response,40011,'用户名格式不合法');
            }
            $usernameCheck = $this->checkUsernameIsExists($post['username']);
            if (!$usernameCheck) {
                return $this->jsonError($response,40011,'该用户名已被注册，请重新输入');
            }
            $passwordValidate = validatePassword($post['password']);
            if (!$passwordValidate) {
                return $this->jsonError($response,40017,'密码长度应在6到16位');
            }
            if ($post['password'] != $post['repassword']) {
                return $this->jsonError($response,40016,'两次输入输入不一致，请重新输入');
            }

            $user['mtime'] = $user['ctime'] = time();
            $user['salt'] = mt_rand(1000, 9999);
            $user['password'] = md5($post['password'] . $user['salt']);
            $user['username'] = $post['username'];
            $user['mobile'] = $post['mobile'];
            $rs = $this->db->lq_user()->insert($user);
            $userRow = $this->db->lq_user()->where(['mobile' => $post['mobile'], 'is_del' => 0])->fetch();
            if ($rs) {
                $this->setTokenUserId($post['access_token'], $userRow['id']);
                return $this->jsonSuccess($response,null,'注册成功');
            } else {
                return $this->jsonError($response,40012,'注册失败');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
        return $return;
    }

    public function login(Request $request, Response $response)
    {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['password']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
                return $this->jsonError($response,40012,'手机格式错误');
            }
            $userRow = $this->db->lq_user()->where(['mobile' => $post['mobile']])->fetch();
            if ($userRow) {
                $password = md5($post['password'] . $userRow['salt']);
                if ($password == $userRow['password']) {
                    $this->setTokenUserId($post['access_token'], $userRow['id']);
                    return $this->jsonSuccess($response,null,'登陆成功');
                } else {
                    return $this->jsonError($response,40012,'密码错误');
                }
            } else {
                return $this->jsonError($response,40012,'手机号不正确或未注册');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
        return $return;
    }

    /*
     * 忘记密码
     */

    public function forget(Request $request, Response $response) {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['password'] && $post['vericode'] && $post['repassword']) {
            
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
                return $this->jsonError($response,40012,'手机格式错误');
            }
            $mobileCheck = $this->checkMobileIsExists($post['mobile']);
            if ($mobileCheck) {
                return $this->jsonError($response,40011,'手机号码不存在');
            }
            
            $passwordValidate = validatePassword($post['password']);
            if (!$passwordValidate) {
                return $this->jsonError($response,40017,'密码长度应在6到16位');
            }
            if ($post['password'] != $post['repassword']) {
                return $this->jsonError($response,40016,'两次输入输入不一致，请重新输入');
            }

            $vericodeCheck = $this->codeVerified($post['mobile'], $post['vericode'], 'forget');
            if (!$vericodeCheck) {
                return $this->jsonError($response,40013,'验证码错误请重新输入');
            }
            $where =  ['mobile' => $post['mobile'], 'status' => 0, 'is_del' => 0];
            $userRow = $this->db->lq_user()->where($where)->fetch();
            if ($userRow) {
                $data['password'] = md5($post['password'] . $userRow['salt']);
                $data['mtime'] = time();
                $rs = $this->db->lq_user()->where($where)->update($data);
                if ($rs) {
                    return $this->jsonSuccess($response,null,'密码重置成功');
                } else {
                    return $this->jsonError($response,20002,'修改失败');
                }
            } else {
                return $this->jsonError($response,41000,'该账号不存在或已锁定');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
    }

    /*
     * 用户登出
     */

    public function logout(Request $request, Response $response) {
        $post = $this->getOauthRequest();
        if($post['access_token']){
            $rs = $this->delTokenUserId($post['access_token'], $post['user_id']);
            if ($rs) {
                return $this->jsonSuccess($response,null,'登出成功');
            }else{
                return $this->jsonError($response,40012,'系统繁忙');
            }
            
        }else{
            return $this->jsonError($response,40012,'请求参数错误');
        } 
    }


}

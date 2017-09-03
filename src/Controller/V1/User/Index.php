<?php

namespace Api\Controller\V1\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;

class Index extends Controller
{

    protected function register() {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['username'] && $post['password'] && $post['repassword']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
                return $this->jsonError($response,40012,'手机格式错误');
            }
            $mobileCheck = $this->checkMobileIsRegistered($post['mobile']);
            if (!$mobileCheck) {
                return $this->jsonError($response,40011,'手机号码已经注册，请直接登陆');
            }
            $usernameValidate = $this->validateUsername($post['username']);
            if (!$usernameValidate) {
                return $this->jsonError($response,40011,'用户名格式不合法');
            }
            $usernameCheck = $this->checkUsernameIsRegistered($post['username']);
            if (!$usernameCheck) {
                return $this->jsonError($response,40011,'该用户名已被注册，请重新输入');
            }
            $passwordValidate = $this->validatePassword($post['password']);
            if (!$passwordValidate) {
                return $this->jsonError($response,40017,'密码长度应在6到16位');
            }
            if ($post['password'] != $post['repassword']) {
                return $this->jsonError($response,40016,'两次输入输入不一致，请重新输入');
            }
            $client['id'] = 0;
            $client['mtime'] = $client['ctime'] = time();
            $client['login_salt'] = mt_rand(10000, 99999);
            $client['password'] = md5($post['password'] . $client['login_salt']);
            $client['username'] = $post['username'];
            $client['mobile'] = $post['mobile'];
            $rs = $this->db->el_client()->insert($client);
            $clientRow = $this->db->el_client()->where(['mobile' => $post['mobile'], 'is_del' => 0])->fetch();
            if ($rs) {
                $this->setTokenUserId($post['access_token'], $post['client_id'], $clientRow['id']);
                return $this->jsonSuccess($response,null,'注册成功');
            } else {
                return $this->jsonError($response,40012,'注册失败');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
        return $return;
    }

    /*
     * 用户登录
     */

    protected function login() {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['password']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
                return $this->jsonError($response,40012,'手机格式错误');
            }
            $clientRow = $this->app->no()->el_client()->where(array('mobile' => $post['mobile']))->fetch();
            if ($clientRow) {
                $password = md5($post['password'] . $clientRow['login_salt']);
                if ($password == $clientRow['password']) {
                    $this->app->setTokenUserId($post['access_token'], $post['client_id'], $clientRow['id']);
                    $return = ['status' => true, 'errno' => '0', 'message' => "登陆成功"];
                } else {
                    $return = ['status' => false, 'errno' => '40021', 'errmsg' => "密码错误"];
                }
            } else {
                $return = ['status' => false, 'errno' => '40019', 'errmsg' => "手机号不正确或未注册"];
            }
        } else {
            $return = array('status' => false, 'errno' => '40001', 'errmsg' => "请求参数无效");
        }
        return $return;
    }

    /*
     * 忘记密码
     */

    protected function forget() {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['password'] && $post['vericode'] && $post['repassword']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
                return $this->jsonError($response,40012,'手机格式错误');
            }
            $vericodeCheck = $this->app->codeVerified($post['mobile'], $post['vericode'], 'forget');
            if (!$vericodeCheck) {
                return array('status' => false, 'errno' => '40013', 'errmsg' => "验证码错误请重新输入");
            }
            $mobileCheck = $this->checkMobileIsRegistered($post['mobile']);
            if ($mobileCheck) {
                return array('status' => false, 'errno' => '40011', 'errmsg' => "该手机号未注册");
            }
            $passwordValidate = $this->validatePassword($post['password']);
            if (!$passwordValidate) {
                return array('status' => false, 'errno' => '40017', 'errmsg' => "密码长度应在6到16位");
            }
            if ($post['password'] != $post['repassword']) {
                return array('status' => false, 'errno' => '40016', 'errmsg' => "两次输入输入不一致，请重新输入");
            }
            $clientRow = $this->app->no()->el_client()->where(array('mobile' => $post['mobile'], 'status' => 0, 'is_del' => 0))->fetch();
            if ($clientRow) {
                $data['password'] = md5($post['password'] . $clientRow['login_salt']);
                $rs = $clientRow->update($data);
                if ($rs) {
                    $return = ['status' => true, 'errno' => '0', 'message' => '密码重置成功'];
                } else {
                    $return = ['status' => false, 'errno' => '20002', 'errmsg' => '修改失败'];
                }
            } else {
                $return = ['status' => false, 'errno' => '41000', 'errmsg' => '该账号已锁定'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 用户登出
     */

    protected function logout() {
        $post = $this->getOauthRequest();
        $this->app->delTokenUserId($post['access_token'], $post['client_id']);
        $return = ['status' => true, 'errno' => '0', 'message' => "登出成功"];
        return $return;
    }


}

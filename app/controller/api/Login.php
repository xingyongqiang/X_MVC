<?php

/**
 * swagger: api Login
 */
class Login extends Base
{
    /**
     * get: 测试接口
     * path: index
     * method: index
     */
    public function index()
    {
        echo 'hello Login!';
    }

    /**
     * post:获取验证码
     * path:post_code
     * method:post_code
     * param:phone - {tel} 用户手机号
     */
    public function post_code()
    {
        $phone = isset($_POST["phone"]) ? sql_format($_POST["phone"]) : "";//手机号
        if (!chk_phone($phone)) ajax_return(1, '手机号码不正确');
        ajax_return(1, '', $phone);
    }

    /**
     * delete: 测试接口
     * path: delete
     * method: delete
     */
    public function delete()
    {
        echo 'hello delete!';
    }

    /**
     * post: github 登录
     * path: github
     * method: github
     * param: code - {string} code码
     */
    public function github()
    {
        //https://github.com/login/oauth/authorize?client_id=12a7917183d9dfea3b80&redirect_uri=https://xcx.xingyongqiang.com/api/login/github//获取code
        //https://github.com/login/oauth/access_token?client_id=12a7917183d9dfea3b80&client_secret=976f0572cb955dc54c8565600aeb6534392cb1b5&code=${requestToken}
        //https://api.github.com/user?access_token=4dc43c2f43b773c327f97acf5dd66b147db9259c

        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (!$code) $this->error('参数有误，请重新登录');
        $url = 'https://github.com/login/oauth/access_token?client_id=12a7917183d9dfea3b80&client_secret=976f0572cb955dc54c8565600aeb6534392cb1b5&code=' . $code;
        $token = send_curl($url);
        $result = json_decode(send_curl('https://api.github.com/user?' . $token), true);

        $user['login'] = $result['login'];
        $user['node_id'] = $result['node_id'];
        $user['headimg'] = $result['avatar_url'];
        $user['nickname'] = $result['name'];
        $user['email'] = $result['email'];

        ajax_return(1, '登录成功', $user);
    }

    /**
     * post: 微博登录
     * path: weibo
     * method: weibo
     * param: code - {string} code码
     */
    public function weibo()
    {
        //https://api.weibo.com/oauth2/authorize?client_id=3178230374&redirect_uri=https://xcx.xingyongqiang.com/api/login/weibo&response_type=code&forcelogin=false

        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (!$code) $this->error('参数有误，请重新登录');

        $data['code'] = $code;
        $data['client_id'] = '3178230374';
        $data['client_secret'] = '1940925efa0e3caeff3e6ac038f74b0e';
        $data['grant_type'] = 'authorization_code';
        $data['redirect_uri'] = 'https://xcx.xingyongqiang.com/api/login/weibo';
        $token = send_post('https://api.weibo.com/oauth2/access_token', $data);

        $token = json_decode($token, true);
        $access_token = $token['access_token'];
        $uid = $token['uid'];

        $url = "https://api.weibo.com/2/users/show.json?access_token=$access_token&uid=$uid";
        $info = json_decode(send_curl($url), true);

        $user['id'] = $info['id'];
        $user['nickname'] = $info['screen_name'];
        $user['avatar_large'] = $info['avatar_large'];
        $user['province'] = $info['province'];
        $user['city'] = $info['city'];
        $user['description'] = $info['description'];

        ajax_return(1, '', $user);
    }
}
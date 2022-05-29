<?php

namespace App\Controllers;

class User extends BaseController
{
    public function Index() {

    }

    public function Login()
    {
        $request = \Config\Services::request();
        $username = $request->getPostGet('username', FILTER_SANITIZE_ADD_SLASHES);
        if (!$username) {
            echoJson(400, 'Bad Request', [], ['username' => 'The username field is required.'], '#1001');
            exit(0);
        }
        $username = trim($username);
        $password = $request->getPostGet('password', FILTER_SANITIZE_ADD_SLASHES);
        if (!$password) {
            echoJson(400, 'Bad Request', [], ['password' => 'The password field is required.'], '#1002');
            exit(0);
        }
        $password = trim($password);

        $db = db_connect('reader');
        $userModel = model('UserModel', true, $db);

        $user = $userModel->select(['id', 'password', 'nickname', 'status'])->where(['username' => $username])->first();
        if (!$user) {
            echoJson(419, 'this user is not exist', [], [], '#1003');
            exit(0);
        }
        if ($user['status'] != 1) {
            echoJson(419, 'This user has been banned', [], [], '#1004');
            exit(0);
        }

        if (!password_verify($password, $user['password'])) {
            echoJson(200, 'Incorrect username or password.', [], [], '#1005');
            exit(0);
        }

        $encrypter = \Config\Services::encrypter();
        $encrypted_uid = $encrypter->encrypt($user['id']);
        $loginToken = base64_encode($encrypted_uid);

        // $this->redis->set('logintoken_'.$loginToken, $user['id']);

        echoJson(200, 'ok', ['loginToken' => $loginToken, 'userId' => $user['id'], 'username' => $username, 'userNickname' => $user['nickname']], [], '#1006');
    }

    public function Verifytoken() {
        $request = \Config\Services::request();
        $loginToken = $request->getPostGet('loginToken');
        if (!$loginToken) {
            echoJson(400, 'Bad Request', [], ['loginToken' => 'The login token field is required.'], '#1007');
            exit(0);
        }

        $encrypter = \Config\Services::encrypter();
        $encrypted_uid = base64_decode($loginToken);
        $uid = $encrypter->decrypt($encrypted_uid);

        $uid = intval($uid);

        if (!$uid) {
            echoJson(401, 'Invalid Token', [], [], '#1008');
            exit(0);
        }

        $db = db_connect('reader');
        $userModel = model('UserModel', true, $db);
        $user = $userModel->select(['username', 'nickname', 'status'])->where(['id' => $uid])->first();
        if ($user) {
            if ($user['status'] != 1) {
                echoJson(401, 'This user has been banned', [], [], '#1009');
                exit(0);
            }

            echoJson(200, 'ok', ['userId' => $uid, 'username' => $user['username'], 'userNickname' => $user['nickname']], [], '#1010');
        }
        else {
            echoJson(401, 'Invalid Token', [], [], '#1011');
        }
    }
}
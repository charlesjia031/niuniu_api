<?php

namespace App\Controllers;

class Message extends BaseController
{
    public function GetMessage()
    {
        $request = \Config\Services::request();
        $lastId = $request->getPostGet('lastId', FILTER_SANITIZE_ADD_SLASHES);
        $lastId = intval($lastId);
        if (!$lastId) {
            echoJson(400, 'Bad Request', [], ['lastId' => 'The lastId field is required.'], '#2001');
            exit(0);
        }

        $loginToken = $request->getPostGet('loginToken', FILTER_SANITIZE_ADD_SLASHES);
        if (!$loginToken) {
            echoJson(400, 'Bad Request', [], ['loginToken' => 'The loginToken field is required.'], '#2002');
            exit(0);
        }
        $loginToken = trim($loginToken);

        $encrypter = \Config\Services::encrypter();
        $encrypted_uid = base64_decode($loginToken);
        $uid = $encrypter->decrypt($encrypted_uid);

        $uid = intval($uid);

        if (!$uid) {
            echoJson(401, 'Invalid Token', [], [], '#2003');
            exit(0);
        }

        $page_size = 50;

        $db = db_connect('reader');
        $userModel = model('UserModel', true, $db);
        $user = $userModel->select(['username', 'nickname', 'status'])->where(['id' => $uid])->first();
        if ($user) {
            if ($user['status'] != 1) {
                echoJson(401, 'Invalid Token', [], [], '#2004');
                exit(0);
            }
        }
        unset($userModel);

        $messageModel = model('MessageModel', true, $db);
        $message = $messageModel->select(['id','messageType','content','senderId','username','userNickname','senderImageThumb','createdAt'])->where(['id >' => $lastId])->orderBy('id', 'DESC')->findAll($page_size, 0);
        unset($messageModel);
        echoJson(200, 'ok', $message, [], '#2005');
    }

    public function GetSticker() {
        $request = \Config\Services::request();
        $lastUpdatedAt = $request->getPostGet('lastUpdatedAt');
        $lastUpdatedAt = str_replace('"','', $lastUpdatedAt);
        $lastUpdatedAt = intval($lastUpdatedAt);
        if (!$lastUpdatedAt) {
            echoJson(400, 'Bad Request', [], ['lastId' => 'The lastUpdatedAt field is integer only.'], '#2006');
            exit(0);
        }

        $db = db_connect('reader');
        // $db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        // $db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        
        $stickersModel = model('StickersModel', true, $db);
        $sticker = $stickersModel->select(['id','name','sequenceNum','category','isRemote'])->where(['updatedAt >=' => $lastUpdatedAt])->orderBy('id', 'DESC')->findAll();
        unset($stickersModel);
        echoJson(200, 'ok', $sticker, [], '#2007');
    }
}



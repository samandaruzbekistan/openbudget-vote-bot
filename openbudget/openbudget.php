<?php 
require_once "Telegram.php";
date_default_timezone_set("Asia/Tashkent");
$token = "";

// Created at Samandar Sariboyev - samandarsariboyev69@gmail.com - +998 97 567 20 09
$username = "";
$host = "";
$password = "";
$db = "";

$telegram = new Telegram($token);
$data = $telegram->getData();
$message = $data['message'];
$message_id = $message['message_id'];
$text = $message['text'];
$chat_id = $message['chat']['id'];
$callback_query = $telegram->Callback_Query();
$callback_data = $telegram->Callback_Data();
$chatID = $telegram->Callback_ChatID();
$adminlar = [1182438391,848511386,1199252280];

$con = mysqli_connect($host, $username, $password, $db);

echo "bot is working!";
if ($callback_query != null && $callback_query != '') {
    $r = "Select * from `users` where `chat_id` = {$chatID}";
    $res = mysqli_query($con, $r);
    $p = mysqli_fetch_assoc($res);
    $page = $p['page'];
    switch ($page) {
        case 'vote':
            if ($callback_data == 'check'){
                // $arr = json_encode($callback_query);
                // sendMessageCall($arr);
                SetPageCall('check');
                $telegram->deleteMessage(['chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id']]);
                sendMessageCall("<b>Ovoz beriladigan telefon raqamni 971234567 formatda kiriting:</b>");
            }
            break;
        
        default:
            # code...
            break;
    }
}
else if ($text == '/start') {
    Start();
}
elseif (strpos($text, '/start') !== false) {
    $referal_chat_id = substr($text, 7);
    StartByReferal($referal_chat_id);
}
else{
    $sql = "select * from `users` where chat_id = {$chat_id};";
    $r = mysqli_query($con, $sql);
    $fetch_array = mysqli_fetch_assoc($r);
    switch ($fetch_array['page']) {
        case 'start':
            switch ($text) {
                case '🔗 Referal':
                    $str = "Sizning referal havolangiz:\n\nhttps://t.me/openbudget_tolovli_bot?start=".$chat_id."\n\nUshbu havolani boshqalarga ulashing, ularning birinchi ovoziga uchun sizga 2 000 so'm beriladi";
                    sendMessage($str);
                    Start();
                    break;
                case '📤 Ovoz berish':
                    SetPage('vote');
                    // sendTextWithKeyboard(['🏠 Bosh sahifaga qaytish'],"📞 Ovoz berish uchun telefon raqamni kiriting:\n\nTelefon raqami 991234567 formatida kiritilishi kerak.\n\n✅ Ovoz berish muvaffaqiyatli o'tganda, hisobingizga 11 000 so'm o'tkazib beriladi!");
                   
                    $buttons = [
                        [$telegram->buildInlineKeyboardButton("Saytdan ovoz berish 🚀", 'https://openbudget.uz/boards/initiatives/initiative/32/1f179f65-b2c8-4435-9d57-d390aecde891', '003')],
                        [$telegram->buildInlineKeyboardButton("Telegramdan ovoz berish 🚀", 'https://t.me/ochiqbudjet_3_bot?start=032299424010', '004')],
                        [$telegram->buildInlineKeyboardButton("Ovoz berdim ✅", '', 'check')],
                    ];
                    $keyboard = $telegram->buildInlineKeyBoard($buttons);
                    $tekst = "Ovoz berish uchun <b>Ovoz berish </b>🚀 tugmasini bosib raqamingizni kiritib ovoz bering.\n📌 ESLATMA: Iltimos ovoz berganingizdan so'ng <b>Ovoz berdim</b> ✅ tugmasini bosing, aks holda ovozingiz inobatga olinmaydi!";
                    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $tekst, 'caption' => $caption,'reply_markup' =>$keyboard, 'parse_mode' => "HTML"]);
                    sendTextWithKeyboard(['🏠 Bosh sahifaga qaytish'], "Shoshiling! Har bir ovoz uchun pul ishlang 💸");
                    break;
                case "💰 Hisobim":
                    $tekst = "💴 Sizning balansingiz: {$fetch_array['balance']} so'm

💸 Jami to'langan: {$fetch_array['paid_amount']} so'm
👤 Referal orqali ulanganlar: {$fetch_array['referrals_count']} ta
👤 Referal orqali ulanib ovoz berganlar: {$fetch_array['vote_referalled']} ta
📤 Bergan ovozlaringiz soni: {$fetch_array['vote_count']} ta

💷 1 ovoz uchun to'lanadigan summa: 11000 so'm
💶 Referalingiz orqali kelgan foydalanuvchining 1-ovozi uchun sizga beriladigan bonus: 2000 so'm";
                    sendMessage($tekst);
                    Start();
                    break;
                case '💸 Pul yechib olish':
                    if($fetch_array['balance'] == 0){
                        sendMessage("<b>💸 Sizning balansingiz 0 so'm</b>\nOvoz berib balansingizni to'ldiring");
                        Start();
                    }
                    else if($fetch_array['balance'] > 0){
                        SetPage('get_card_number');
                        sendTextWithKeyboard(['🏠 Bosh sahifaga qaytish'], "Balansingiz: {$fetch_array['balance']}\nKarta raqam va karta egasining ism familyasini yuboring");
                    }
                    break;
                default:
                    # code...
                    break;
            }
            break;
        case 'check':
            $datetime = date("H:i:s d.m.Y");
            // sendMessage($datetime);
            $sql = "INSERT INTO `votes`(`chat_id`, `user_phone`, `datetime`) VALUES ($chat_id,'$text','$datetime')";
            mysqli_query($con, $sql);
            sendMessage("<b>Ovozingiz tekshirilmoqda ‼️ Saytda ovoz bergan bo'lsangiz 10-20 daqiqa ichida hisobingizga pul o'tkazib beriladi</b>");
            Start();
            break;
        case 'vote':
            if($text == "🏠 Bosh sahifaga qaytish") Start();
            break;
        case 'get_card_number':
            $sql = "INSERT INTO `patment_request`(`chat_id`, `user_name`, `card_data`) VALUES ($chat_id, '{$fetch_array['name']}', '{$text}')";
            // sendMessage($sql);
            mysqli_query($con,$sql);
            sendMessage("So'rovingiz qabul qilindi tez orada pul o'tkaziladi");
            Start();
            break;
        default:
            # code...
            break;
    }
}

function Start(){
	global $chat_id, $message,$con, $data, $telegram;
    $user = mysqli_query($con, "SELECT * FROM  `users` where `chat_id` =  {$chat_id};");
    $dat = json_encode($data);
    if(mysqli_num_rows($user)<1){
        $sql = "INSERT INTO `users`(`chat_id`, `name`, `page`, `json_data`) VALUES ($chat_id, '{$message['from']['first_name']}','start', '{$dat}')";
	    $r = mysqli_query($con,$sql);
        
        SetPage('start');
        sendTextWithKeyboard(['📤 Ovoz berish','💰 Hisobim','💸 Pul yechib olish','🔗 Referal'], 'Asosiy menu');
    }
    else{
        SetPage('start');
        sendTextWithKeyboard(['📤 Ovoz berish','💰 Hisobim','💸 Pul yechib olish','🔗 Referal'], 'Asosiy menu');
    }

}

function StartByReferal($referal_id){
	global $chat_id, $message,$con, $data, $telegram;
    $user = mysqli_query($con, "SELECT * FROM  `users` where `chat_id` =  {$chat_id};");
    $dat = json_encode($data);
    if(mysqli_num_rows($user)<1){
        $sql = "INSERT INTO `users`(`chat_id`, `name`, `page`, `json_data`,`referred_by`) VALUES ($chat_id, '{$message['from']['first_name']}','start', '{$dat}', {$referal_id})";
	    $r = mysqli_query($con,$sql);
        $sql = "UPDATE `users` SET `referrals_count` = `referrals_count` + 1 WHERE `chat_id` = {$referal_id}";
        mysqli_query($con, $sql);
        SetPage('start');
        sendTextWithKeyboard(['📤 Ovoz berish','💰 Hisobim','💸 Pul yechib olish','🔗 Referal'], 'Asosiy menu');
    }
    else{
        SetPage('start');
        sendTextWithKeyboard(['📤 Ovoz berish','💰 Hisobim','💸 Pul yechib olish','🔗 Referal'], 'Asosiy menu');
    }

}

function sendTextWithKeyboard($buttons, $text, $backBtn = false)
{
    global $telegram, $chat_id, $texts;
    $option = [];
    if (count($buttons) % 2 == 0) {
        for ($i = 0; $i < count($buttons); $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
    } else {
        for ($i = 0; $i < count($buttons) - 1; $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
        $option[] = array($telegram->buildKeyboardButton(end($buttons)));
    }
    if ($backBtn) {
        $option[] = [$telegram->buildKeyboardButton($texts->getText("back_btn"))];
    }
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function sendTextWithKeyboardCall($buttons, $text, $backBtn = false)
{
    global $telegram, $chatID, $texts;
    $option = [];
    if (count($buttons) % 2 == 0) {
        for ($i = 0; $i < count($buttons); $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
    } else {
        for ($i = 0; $i < count($buttons) - 1; $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
        $option[] = array($telegram->buildKeyboardButton(end($buttons)));
    }
    if ($backBtn) {
        $option[] = [$telegram->buildKeyboardButton($texts->getText("back_btn"))];
    }
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}


function SetPage($name)
{
    global $chat_id, $con;
    $r = mysqli_query($con, "UPDATE `users` SET `page`='{$name}' WHERE `chat_id` = {$chat_id}");
}

function SetPageCall($name)
{
    global $con, $chatID;
    $r = mysqli_query($con, "UPDATE `users` SET `page`='{$name}' WHERE `chat_id` = {$chatID}");
}


function sendMessage($text)
{
    global $telegram, $chat_id;
    $telegram->sendMessage(['chat_id' => $chat_id, 'reply_markup' => json_encode(['remove_keyboard' => true], true), 'text' => $text, 'parse_mode' => "HTML"]);
}

function sendMessageCall($text)
{
    global $telegram, $chatID;
    $telegram->sendMessage(['chat_id' => $chatID, 'reply_markup' => json_encode(['remove_keyboard' => true]),'text' => $text, 'parse_mode' => "HTML"]);
}
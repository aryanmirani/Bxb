<?php

require 'vendor/autoload.php';

use TelegramBotApi;

$telegram = new Api('YOUR_BOT_TOKEN');

// تعیین ID مدیران
$admins = [123456789, 987654321]; // ID مدیران را در اینجا قرار دهید

// متغیر برای ذخیره وضعیت موزیک
$musicPlaying = false;
$currentChatId = null;

// دریافت بروزرسانی‌ها از تلگرام
$updates = $telegram->getUpdates();

foreach ($updates as $update) {
    $chatId = $update->getMessage()->getChat()->getId();
    $userId = $update->getMessage()->getFrom()->getId();
    $text = $update->getMessage()->getText();

    // بررسی اینکه آیا کاربر مدیر است
    if (!in_array($userId, $admins)) {
        $telegram->sendMessage($chatId, "🚫 متأسفانه شما مجاز به استفاده از این ربات نیستید. لطفاً با مدیر تماس بگیرید.");
        continue;
    }

    // بررسی دستورات
    if (stripos($text, 'play') !== false || stripos($text, 'پخش') !== false) {
        // بررسی وجود لینک در متن
        if (preg_match('/(https?://[^s]+)/', $text, $matches)) {
            $audioLink = $matches[0];
            if (!$musicPlaying) {
                $telegram->sendAudio($chatId, $audioLink);
                $musicPlaying = true;
                $currentChatId = $chatId;
                $telegram->sendMessage($chatId, "🎶 موزیک در حال پخش است: $audioLink");
            } else {
                $telegram->sendMessage($chatId, "🎵 موزیک در حال حاضر در حال پخش است. برای قطع موزیک، از دستور stop یا بستن استفاده کنید.");
            }
        } else {
            $telegram->sendMessage($chatId, "❌ لطفاً یک لینک معتبر برای پخش موزیک ارسال کنید.");
        }
    } elseif (stripos($text, 'stop') !== false || stripos($text, 'بستن') !== false) {
        if ($musicPlaying && $currentChatId == $chatId) {
            // منطق واقعی برای قطع موزیک
            // در اینجا باید کدی برای قطع موزیک اضافه کنید (این قسمت بسته به پلتفرم شما متفاوت خواهد بود)
            $musicPlaying = false;
            $telegram->sendMessage($chatId, "⏸️ موزیک قطع شد.");
        } else {
            $telegram->sendMessage($chatId, "🔇 هیچ موزیکی در حال پخش نیست یا شما مجاز به قطع آن نیستید.");
        }
    } else {
        $telegram->sendMessage($chatId, "🛠️ لطفاً از دستورات زیر استفاده کنید:\n- برای پخش موزیک: play <لینک> یا پخش <لینک>\n- برای قطع موزیک: stop یا بستن");
    }
}

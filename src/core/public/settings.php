<?php

if($text == '⚙️ تنظیمات'){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"⚙️ به تنظیمات اکانت خود در ربات اوه پسر خوش آمدید، لطفا یک بخش را از بین بخش های زیر انتخاب کنید 👇🏻",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[
                [['text'=>'⚙️ مرتب سازی نمایش ویس ها', 'callback_data'=>'usersettings']],
                [['text'=>'⚙️ نمایش ویس های نامناسب', 'callback_data'=>'showbadvoices']],
                [['text'=>'⚙️ عملکرد دکمه ارسال ویس برای دیگران', 'callback_data'=>'sendvoiceaction']],
            ],
        ])
    ]);
}
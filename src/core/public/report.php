<?php


if($text == '⚠️ گزارش'){
    $db->query("UPDATE `user` SET `step` = 'reportvoice1' WHERE `id` = '{$from_id}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'👈🏻 در صورت مشاهده یا وجود مشکلاتی از جمله (ثبت بی اجازه ویس شما/شخصی در ربات توسط کاربران دیگر، وجود توهین و فحاشی به شخص خاصی، و یا...) در یکی از ویس های ثبت شده در ربات، میتوانید در این قسمت به ما این مورد را گزارش دهید.

📌 نکاتی که باید به آنها توجه کنید :
▪️ گزارش ثبت شده میتواند برای ویس های عمومی و خصوصی باشد. (برای ویس های خصوصی : درصورتی که ویس غیرمجازی توسط کاربری به شکل خصوصی و بدون تایید در ربات ثبت شد و آن کاربر از آن ویس در مکان های مختلف استفاده کرد، شما همچنان میتوانید آن ویس را در اینجا گزارش کنید)
▪️ویس گزارش شده پس از بررسی و تایید مدیریت از دسترس تمامی کاربران (حتی برای تیم مدیریت اوه پسر) از دسترس خارج میشود.
▪️ درصورتی که ویس گزارش شده به تایید تیم مدیریت از دسترس خارج شود، ارسال مجدد آن ویس در ربات نیز غیرفعال میشود و درصورتی که آن کاربر (یا هر کاربر دیگری) تصمیم به ثبت مجدد ویس غیرفعال شده کنند، ربات این اجازه را به آنها نمیدهد.

✅ درصورت مطالعه و موافقت با موارد بالا، لطفا ویس مورد نظر را برای گزارش ارسال کنید :',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
}
elseif($user['step'] == 'reportvoice1' && $text !== $backbtn){
    $systemid = $update->message->voice->file_unique_id;
    if(!$update->message->voice){
        SendMessage($chat_id, 'لطفا فقط یک ویس را ارسال کنید.');
        mysqli_close($db);
        exit();
    }
    $getsubmittedvoice = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$systemid}' LIMIT 1"));
    if($getsubmittedvoice){
        if($getsubmittedvoice['sender'] == $from_id){
            SendMessage($chat_id, '🚫 شما نمیتوانید ویسی که خودتان ثبت کردید را گزارش کنید. درصورتی که نیاز به بررسی این ویس دارید، لطفا با تیم مدیریت از طریق بخش پشتیبانی آن را مطرح کنید.');
            mysqli_close($db);
            exit();
        }
    }else{
        SendMessage($chat_id, 'این ویس در ربات ثبت نشده است!');
        mysqli_close($db);
        exit();
    }
    $db->query("UPDATE `user` SET `step` = 'reportvoice2', `voicename` = '{$systemid}' WHERE `id` = '{$from_id}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'📝 لطفا توضیحی برای گزارش خود بنویسید.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
    mysqli_close($db);
    exit();
}

elseif($user['step'] == 'reportvoice2' && $text !== $backbtn){
    $db->query("UPDATE `user` SET `step` = 'none', `voicename` = '' WHERE `id` = '{$from_id}' LIMIT 1");
    $systemid = $user['voicename'];
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$systemid}' LIMIT 1"));
    $vname = $voiceinfo['name'];
    SendVoice($CONFIG['CHANNEL']['VOICEACCEPT'],
        'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.$voiceinfo['messageid'], 
        json_encode([
            'inline_keyboard'=>[
            [['text'=>"👁‍🗨 علامت به عنوان دیده شده (برای کاربر)",'callback_data'=>'acceptreportseen-'.$systemid.'-'.$chat_id]],
            [['text'=>"✅",'callback_data'=>'acceptreport-'.$systemid.'-'.$chat_id], ['text'=>"❌",'callback_data'=>'rejectreport-'.$systemid.'-'.$chat_id]],
            ],
        ]),
        "🎤 ⚠️ گزارش ویس با عنوان ($vname) توسط کاربر $first_name

💬 آیدی عددی ارسال کننده : $from_id
$senderusername

📝 توضیحات : $text"
        );
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'✅ ویس ارسالی با موفقیت گزارش و برای تیم مدیریت اوه پسر ارسال شد.

📌 نکاتی که باید به آنها توجه کنید :
▪️ درصورت مشاهده شدن گزارش شما توسط تیم مدیریت اوه پسر، به صورت اتوماتیک به شما اطلاع داده خواهد شد.
▪️ زمان بررسی گزارش شما حداقل 15 دقیقه و حداکثر 72 ساعت میتواند زمان بر باشد.',
        'reply_markup'=>json_encode(['keyboard'=>$home, 'resize_keyboard'=>true])
    ]);
    mysqli_close($db);
    exit();
}
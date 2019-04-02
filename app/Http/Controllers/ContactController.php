<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMail;
use Mail;

class ContactController extends Controller
{
    /**
     * 返回联系我们表单
     */
    public function showForm()
    {
        return view('posts.contact');
    }

    /**
     * 发送邮件提醒用户留言成功
     * 
     * @param ContactRequest $request
     */
    public function sendContactInfo(ContactRequest $request)
    {
        $data = $request->only('name', 'email', 'phone');
        $data['messageLines'] = explode("\n", $request->input('message'));// 
        
        // Mail::to($request->input('email'))->send(new ContactMail($data));    //即时发送
        Mail::to($request->input('email'))->queue(new ContactMail($data));      // 加入队列

        return back()->with('success', '消息已发送，感谢您的反馈');
    }
}

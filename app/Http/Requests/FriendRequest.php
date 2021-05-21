<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FriendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "api_id" => "required",
            "api_key" => "required",
            "content_json.nickname" => "required",
            "content_json.wxid" => "required",
            "content_json.user_list.*.userid" => "required",
            "content_json.user_list.*.remark" => "required",
            "content_json.user_list.*.nickname" => "required",
            "content_json.user_list.*.user_number" => "required",
        ];
    }
}

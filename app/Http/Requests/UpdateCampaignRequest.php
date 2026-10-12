<?php

namespace App\Http\Requests;

class UpdateCampaignRequest extends StoreCampaignRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}

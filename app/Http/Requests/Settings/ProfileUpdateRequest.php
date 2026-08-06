<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->profileRules($this->user()->id);

        // BFP staff also manage their rank/contact number from this same
        // form; residents (registered via the shared trait/CreateNewUser)
        // never see these fields, so they stay out of profileRules().
        if ($this->user()->isBfpStaff()) {
            $rules['rank'] = ['required', 'string', 'max:100'];
            $rules['contact_number'] = ['required', 'string', 'max:20'];
        }

        return $rules;
    }
}
